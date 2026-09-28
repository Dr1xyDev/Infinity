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

namespace pocketmine\level\generator\vanilla\level\generator\heightproviders;

use pocketmine\level\generator\vanilla\level\ChunkManager;
use pocketmine\level\generator\vanilla\level\generator\MathHelper;
use pocketmine\level\generator\vanilla\level\generator\verticalanchor\VerticalAnchor;
use pocketmine\level\generator\vanilla\utils\Random;

class TrapezoidHeight implements HeightProvider {

	public static function of(VerticalAnchor $minInclusive, VerticalAnchor $maxInclusive, int $plateau) : TrapezoidHeight {
		return new self($minInclusive, $maxInclusive, $plateau);
	}

	public function __construct(
		private VerticalAnchor $minInclusive,
		private VerticalAnchor $maxInclusive,
		private int $plateau
	){}

	public function sample(Random $random, ChunkManager $level) : int {
		$min = $this->minInclusive->resolveY($level);
		$max = $this->maxInclusive->resolveY($level);
		if ($min > $max) {
			return $min;
		} else {
			$range = $max - $min;
			if ($this->plateau >= $range) {
				return MathHelper::randomBetweenInclusive($random, $min, $max);
			} else {
				$plateauStart = ($range - $this->plateau) / 2;
				$plateauEnd = $range - $plateauStart;
				return $min + MathHelper::randomBetweenInclusive($random, 0, $plateauEnd) + MathHelper::randomBetweenInclusive($random, 0, $plateauStart);
			}
		}
	}
}
