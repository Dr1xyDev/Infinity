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

namespace pocketmine\level\generator\vanilla\event\server;

use pocketmine\level\generator\vanilla\network\NetworkInterface;
use Throwable;

/**
 * Never called. Should never have come into this world. Nothing to see here.
 * @deprecated
 */
class NetworkInterfaceCrashEvent extends NetworkInterfaceEvent
{
	/** @var Throwable */
	private $exception;

	public function __construct(NetworkInterface $interface, Throwable $throwable)
	{
		parent::__construct($interface);
		$this->exception = $throwable;
	}

	public function getCrashInformation() : Throwable
	{
		return $this->exception;
	}
}
