<?php

/*
 *
 *   _____       _                          _
 *  / ____|     | |                        (_)
 * | (___  _   _| |__  _ __ ___   __ _ _ __ _ _ __   ___
 *  \___ \| | | | '_ \| '_ ` _ \ / _` | '__| | '_ \ / _ \
 *  ____) | |_| | |_) | | | | | | (_| | |  | | | | |  __/
 * |_____/ \__,_|_.__/|_| |_| |_|\__,_|_|  |_|_| |_|\___|
 *
 * This program is private software. No license required.
 * Publication of this program is forbidden and will be punished.
 *
 * @author SEMENNEJO
 * @link vk.com/vk.snikers && t.me/semennejo
 *
 *
 */

declare(strict_types=1);

namespace pocketmine\level\generator\vanilla\network\mcpe\raklib;

use pmmp\thread\Thread as NativeThread;
use pmmp\thread\ThreadSafeArray;
use pocketmine\level\generator\vanilla\snooze\SleeperNotifier;
use pocketmine\level\generator\vanilla\thread\log\ThreadSafeLogger;
use pocketmine\level\generator\vanilla\thread\NonThreadSafeValue;
use pocketmine\level\generator\vanilla\thread\Thread;
use pocketmine\level\generator\vanilla\thread\ThreadCrashException;
use raklib\generic\SocketException;
use raklib\server\ipc\RakLibToUserThreadMessageSender;
use raklib\server\ipc\UserToRakLibThreadMessageReceiver;
use raklib\server\Server;
use raklib\server\ServerSocket;
use raklib\server\SimpleProtocolAcceptor;
use raklib\utils\ExceptionTraceCleaner;
use raklib\utils\InternetAddress;

use function gc_enable;
use function ini_set;

class RakLibServer extends Thread
{
	protected bool $ready = false;
	protected string $mainPath;
	/** @phpstan-var NonThreadSafeValue<InternetAddress> */
	protected NonThreadSafeValue $address;
	/** @phpstan-var NonThreadSafeValue<array> */
	protected NonThreadSafeValue $protocolVersions;

	/**
	 * @phpstan-param ThreadSafeArray<int, string> $mainToThreadBuffer
	 * @phpstan-param ThreadSafeArray<int, string> $threadToMainBuffer
	 */
	public function __construct(
		protected ThreadSafeLogger $logger,
		protected ThreadSafeArray $mainToThreadBuffer,
		protected ThreadSafeArray $threadToMainBuffer,
		InternetAddress $address,
		protected int $serverId,
		protected int $maxMtuSize,
		array $protocolVersions,
		protected SleeperNotifier $sleeperNotifier
	) {
		$this->mainPath = \pocketmine\PATH;
		$this->address = new NonThreadSafeValue($address);
		$this->protocolVersions = new NonThreadSafeValue($protocolVersions);
	}

	public function startAndWait(int $options = NativeThread::INHERIT_NONE) : void
	{
		$this->start($options);
		$this->synchronized(function () : void {
			while (!$this->ready && $this->getCrashInfo() === null) {
				$this->wait();
			}
			$crashInfo = $this->getCrashInfo();
			if ($crashInfo !== null) {
				if ($crashInfo->getType() === SocketException::class) {
					throw new SocketException($crashInfo->getMessage());
				}
				throw new ThreadCrashException("RakLib failed to start", $crashInfo);
			}
		});
	}

	protected function onRun() : void
	{
		gc_enable();
		ini_set("display_errors", '1');
		ini_set("display_startup_errors", '1');
		\GlobalLogger::set($this->logger);

		$socket = new ServerSocket($this->address->deserialize());
		$manager = new Server(
			$this->serverId,
			$this->logger,
			$socket,
			$this->maxMtuSize,
			new SimpleProtocolAcceptor($this->protocolVersions->deserialize()),
			new UserToRakLibThreadMessageReceiver(new PthreadsChannelReader($this->mainToThreadBuffer)),
			new RakLibToUserThreadMessageSender(new SnoozeAwarePthreadsChannelWriter($this->threadToMainBuffer, $this->sleeperNotifier)),
			new ExceptionTraceCleaner($this->mainPath),
			recvMaxSplitParts: 1024
		);
		$this->synchronized(function () : void {
			$this->ready = true;
			$this->notify();
		});
		while (!$this->isKilled) {
			$manager->tickProcessor();
		}
		$manager->waitShutdown();
	}

	public function getThreadName() : string
	{
		return "RakLib";
	}
}
