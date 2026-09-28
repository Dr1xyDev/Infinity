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

namespace pocketmine\level\generator\vanilla\utils\valueproviders;

use pocketmine\level\generator\vanilla\utils\Random;

class UniformInt extends IntProvider {

	public function __construct(
		private int $minInclusive,
		private int $maxInclusive
	){}

	public static function of(int $minInclusive, int $maxInclusive) : UniformInt {
		return new UniformInt($minInclusive, $maxInclusive);
	}

	public function getMinInclusive() : int {
		return $this->minInclusive;
	}

	public function getMaxInclusive() : int {
		return $this->maxInclusive;
	}

	public function sample(Random $random) : int {
		return $random->nextBoundedInt($this->maxInclusive - $this->minInclusive + 1) + $this->minInclusive;
	}

	public function getMinValue() : int {
		return $this->minInclusive;
	}

	public function getMaxValue() : int {
		return $this->maxInclusive;
	}

	public function getType() : IntProviderType {
		return IntProviderType::UNIFORM;
	}
}
