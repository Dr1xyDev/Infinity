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
use function max;
use function min;

class ClampedInt extends IntProvider {

	public function __construct(
		private IntProvider $source,
		private int $minInclusive,
		private int $maxInclusive
	){}

	public static function of(IntProvider $source, int $minInclusive, int $maxInclusive) : ClampedInt {
		return new self($source, $minInclusive, $maxInclusive);
	}

	public function sample(Random $random) : int {
		return min(max($this->source->sample($random), $this->minInclusive), $this->maxInclusive);
	}

	public function getMinValue() : int {
		return max($this->minInclusive, $this->source->getMinValue());
	}

	public function getMaxValue() : int {
		return min($this->maxInclusive, $this->source->getMaxValue());
	}

	public function getType() : IntProviderType {
		return IntProviderType::CONSTANT;
	}
}
