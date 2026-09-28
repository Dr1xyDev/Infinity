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

namespace pocketmine\level\generator\vanilla\network\upnp;

use pocketmine\level\generator\vanilla\network\NetworkInterface;
use pocketmine\level\generator\vanilla\utils\Internet;
use pocketmine\level\generator\vanilla\utils\InternetException;

final class UPnPNetworkInterface implements NetworkInterface
{
	private ?string $serviceURL = null;

	public function __construct(
		private \Logger $logger,
		private string $ip,
		private int $port
	) {
		if (!Internet::$online) {
			throw new \RuntimeException("Server is offline");
		}

		$this->logger = new \PrefixedLogger($logger, "UPnP Port Forwarder");
	}

	public function start() : void
	{
		$this->logger->info("Attempting to portforward...");

		try {
			$this->serviceURL = UPnP::getServiceUrl();
			UPnP::portForward($this->serviceURL, Internet::getInternalIP(), $this->port, $this->port);
			$this->logger->info("Forwarded $this->ip:$this->port to external port $this->port");
		} catch (UPnPException | InternetException $e) {
			$this->logger->error("UPnP portforward failed: " . $e->getMessage());
		}
	}

	public function setName(string $name) : void
	{

	}

	public function tick() : void
	{

	}

	public function shutdown() : void
	{
		if ($this->serviceURL === null) {
			return;
		}

		UPnP::removePortForward($this->serviceURL, $this->port);
	}
}
