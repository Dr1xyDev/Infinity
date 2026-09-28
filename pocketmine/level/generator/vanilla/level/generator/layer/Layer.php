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

namespace pocketmine\level\generator\vanilla\level\generator\layer;

use pocketmine\level\generator\vanilla\level\biome\BiomeFactory;
use pocketmine\level\generator\vanilla\level\biome\BiomeIds;
use pocketmine\level\generator\vanilla\utils\Random;
use function count;

abstract class Layer {

	public const int OCEAN_ID = 0;
	public const int WARM_ID = 1;
	public const int MEDIUM_ID = 2;
	public const int COLD_ID = 3;
	public const int ICE_ID = 4;

	public const int SPECIAL_MASK = 0xf00;
	public const int SPECIAL_SHIFT = 8;

	private Random $random;
	private int $seed;

	public function __construct(int $seed){
		$this->random = new Random();
		$this->seed = $seed;
	}

	abstract public function fillArea(LayerData $layerData, int $xo, int $yo, int $w, int $h) : void;

	public function initRandom(int $x, int $z) : void{
		$this->random->setSeed($this->seed);
		$this->random->setSeed($x * $this->random->nextInt() + $z * $this->random->nextInt() ^ $this->seed);
	}

	public function nextRandom(int $number) : int {
		return $this->random->nextBoundedInt($number);
	}

	protected function isSame(int $a, int $b) : bool {
		if ($a === $b) {
			return true;
		}

		if ($a === BiomeIds::MESA_ROCK || $a === BiomeIds::MESA_CLEAR_ROCK) {
			return $b === BiomeIds::MESA_ROCK || $b === BiomeIds::MESA_CLEAR_ROCK;
		}

		$biomeFactory = BiomeFactory::getInstance();
		$biome1 = $biomeFactory->get($a);
		$biome2 = $biomeFactory->get($b);
		if ($biome1 !== null && $biome2 !== null) {
			return $biome1->getCategory() === $biome2->getCategory();
		}

		return false;
	}

	protected function isOcean(int $id) : bool {
		return $id == BiomeIds::OCEAN || $id == BiomeIds::DEEP_OCEAN || $id == BiomeIds::FROZEN_OCEAN;
	}

	/**
	 * @param int[] $list
	 */
	protected function random(array $list) : int {
		return $list[$this->nextRandom(count($list))];
	}

	protected function modeOrRandom(int $a, int $b, int $c, int $d) : int{
		if ($b === $c && $c === $d) {
			return $b;
		}
		if ($a === $b && $a === $c) {
			return $a;
		}
		if ($a === $b && $a === $d) {
			return $a;
		}
		if ($a === $c && $a === $d) {
			return $a;
		}
		if ($a === $b && $c !== $d) {
			return $a;
		}
		if ($a === $c && $b !== $d) {
			return $a;
		}
		if ($a === $d && $b !== $c) {
			return $a;
		}
		if ($b === $c && $a !== $d) {
			return $b;
		}
		if ($b === $d && $a !== $c) {
			return $b;
		}
		if ($c === $d && $a !== $b) {
			return $c;
		}

		return $this->random([$a, $b, $c, $d]);
	}
}
