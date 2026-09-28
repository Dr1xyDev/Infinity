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

namespace pocketmine\level\generator\vanilla\event\player;

use pocketmine\level\generator\vanilla\event\Cancellable;
use pocketmine\level\generator\vanilla\level\Location;
use pocketmine\Player;

class PlayerMoveEvent extends PlayerEvent implements Cancellable
{
	/** @var Location */
	private $from;
	/** @var Location */
	private $to;

	public function __construct(Player $player, Location $from, Location $to)
	{
		$this->player = $player;
		$this->from = $from;
		$this->to = $to;
	}

	public function getFrom() : Location
	{
		return $this->from;
	}

	public function getTo() : Location
	{
		return $this->to;
	}

	public function setTo(Location $to) : void
	{
		$this->to = $to;
	}
}
