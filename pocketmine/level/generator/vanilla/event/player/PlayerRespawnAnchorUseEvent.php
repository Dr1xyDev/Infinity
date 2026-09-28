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

use pocketmine\level\generator\vanilla\block\Block;
use pocketmine\level\generator\vanilla\event\Cancellable;
use pocketmine\Player;

class PlayerRespawnAnchorUseEvent extends PlayerEvent implements Cancellable
{
	public const ACTION_EXPLODE = 0;
	public const ACTION_SET_SPAWN = 1;

	public function __construct(
		Player $player,
		protected Block $block,
		private int $action = self::ACTION_EXPLODE
	) {
		$this->player = $player;
	}

	public function getBlock() : Block
	{
		return $this->block;
	}

	public function getAction() : int
	{
		return $this->action;
	}

	public function setAction(int $action) : void
	{
		$this->action = $action;
	}
}
