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

namespace pocketmine\level\generator\vanilla\block\utils\pattern;

use pocketmine\level\generator\vanilla\block\Block;

class BlockMatcher{

	private int $blockId;

	private function __construct(int $blockId){
		$this->blockId = $blockId;
	}

	public static function forBlock(Block $block) : self{
		return new self($block->getId());
	}

	public static function forBlockId(int $blockId) : self{
		return new self($blockId);
	}

	public function apply(Block $block) : bool{
		return $block->getId() === $this->blockId;
	}
}
