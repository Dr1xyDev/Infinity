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

use pocketmine\level\generator\vanilla\level\generator\heightproviders\HeightProvider;
use pocketmine\level\generator\vanilla\level\generator\heightproviders\TrapezoidHeight;
use pocketmine\level\generator\vanilla\level\generator\heightproviders\UniformHeight;
use pocketmine\level\generator\vanilla\level\generator\verticalanchor\VerticalAnchor;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\utils\Random;

class HeightRangePlacement extends PlacementModifier {

	public static function of(HeightProvider $height) : HeightRangePlacement {
		return new self($height);
	}

	public static function uniform(VerticalAnchor $minInclusive, VerticalAnchor $maxInclusive) : HeightRangePlacement {
		return new self(UniformHeight::of($minInclusive, $maxInclusive));
	}

	public static function triangle(VerticalAnchor $minInclusive, VerticalAnchor $maxInclusive) : HeightRangePlacement {
		return new self(TrapezoidHeight::of($minInclusive, $maxInclusive, 0));
	}

	public function __construct(
		private HeightProvider $height
	){}

	public function getPositions(PlacementContext $context, Random $random, Vector3 $origin) : array{
		return [new Vector3($origin->getFloorX(), $this->height->sample($random, $context->getLevel()), $origin->getFloorZ())];
	}
}
