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

namespace pocketmine\level\generator\vanilla\level\generator\feature\trunkplacers;

use pocketmine\level\generator\vanilla\block\BlockIds;
use pocketmine\level\generator\vanilla\block\Log;
use pocketmine\level\generator\vanilla\level\ChunkManager;
use pocketmine\level\generator\vanilla\level\generator\feature\configurations\TreeConfiguration;
use pocketmine\level\generator\vanilla\level\generator\feature\Feature;
use pocketmine\level\generator\vanilla\level\generator\feature\setter\Setter;
use pocketmine\level\generator\vanilla\level\generator\feature\TreeFeature;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\utils\Random;

abstract class TrunkPlacer {

	private const int MAX_BASE_HEIGHT = 32;
	private const int MAX_RAND = 24;
	public const int MAX_HEIGHT = 80;

	public function __construct(
		protected int $baseHeight,
		protected int $heightRandA,
		protected int $heightRandB
	){}

	abstract public function type() : TrunkPlacerType;

	/**
	 * @return FoliageAttachment[]
	 */
	abstract public function placeTrunk(ChunkManager $level, Setter $trunkSetter, Random $random, int $treeHeight, Vector3 $origin, TreeConfiguration $config) : array;

	public function getTreeHeight(Random $random) : int {
		return $this->baseHeight + $random->nextBoundedInt($this->heightRandA + 1) + $random->nextBoundedInt($this->heightRandB + 1);
	}

	private static function isDirt(ChunkManager $level, Vector3 $pos) : bool {
		$state = $level->getBlockAt($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ());
		$stateLegacyId = $state->getId();

		return Feature::isDirt($state) && $stateLegacyId !== BlockIds::GRASS && $stateLegacyId !== BlockIds::MYCELIUM;
	}

	protected static function setDirtAt(ChunkManager $level, Setter $trunkSetter, Random $random, Vector3 $pos, TreeConfiguration $config) : void {
		if ($config->forceDirt || !self::isDirt($level, $pos)) {
			$trunkSetter->set($pos, $config->dirtProvider->getState($random, $pos));
		}
	}

	protected function placeLog(ChunkManager $level, Setter $trunkSetter, Random $random, Vector3 $pos, TreeConfiguration $config, ?\Closure $stateModifier = null) : bool {
		if ($this->validTreePos($level, $pos)) {
			$state = $config->trunkProvider->getState($random, $pos);
			if ($stateModifier !== null) {
				$state = $stateModifier($state);
			}

			$trunkSetter->set($pos, $state);
			return true;
		} else {
			return false;
		}
	}

	protected function placeLogIfFree(ChunkManager $level, Setter $trunkSetter, Random $random, Vector3 $pos, TreeConfiguration $config) : void {
		if ($this->isFree($level, $pos)) {
			$this->placeLog($level, $trunkSetter, $random, $pos, $config);
		}
	}

	protected function validTreePos(ChunkManager $level, Vector3 $pos) : bool {
		return TreeFeature::validTreePos($level, $pos);
	}

	public function isFree(ChunkManager $level, Vector3 $pos) : bool{
		$state = $level->getBlockAt($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ());

		return $this->validTreePos($level, $pos) || $state instanceof Log;
	}
}
