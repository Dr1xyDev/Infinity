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

namespace pocketmine\level\generator\vanilla\event\block;

use pocketmine\level\generator\vanilla\block\Block;
use pocketmine\Player;

/**
 * Called when plants or crops grow.
 */
class BlockGrowEvent extends BaseBlockChangeEvent{

	public function __construct(
		Block $block,
		Block $newState,
		private ?Player $player = null,
	){
		parent::__construct($block, $newState);
	}

	/**
	 * It returns the player which grows the crop.
	 * It returns null when the crop grows by itself.
	 */
	public function getPlayer() : ?Player{
		return $this->player;
	}
}
