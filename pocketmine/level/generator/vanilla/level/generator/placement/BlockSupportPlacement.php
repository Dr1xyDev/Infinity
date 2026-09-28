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

namespace pocketmine\level\generator\vanilla\level\generator\placement;

use pocketmine\level\generator\vanilla\level\generator\feature\blocksupport\BlockSupport;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\utils\Random;

class BlockSupportPlacement extends PlacementModifier {

	public function __construct(
		private BlockSupport $blockSupport
	){}

	public function getPositions(PlacementContext $context, Random $random, Vector3 $origin) : array{
		return $this->blockSupport->isValidPosition($context->getLevel(), $origin) ? [$origin] : [];
	}
}
