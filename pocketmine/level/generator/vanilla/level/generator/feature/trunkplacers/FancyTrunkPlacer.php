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

use pocketmine\level\generator\vanilla\block\Block;
use pocketmine\level\generator\vanilla\block\utils\PillarRotationHelper;
use pocketmine\level\generator\vanilla\level\ChunkManager;
use pocketmine\level\generator\vanilla\level\generator\feature\configurations\TreeConfiguration;
use pocketmine\level\generator\vanilla\level\generator\feature\setter\Setter;
use pocketmine\level\generator\vanilla\math\Axis;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\utils\Random;
use function abs;
use function cos;
use function floor;
use function max;
use function min;
use function pow;
use function sin;
use function sqrt;
use const M_PI;

class FancyTrunkPlacer extends TrunkPlacer{

	private const float TRUNK_HEIGHT_SCALE = 0.618;
	private const float CLUSTER_DENSITY_MAGIC = 1.382;
	private const float BRANCH_SLOPE = 0.381;
	private const float BRANCH_LENGTH_MAGIC = 0.328;

	public function type() : TrunkPlacerType {
		return TrunkPlacerType::FANCY_TRUNK_PLACER;
	}

	public function placeTrunk(ChunkManager $level, Setter $trunkSetter, Random $random, int $treeHeight, Vector3 $origin, TreeConfiguration $config) : array {
		$assumedFoliageHeight = 5;
		$height = $treeHeight + 2;
		$trunkHeight = (int) floor($height * self::TRUNK_HEIGHT_SCALE);
		$this->setDirtAt($level, $trunkSetter, $random, $origin->down(), $config);
		$foliageDensity = 1.0;
		$clustersPerY = min(1, floor(self::CLUSTER_DENSITY_MAGIC + pow($foliageDensity * $height / 13.0, 2.0)));
		$trunkTop = $origin->getY() + $trunkHeight;
		$relativeY = $height - $assumedFoliageHeight;
		$foliageCoords = [];
		$foliageCoords[] = new FoliageCoords($origin->up($relativeY), $trunkTop);

		for (; $relativeY >= 0; $relativeY--) {
			$treeShape = self::treeShape($height, $relativeY);
			if (!($treeShape < 0.0)) {
				for ($i = 0; $i < $clustersPerY; $i++) {
					$widthScale = 1.0;
					$radius = $widthScale * $treeShape * ($random->nextFloat() + self::BRANCH_LENGTH_MAGIC);
					$angle = $random->nextFloat() * 2.0 * M_PI;
					$x = $radius * sin($angle) + 0.5;
					$z = $radius * cos($angle) + 0.5;
					$checkStart = $origin->add(floor($x), $relativeY - 1, floor($z));
					$checkEnd = $checkStart->up(5);
					if ($this->makeLimb($level, $trunkSetter, $random, $checkStart, $checkEnd, false, $config)) {
						$dx = $origin->getX() - $checkStart->getX();
						$dz = $origin->getZ() - $checkStart->getZ();
						$branchHeight = $checkStart->getY() - sqrt($dx * $dx + $dz * $dz) * self::BRANCH_SLOPE;
						$branchTop = $branchHeight > $trunkTop ? $trunkTop : (int) $branchHeight;
						$checkBranchBase = new Vector3($origin->getX(), $branchTop, $origin->getZ());
						if ($this->makeLimb($level, $trunkSetter, $random, $checkBranchBase, $checkStart, false, $config)) {
							$foliageCoords[] = new FoliageCoords($checkStart, $checkBranchBase->getY());
						}
					}
				}
			}
		}

		$this->makeLimb($level, $trunkSetter, $random, $origin, $origin->up($trunkHeight), true, $config);
		$this->makeBranches($level, $trunkSetter, $random, $height, $origin, $foliageCoords, $config);
		$attachments = [];
		foreach ($foliageCoords as $foliageCoord) {
			if ($this->trimBranches($height, $foliageCoord->getBranchBase() - $origin->getY())) {
				$attachments[] = $foliageCoord->attachment();
			}
		}

		return $attachments;
	}

	private function makeLimb(ChunkManager $level, Setter $trunkSetter, Random $random, Vector3 $startPos, Vector3 $endPos, bool $doPlace, TreeConfiguration $config) : bool {
		if (!$doPlace && $startPos->equals($endPos)) {
			return true;
		} else {
			$delta = $endPos->add(-$startPos->getX(), -$startPos->getY(), -$startPos->getZ());
			$steps = $this->getSteps($delta);
			$dx = (float) $delta->getX() / $steps;
			$dy = (float) $delta->getY() / $steps;
			$dz = (float) $delta->getZ() / $steps;

			for ($i = 0; $i <= $steps; $i++) {
				$blockPos = $startPos->add(floor(0.5 + $i * $dx), floor(0.5 + $i * $dy), floor(0.5 + $i * $dz));
				if ($doPlace) {
					$this->placeLog($level, $trunkSetter, $random, $blockPos, $config, function (Block $state) use ($startPos, $blockPos) : Block {
						$pillarRotations = PillarRotationHelper::getRotations($state);
						if ($pillarRotations !== null) {
							$state->setDamage($pillarRotations->fromAxis($this->getLogAxis($startPos, $blockPos)));
						}

						return $state;
					});
				} elseif (!$this->isFree($level, $blockPos)) {
					return false;
				}
			}

			return true;
		}
}

	private function getSteps(Vector3 $pos) : int {
		$absX = (int) abs($pos->getX());
		$absY = (int) abs($pos->getY());
		$absZ = (int) abs($pos->getZ());
		return (int) max($absX, $absY, $absZ);
	}

	private function getLogAxis(Vector3 $startPos, Vector3 $blockPos) : int {
		$axis = Axis::Y;
		$xdiff = abs($blockPos->getX() - $startPos->getX());
		$zdiff = abs($blockPos->getZ() - $startPos->getZ());
		$maxdiff = max($xdiff, $zdiff);
		if ($maxdiff > 0) {
			if ($xdiff == $maxdiff) {
				$axis = Axis::X;
			} else {
				$axis = Axis::Z;
			}
		}

		return $axis;
	}

	private function trimBranches(int $height, int $localY) : bool {
		return $localY >= $height * 0.2;
	}

	/**
	 * @param FoliageCoords[] $foliageCoords
	 */
	private function makeBranches(ChunkManager $level, Setter $trunkSetter, Random $random, int $height, Vector3 $origin, array $foliageCoords, TreeConfiguration $config) : void{
		foreach ($foliageCoords as $endCoord) {
			$branchBase = $endCoord->getBranchBase();
			$baseCoord = new Vector3($origin->getX(), $branchBase, $origin->getZ());
			if (!$baseCoord->equals($endCoord->attachment()->pos()) && $this->trimBranches($height, $branchBase - $origin->getY())) {
				$this->makeLimb($level, $trunkSetter, $random, $baseCoord, $endCoord->attachment()->pos(), true, $config);
			}
		}
	}

	private static function treeShape(int $height, int $y) : float{
		if ($y < $height * 0.3) {
			return -1.0;
		} else {
			$radius = $height / 2.0;
			$adjacent = $radius - $y;
			$distance = sqrt($radius * $radius - $adjacent * $adjacent);
			if ($adjacent == 0.0) {
				$distance = $radius;
			} elseif (abs($adjacent) >= $radius) {
				return 0.0;
			}

			return $distance * 0.5;
		}
	}
}
