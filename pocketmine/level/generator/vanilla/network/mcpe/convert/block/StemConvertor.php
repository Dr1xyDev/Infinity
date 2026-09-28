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
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ProtocolInfo;

final class StemConvertor implements BlockConvertor {
	public function __construct() {
		//NOOP
	}

	public function to(Block $block, int $protocolVersion) : ?Block {
		if ($protocolVersion < ProtocolInfo::PROTOCOL_428 && $block->getDamage() > 7) {
			return BlockFactory::get($block->getId(), $block->getDamage() & 7);
		}

		return null;
	}
}
