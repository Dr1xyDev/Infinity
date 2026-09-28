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

/**
 * Called when a new block forms, usually as the result of some action.
 * This could be things like obsidian forming due to collision of lava and water.
 */
class BlockFormEvent extends BaseBlockChangeEvent{

	public function __construct(
		Block $block,
		Block $newState,
		private Block $causingBlock
	){
		parent::__construct($block, $newState);
	}

	/**
	 * Returns the block which caused the target block to form into a new state.
	 */
	public function getCausingBlock() : Block{
		return $this->causingBlock;
	}
}
