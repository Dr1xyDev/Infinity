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
use pocketmine\level\generator\vanilla\event\player\PlayerCreationEvent;
use pocketmine\level\generator\vanilla\network\AdvancedNetworkInterface;
use pocketmine\level\generator\vanilla\network\mcpe\PacketSender;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ProtocolInfo;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\DisconnectFailReason;
use pocketmine\level\generator\vanilla\network\Network;
use pocketmine\level\generator\vanilla\network\PacketHandlingException;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\level\generator\vanilla\snooze\SleeperNotifier;
use pocketmine\level\generator\vanilla\thread\ThreadCrashException;
use pocketmine\level\generator\vanilla\utils\Utils;
use raklib\generic\DisconnectReason;
use raklib\generic\SocketException;
use raklib\protocol\EncapsulatedPacket;
use raklib\protocol\PacketReliability;
use raklib\server\ipc\RakLibToUserThreadMessageReceiver;
use raklib\server\ipc\UserToRakLibThreadMessageSender;
use raklib\server\ServerEventListener;
use raklib\utils\InternetAddress;

use function addcslashes;
use function base64_encode;
use function implode;
use function mt_rand;
use function print_r;
use function rtrim;
use function substr;

use const PHP_INT_MAX;

class RakLibInterface implements ServerEventListener, AdvancedNetworkInterface, PacketSender
{
	/**
	 * Sometimes this gets changed when the MCPE-layer protocol gets broken to the point where old and new can't
	 * communicate. It's important that we check this to avoid catastrophes.
	 */
	public const int MCBE_RAKNET_PROTOCOL_VERSION_WITHOUT_ZLIB_COMPRESSION = 11;
	public const int MCBE_RAKNET_PROTOCOL_VERSION_WITH_RAW_ZLIB_ENCODING = 10;
	public const int MCBE_RAKNET_PROTOCOL_VERSION = 9;
	public const int MCPE_RAKNET_PROTOCOL_VERSION = 8;

	public const string MCPE_RAKNET_PACKET_ID = "\xfe";

	private Server $server;
	private Network $network;

	private int $rakServerId;
	private RakLibServer $rakLib;

	/** @var Player[] */
	private array $sessions = [];
	/** @var int[] */
	private array $identifiersACK = [];

	private RakLibToUserThreadMessageReceiver $eventReceiver;
	private UserToRakLibThreadMessageSender $interface;

	private SleeperNotifier $sleeper;

	public function __construct(
		Server $server,
		string $ip,
		int $port
	) {
		$this->server = $server;

		$this->rakServerId = mt_rand(0, PHP_INT_MAX);

		$this->sleeper = new SleeperNotifier();

		/** @phpstan-var ThreadSafeArray<int, string> $mainToThreadBuffer */
		$mainToThreadBuffer = new ThreadSafeArray();
		/** @phpstan-var ThreadSafeArray<int, string> $threadToMainBuffer */
		$threadToMainBuffer = new ThreadSafeArray();

		$this->rakLib = new RakLibServer(
			$this->server->getLogger(),
			$mainToThreadBuffer,
			$threadToMainBuffer,
			new InternetAddress($ip, $port, 4),
			$this->rakServerId,
			(int) $this->server->getProperty("network.max-mtu-size", 1492),
			[
				self::MCBE_RAKNET_PROTOCOL_VERSION_WITHOUT_ZLIB_COMPRESSION,
				self::MCBE_RAKNET_PROTOCOL_VERSION_WITH_RAW_ZLIB_ENCODING,
				self::MCBE_RAKNET_PROTOCOL_VERSION,
				self::MCPE_RAKNET_PROTOCOL_VERSION
			],
			$this->sleeper
		);
		$this->eventReceiver = new RakLibToUserThreadMessageReceiver(
			new PthreadsChannelReader($threadToMainBuffer)
		);
		$this->interface = new UserToRakLibThreadMessageSender(
			new PthreadsChannelWriter($mainToThreadBuffer)
		);
	}

	public function start() : void
	{
		$this->server->getTickSleeper()->addNotifier($this->sleeper, function () : void {
			$this->tick();
		});

		$this->server->getLogger()->debug("Waiting for RakLib to start...");
		try {
			$this->rakLib->startAndWait(NativeThread::INHERIT_CONSTANTS);
		} catch (SocketException $e) {
			throw new \Exception($e->getMessage(), 0, $e);
		}
		$this->server->getLogger()->debug("RakLib booted successfully");

		$this->setPortCheck($this->server->getSubmarineProperty("network.port-checking", true));
		$this->setPacketLimit($this->server->getSubmarineProperty("network.packet-limit", 300));
	}

	public function setNetwork(Network $network) : void
	{
		$this->network = $network;
	}

	public function tick() : void
	{
		if ($this->eventReceiver->handle($this)) {
			while ($this->eventReceiver->handle($this)) {
			}
		}

		if ($this->rakLib->isTerminated()) {
			$this->network->unregisterInterface($this);

			$e = $this->rakLib->getCrashInfo();
			if ($e !== null) {
				\GlobalLogger::get()->error("Error: " . print_r($e->getTrace(), true));
				throw new ThreadCrashException("RakLib crashed: " . $e->getMessage(), $e);
			}
			throw new \Exception("RakLib Thread crashed without crash information");
		}
	}

	public function onClientDisconnect(int $sessionId, int $reason) : void
	{
		if (isset($this->sessions[$sessionId])) {
			$player = $this->sessions[$sessionId];
			unset($this->sessions[$sessionId], $this->identifiersACK[$sessionId]);
			$player->close($player->getLeaveMessage(), match($reason) {
				DisconnectReason::CLIENT_DISCONNECT => $this->server->getLanguage()->translateString("pocketmine.disconnect.clientDisconnect"),
				DisconnectReason::PEER_TIMEOUT => $this->server->getLanguage()->translateString("pocketmine.disconnect.error.timeout"),
				DisconnectReason::CLIENT_RECONNECT => $this->server->getLanguage()->translateString("pocketmine.disconnect.clientReconnect"),
				default => "Unknown RakLib disconnect reason (ID $reason)"
			});
		}
	}

	public function close(int $sessionId, string $reason = "unknown reason") : void
	{
		if (isset($this->sessions[$sessionId])) {
			unset($this->sessions[$sessionId], $this->identifiersACK[$sessionId]);
			$this->interface->closeSession($sessionId);
		}
	}

	public function shutdown() : void
	{
		$this->server->getTickSleeper()->removeNotifier($this->sleeper);
		$this->rakLib->quit();
	}

	public function onClientConnect(int $sessionId, string $address, int $port, int $clientID, int $protocolVersion) : void
	{
		$ev = new PlayerCreationEvent($this, Player::class, Player::class, $address, $port, $protocolVersion);
		$ev->call();
		$class = $ev->getPlayerClass();

		/**
		 * @var Player $player
		 * @see Player::__construct()
		 */
		$player = new $class($this, $ev->getAddress(), $ev->getPort(), $sessionId, $protocolVersion);
		$this->sessions[$sessionId] = $player;
		$this->server->addPlayer($player);
	}

	public function onPacketReceive(int $sessionId, string $packet) : void
	{
		if (isset($this->sessions[$sessionId])) {
			$player = $this->sessions[$sessionId];

			if ($packet === "" || $packet[0] !== self::MCPE_RAKNET_PACKET_ID) {
				$player->getServer()->getLogger()->debug("[" . $player->getAddress() . "] " . $player->getName() . " Non-FE packet received: " . base64_encode($packet));
				return;
			}

			$buf = substr($packet, 1);
			$name = $player->getName();
			try {
				$player->handleEncoded($buf);
			} catch (PacketHandlingException $e) {
				$logger = $player->getServer()->getLogger();

				$address = $player->getAddress();
				$player->close($player->getLeaveMessage(), "Bad packet: " . $e->getMessage(), true, DisconnectFailReason::BAD_PACKET);
				$this->interface->blockAddress($address, 5);

				//intentionally doesn't use logException, we don't want spammy packet error traces to appear in release mode
				$logger->debug(implode("\n", Utils::printableExceptionInfo($e)));

				$this->interface->blockAddress($address, 5);
			} catch (\Throwable $e) {
				$logger = $this->server->getLogger();

				//record the name of the player who caused the crash, to make it easier to find the reproducing steps
				$logger->emergency("Crash occurred while handling a packet from session: $name");
				$logger->logException($e);

				if (!$this->server->internalErrorKick) {
					$player->sendMessage("Internal server error");
				} else {
					$address = $player->getAddress();
					$player->close($player->getLeaveMessage(), "Internal server error", true, DisconnectFailReason::BAD_PACKET);
					$this->interface->blockAddress($address, 5);
				}
			}
		}
	}

	public function blockAddress(string $address, int $timeout = 300) : void
	{
		$this->interface->blockAddress($address, $timeout);
	}

	public function unblockAddress(string $address) : void
	{
		$this->interface->unblockAddress($address);
	}

	public function onRawPacketReceive(string $address, int $port, string $payload) : void
	{
		$this->network->processRawPacket($this, $address, $port, $payload);
	}

	public function sendRawPacket(string $address, int $port, string $payload) : void
	{
		$this->interface->sendRaw($address, $port, $payload);
	}

	public function addRawPacketFilter(string $regex) : void
	{
		$this->interface->addRawPacketFilter($regex);
	}

	public function onPacketAck(int $sessionId, int $identifierACK) : void
	{

	}

	public function setName(string $name) : void
	{
		$info = $this->server->getQueryInformation();

		$this->interface->setName(
			implode(
				";",
				[
					"MCPE",
					rtrim(addcslashes($name, ";"), '\\'),
					ProtocolInfo::CURRENT_PROTOCOL,
					ProtocolInfo::MINECRAFT_VERSION_NETWORK,
					$info->getPlayerCount(),
					$info->getMaxPlayerCount(),
					$this->rakServerId,
					$this->server->getName(),
					match($this->server->getGamemode()) {
						Player::SURVIVAL => "Survival",
						Player::ADVENTURE => "Adventure",
						default => "Creative"
					}
				]
			) . ";"
		);
	}

	public function setPortCheck(bool $name) : void
	{
		$this->interface->setPortCheck($name);
	}

	public function setPacketLimit(int $limit) : void
	{
		$this->interface->setPacketsPerTickLimit($limit);
	}

	public function onBandwidthStatsUpdate(int $bytesSentDiff, int $bytesReceivedDiff) : void
	{
		$this->network->addStatistics($bytesSentDiff, $bytesReceivedDiff);
	}

	public function putBuffer(int $sessionId, string $payload, bool $needACK = false, bool $immediate = true) : ?int
	{
		if (isset($this->sessions[$sessionId])) {
			$pk = new EncapsulatedPacket();
			if ($needACK) {
				$this->identifiersACK[$sessionId] ??= 0;
				$pk->identifierACK = $this->identifiersACK[$sessionId]++;
			} else {
				$pk->identifierACK = null;
			}
			$pk->buffer = self::MCPE_RAKNET_PACKET_ID . $payload;
			$pk->reliability = PacketReliability::RELIABLE_ORDERED;
			$pk->orderChannel = 0;

			$this->interface->sendEncapsulated($sessionId, $pk, $immediate);
			return $pk->identifierACK;
		}
		return null;
	}

	public function onPingMeasure(int $sessionId, int $pingMS) : void
	{
		if (isset($this->sessions[$sessionId])) {
			$this->sessions[$sessionId]->updatePing($pingMS);
		}
	}
}
