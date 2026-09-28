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
use pocketmine\level\generator\vanilla\utils\valueproviders\ConstantInt;
use pocketmine\level\generator\vanilla\utils\valueproviders\IntProvider;

class RandomOffsetPlacement extends PlacementModifier {

	public static function of(IntProvider $xzSpread, IntProvider $ySpread) : RandomOffsetPlacement {
		return new RandomOffsetPlacement($xzSpread, $ySpread);
	}

	public static function vertical(IntProvider $ySpread) : RandomOffsetPlacement {
		return new RandomOffsetPlacement(ConstantInt::of(0), $ySpread);
	}

	public static function horizontal(IntProvider $xzSpread) : RandomOffsetPlacement {
		return new RandomOffsetPlacement($xzSpread, 0);
	}

	public function __construct(
		private IntProvider $xzSpread,
		private IntProvider $ySpread
	){}

	/**
	 * @return Vector3[]
	 */
	public function getPositions(PlacementContext $context, Random $random, Vector3 $origin) : array {
		$scatterX = $origin->getX() + $this->xzSpread->sample($random);
		$scatterY = $origin->getY() + $this->ySpread->sample($random);
		$scatterZ = $origin->getZ() + $this->xzSpread->sample($random);
		return [new Vector3($scatterX, $scatterY, $scatterZ)];
	}
}
