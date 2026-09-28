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

namespace pocketmine\level\generator\vanilla\network\mcpe\convert\block;

use pocketmine\level\generator\vanilla\block\Block;
use pocketmine\level\generator\vanilla\block\BlockFactory;
use pocketmine\level\generator\vanilla\block\Slab;

final class NewSlabConvertor implements BlockConvertor {
	public function __construct(
		private int $targetLegacyId,
		private int $minimalProtocol
	) {}

	public function to(Block $block, int $protocolVersion) : ?Block {
		if ($block instanceof Slab && $protocolVersion < $this->minimalProtocol) {
			return BlockFactory::get($this->targetLegacyId, ($block->isTop() ? 0x08 : 0x0));
		}

		return null;
	}
}
