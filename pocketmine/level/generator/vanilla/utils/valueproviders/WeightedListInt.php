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
use function count;
use function max;
use function min;
use const PHP_INT_MAX;
use const PHP_INT_MIN;

class WeightedListInt extends IntProvider {
	private int $minValue;
	private int $maxValue;

	/**
	 * @param IntProvider[] $distribution
	 */
	public function __construct(
		private array $distribution
	){
		$min = PHP_INT_MAX;
		$max = PHP_INT_MIN;

		foreach ($this->distribution as $value) {
			$entryMin = $value->getMinValue();
			$entryMax = $value->getMaxValue();
			$min = min($min, $entryMin);
			$max = max($max, $entryMax);
		}

		$this->minValue = $min;
		$this->maxValue = $max;
	}

	/**
	 * @param IntProvider[] $value
	 */
	public static function of(array $value) : WeightedListInt {
		return new WeightedListInt($value);
	}

	public function sample(Random $random) : int {
		return $this->distribution[$random->nextRange(0, count($this->distribution) - 1)]->sample($random);
	}

	public function getMinValue() : int {
		return $this->minValue;
	}

	public function getMaxValue() : int {
		return $this->maxValue;
	}

	public function getType() : IntProviderType {
		return IntProviderType::WEIGHTED_LIST;
	}
}
