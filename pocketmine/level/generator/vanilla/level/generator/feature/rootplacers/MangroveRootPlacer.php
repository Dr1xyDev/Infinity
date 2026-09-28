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

namespace pocketmine\level\generator\vanilla\level\generator\feature\rootplacers;

use pocketmine\level\generator\vanilla\level\ChunkManager;
use pocketmine\level\generator\vanilla\level\generator\feature\configurations\TreeConfiguration;
use pocketmine\level\generator\vanilla\level\generator\feature\setter\Setter;
use pocketmine\level\generator\vanilla\level\generator\feature\stateproviders\BlockStateProvider;
use pocketmine\level\generator\vanilla\math\Facing;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\utils\Random;
use pocketmine\level\generator\vanilla\utils\valueproviders\IntProvider;
use function count;

class MangroveRootPlacer extends RootPlacer {
	public const int ROOT_WIDTH_LIMIT = 8;
	public const int ROOT_LENGTH_LIMIT = 15;

	public function __construct(
		IntProvider $trunkOffsetY,
		BlockStateProvider $rootProvider,
		?AboveRootPlacement $aboveRootPlacement,
		private MangroveRootPlacement $mangroveRootPlacement
	){
		parent::__construct($trunkOffsetY, $rootProvider, $aboveRootPlacement);
	}

	public function placeRoots(ChunkManager $level, Setter $rootSetter, Random $random, Vector3 $origin, Vector3 $trunkOrigin, TreeConfiguration $config) : bool{
		$rootPositions = [];
		$columnPos = clone $origin;

		while ($columnPos->getY() < $trunkOrigin->getY()) {
			if (!$this->canPlaceRoot($level, $columnPos)) {
				return false;
			}

			$columnPos = $columnPos->up();
		}

		$rootPositions[] = $trunkOrigin->down();

		foreach (Facing::HORIZONTAL as $dir) {
			$pos = $trunkOrigin->getSide($dir);
			$positionsInDirection = [];
			if (!$this->simulateRoots($level, $random, $pos, $dir, $trunkOrigin, $positionsInDirection, 0)) {
				return false;
			}

			foreach ($positionsInDirection as $positionInDirection) {
				$rootPositions[] = $positionInDirection;
			}

			$rootPositions[] = $trunkOrigin->getSide($dir);
		}

		foreach ($rootPositions as $rootPos) {
			$this->placeRoot($level, $rootSetter, $random, $rootPos, $config);
		}

		return true;
	}

	/**
	 * @param Vector3[] $rootPositions
	 */
	protected function simulateRoots(ChunkManager $level, Random $random, Vector3 $rootPos, int $dir, Vector3 $rootOrigin, array &$rootPositions, int $layer) : bool{
		$maxRootLength = $this->mangroveRootPlacement->maxRootLength();
		if ($layer != $maxRootLength && count($rootPositions) <= $maxRootLength) {
			foreach ($this->potentialRootPositions($rootPos, $dir, $random, $rootOrigin) as $pos) {
				if ($this->canPlaceRoot($level, $pos)) {
					$rootPositions[] = $pos;
					if (!$this->simulateRoots($level, $random, $pos, $dir, $rootOrigin, $rootPositions, $layer + 1)) {
						return false;
					}
				}
			}

			return true;
		} else {
			return false;
		}
	}

	/**
	 * @return Vector3[]
	 */
	protected function potentialRootPositions(Vector3 $pos, int $prevDir, Random $random, Vector3 $rootOrigin) : array {
		$below = $pos->down();
		$nextTo = $pos->getSide($prevDir);
		$manhattan = $rootOrigin->subtractVector($pos)->abs();
		$width = (int) ($manhattan->x + $manhattan->y + $manhattan->z);
		$maxRootWidth = $this->mangroveRootPlacement->maxRootWidth();
		$randomSkewChance = $this->mangroveRootPlacement->randomSkewChance();
		if ($width > $maxRootWidth - 3 && $width <= $maxRootWidth) {
			return $random->nextFloat() < $randomSkewChance ? [$below, $nextTo->down()] : [$below];
		} elseif ($width > $maxRootWidth) {
			return [$below];
		} elseif ($random->nextFloat() < $randomSkewChance) {
			return [$below];
		} else {
			return $random->nextBoolean() ? [$nextTo] : [$below];
		}
	}

	protected function canPlaceRoot(ChunkManager $level, Vector3 $pos) : bool{
		$state = $level->getBlockAt($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ());

		$isPlacement = false;
		foreach ($this->mangroveRootPlacement->canGrowThrough() as $block) {
			if ($block->isSameType($state)) {
				$isPlacement = true;
				break;
			}
		}

		return parent::canPlaceRoot($level, $pos) || $isPlacement;
	}

	protected function placeRoot(ChunkManager $level, Setter $rootSetter, Random $random, Vector3 $pos, TreeConfiguration $config) : void{
		$state = $level->getBlockAt($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ());

		$isPlacement = false;
		foreach ($this->mangroveRootPlacement->muddyRootsIn() as $block) {
			if ($block->isSameType($state)) {
				$isPlacement = true;
				break;
			}
		}

		if ($isPlacement) {
			$muddyRoots = $this->mangroveRootPlacement->muddyRootsProvider()->getState($random, $pos);
			$rootSetter->set($pos, $this->getPotentiallyWaterloggedState($level, $pos, $muddyRoots));
		} else {
			parent::placeRoot($level, $rootSetter, $random, $pos, $config);
		}
	}

	public function type() : RootPlacerType{
		return RootPlacerType::MANGROVE_ROOT_PLACER;
	}
}
