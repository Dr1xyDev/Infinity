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

use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\utils\Random;

class HeightmapPlacement extends PlacementModifier {

	public function __construct(
		private HeightmantType $type
	){}

	public function getPositions(PlacementContext $context, Random $random, Vector3 $origin) : array{
		$x = $origin->getX();
		$z = $origin->getZ();
		$height = $this->type->getHighestWorkableBlock($context->getLevel(), $x, $z);
		return $height !== -1 ? [new Vector3($x, $height, $z)] : [];
	}
}
