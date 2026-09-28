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

/**
 * Network-related classes
 */

namespace pocketmine\level\generator\vanilla\network;

use pocketmine\level\generator\vanilla\event\server\NetworkInterfaceRegisterEvent;
use pocketmine\level\generator\vanilla\event\server\NetworkInterfaceUnregisterEvent;
use pocketmine\level\generator\vanilla\network\mcpe\convert\block\RuntimeBlockMapping;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\PacketPool;
use pocketmine\level\generator\vanilla\utils\Utils;
use raklib\generic\PacketHandlingException;
use function base64_encode;
use function get_class;
use function preg_match;
use function spl_object_id;
use function time;
use const PHP_INT_MAX;

class Network
{
	/** @var NetworkInterface[] */
	private array $interfaces = [];

	/** @var AdvancedNetworkInterface[] */
	private array $advancedInterfaces = [];
	/** @var RawPacketHandler[] */
	private array $rawPacketHandlers = [];

	/**
	 * @var int[]
	 * @phpstan-var array<string, int>
	 */
	private array $bannedIps = [];

	private float $upload = 0;
	private float $download = 0;

	private string $name;

	public function __construct(
		private \Logger $logger
	) {
		PacketPool::init();

		RuntimeBlockMapping::getInstance(844);
	}

	public function addStatistics(float $upload, float $download) : void
	{
		$this->upload += $upload;
		$this->download += $download;
	}

	public function getUpload() : float
	{
		return $this->upload;
	}

	public function getDownload() : float
	{
		return $this->download;
	}

	public function resetStatistics() : void
	{
		$this->upload = 0;
		$this->download = 0;
	}

	/**
	 * @return NetworkInterface[]
	 */
	public function getInterfaces() : array
	{
		return $this->interfaces;
	}

	public function tick() : void
	{
		foreach ($this->interfaces as $interface) {
			$interface->tick();
		}
	}

	/**
	 * @deprecated
	 */
	public function processInterfaces() : void
	{
		$this->tick();
	}

	/**
	 * @deprecated
	 */
	public function processInterface(NetworkInterface $interface) : void
	{
		$interface->tick();
	}

	public function registerInterface(NetworkInterface $interface) : bool
	{
		$ev = new NetworkInterfaceRegisterEvent($interface);
		$ev->call();
		if (!$ev->isCancelled()) {
			$interface->start();
			$this->interfaces[$hash = spl_object_id($interface)] = $interface;
			if ($interface instanceof AdvancedNetworkInterface) {
				$this->advancedInterfaces[$hash] = $interface;
				$interface->setNetwork($this);
				foreach (Utils::stringifyKeys($this->bannedIps) as $ip => $until) {
					$interface->blockAddress($ip);
				}
				foreach ($this->rawPacketHandlers as $handler) {
					$interface->addRawPacketFilter($handler->getPattern());
				}
			}
			$interface->setName($this->name);
			return true;
		}
		return false;
	}

	public function unregisterInterface(NetworkInterface $interface) : void
	{
		if (!isset($this->interfaces[$hash = spl_object_id($interface)])) {
			throw new \InvalidArgumentException("Interface " . get_class($interface) . " is not registered on this network");
		}
		(new NetworkInterfaceUnregisterEvent($interface))->call();
		unset($this->interfaces[$hash], $this->advancedInterfaces[$hash]);
		$interface->shutdown();
	}

	/**
	 * Sets the server name shown on each interface Query
	 */
	public function setName(string $name) : void
	{
		$this->name = $name;
		foreach ($this->interfaces as $interface) {
			$interface->setName($this->name);
		}
	}

	public function getName() : string
	{
		return $this->name;
	}

	public function updateName() : void
	{
		foreach ($this->interfaces as $interface) {
			$interface->setName($this->name);
		}
	}

	public function sendPacket(string $address, int $port, string $payload) : void
	{
		foreach ($this->advancedInterfaces as $interface) {
			$interface->sendRawPacket($address, $port, $payload);
		}
	}

	/**
	 * Blocks an IP address from the main interface. Setting timeout to -1 will block it forever
	 */
	public function blockAddress(string $address, int $timeout = 300) : void
	{
		$this->bannedIps[$address] = $timeout > 0 ? time() + $timeout : PHP_INT_MAX;
		foreach ($this->advancedInterfaces as $interface) {
			$interface->blockAddress($address, $timeout);
		}
	}

	public function unblockAddress(string $address) : void
	{
		unset($this->bannedIps[$address]);
		foreach ($this->advancedInterfaces as $interface) {
			$interface->unblockAddress($address);
		}
	}

	/**
	 * Registers a raw packet handler on the network.
	 */
	public function registerRawPacketHandler(RawPacketHandler $handler) : void
	{
		$this->rawPacketHandlers[spl_object_id($handler)] = $handler;

		$regex = $handler->getPattern();
		foreach ($this->advancedInterfaces as $interface) {
			$interface->addRawPacketFilter($regex);
		}
	}

	/**
	 * Unregisters a previously-registered raw packet handler.
	 */
	public function unregisterRawPacketHandler(RawPacketHandler $handler) : void
	{
		unset($this->rawPacketHandlers[spl_object_id($handler)]);
	}

	public function processRawPacket(AdvancedNetworkInterface $interface, string $address, int $port, string $packet) : void
	{
		if (isset($this->bannedIps[$address]) && time() < $this->bannedIps[$address]) {
			$this->logger->debug("Dropped raw packet from banned address $address $port");
			return;
		}
		$handled = false;
		foreach ($this->rawPacketHandlers as $handler) {
			if (preg_match($handler->getPattern(), $packet) === 1) {
				try {
					$handled = $handler->handle($interface, $address, $port, $packet);
				} catch (PacketHandlingException $e) {
					$handled = true;
					$this->logger->error("Bad raw packet from /$address:$port: " . $e->getMessage());
					$this->blockAddress($address, 600);
					break;
				}
			}
		}
		if (!$handled) {
			$this->logger->debug("Unhandled raw packet from /$address:$port: " . base64_encode($packet));
		}
	}
}
