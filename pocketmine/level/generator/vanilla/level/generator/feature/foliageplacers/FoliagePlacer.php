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

namespace pocketmine\level\generator\vanilla\level\generator\feature\foliageplacers;

use pocketmine\level\generator\vanilla\block\Leaves;
use pocketmine\level\generator\vanilla\level\ChunkManager;
use pocketmine\level\generator\vanilla\level\generator\feature\configurations\TreeConfiguration;
use pocketmine\level\generator\vanilla\level\generator\feature\setter\CollectorSetter;
use pocketmine\level\generator\vanilla\level\generator\feature\TreeFeature;
use pocketmine\level\generator\vanilla\level\generator\feature\trunkplacers\FoliageAttachment;
use pocketmine\level\generator\vanilla\math\Facing;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\utils\Random;
use pocketmine\level\generator\vanilla\utils\valueproviders\IntProvider;
use function abs;
use function min;

abstract class FoliagePlacer {
	public function __construct(
		protected IntProvider $radius,
		protected IntProvider $offset
	){}

	abstract public function type() : FoliagePlacerType;

	abstract public function createFoliage(
		ChunkManager      $level,
		CollectorSetter   $foliageSetter,
		Random            $random,
		TreeConfiguration $config,
		int               $treeHeight,
		FoliageAttachment $foliageAttachment,
		int               $foliageHeight,
		int               $leafRadius,
		int               $offset
	) : void;

	abstract public function foliageHeight(Random $random, int $treeHeight, TreeConfiguration $config) : int;

	public function foliageRadius(Random $random, int $trunkHeight) : int {
		return $this->radius->sample($random);
	}

	public function offset(Random $random) : int {
		return $this->offset->sample($random);
	}

	abstract public function shouldSkipLocation(Random $random, int $dx, int $y, int $dz, int $currentRadius, bool $doubleTrunk) : bool;

	public function shouldSkipLocationSigned(Random $random, int $dx, int $y, int $dz, int $currentRadius, bool $doubleTrunk) : bool{
		if ($doubleTrunk) {
			$minDx = min(abs($dx), abs($dx - 1));
			$minDz = min(abs($dz), abs($dz - 1));
		} else {
			$minDx = abs($dx);
			$minDz = abs($dz);
		}

		return $this->shouldSkipLocation($random, $minDx, $y, $minDz, $currentRadius, $doubleTrunk);
	}

	protected function placeLeavesRow(
		ChunkManager      $level,
		CollectorSetter   $foliageSetter,
		Random            $random,
		TreeConfiguration $config,
		Vector3           $origin,
		int               $currentRadius,
		int               $y,
		bool              $doubleTrunk
	) : void {
		$offset = $doubleTrunk ? 1 : 0;

		for ($dx = -$currentRadius; $dx <= $currentRadius + $offset; $dx++) {
			for ($dz = -$currentRadius; $dz <= $currentRadius + $offset; $dz++) {
				if (!$this->shouldSkipLocationSigned($random, $dx, $y, $dz, $currentRadius, $doubleTrunk)) {
					$this->tryPlaceLeaf($level, $foliageSetter, $random, $config, $origin->add($dx, $y, $dz));
				}
			}
		}
	}

	protected function placeLeavesRowWithHangingLeavesBelow(
		ChunkManager      $level,
		CollectorSetter   $foliageSetter,
		Random            $random,
		TreeConfiguration $config,
		Vector3           $origin,
		int               $currentRadius,
		int               $y,
		bool              $doubleTrunk,
		float             $hangingLeavesChance,
		float             $hangingLeavesExtensionChance
	) : void{
		$this->placeLeavesRow($level, $foliageSetter, $random, $config, $origin, $currentRadius, $y, $doubleTrunk);

		$offset = $doubleTrunk ? 1 : 0;
		$logPos = $origin->down();

		foreach (Facing::HORIZONTAL as $alongEdge) {
			$toEdge = Facing::rotateY($alongEdge, true);
			$offsetToEdge = Facing::isPositive($toEdge) ? $currentRadius + $offset : $currentRadius;
			$pos = $origin->add(0, $y - 1, 0)->getSide($toEdge, $offsetToEdge)->getSide($alongEdge, -$currentRadius);
			$offsetAlongEdge = -$currentRadius;
			while ($offsetAlongEdge < $currentRadius + $offset) {
				$leavesAbove = $foliageSetter->isSet($pos = $pos->up());
				$pos = $pos->down();
				if ($leavesAbove && $this->tryPlaceExtension($level, $foliageSetter, $random, $config, $hangingLeavesChance, $logPos, $pos)) {
					$pos = $pos->down();
					$this->tryPlaceExtension($level, $foliageSetter, $random, $config, $hangingLeavesExtensionChance, $logPos, $pos);
					$pos = $pos->up();
				}

				$offsetAlongEdge++;
				$pos = $pos->getSide($alongEdge);
			}
		}
	}

	private function tryPlaceExtension(ChunkManager $level, CollectorSetter $foliageSetter, Random $random, TreeConfiguration $config, float $chance, Vector3 $logPos, Vector3 $pos) : bool{
		$manhattan = $logPos->subtractVector($pos)->abs();
		$distance = (int) ($manhattan->x + $manhattan->y + $manhattan->z);

		if ($distance >= 7) {
			return false;
		}

		if ($random->nextFloat() > $chance) {
			return false;
		}

		return $this->tryPlaceLeaf($level, $foliageSetter, $random, $config, $pos);
	}

	protected static function tryPlaceLeaf(ChunkManager $level, CollectorSetter $foliageSetter, Random $random, TreeConfiguration $config, Vector3 $pos) : bool{
		$state = $level->getBlockAt($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ());
		$isPersistent = $state instanceof Leaves ? $state->isPersistent() : false;

		if (!$isPersistent && TreeFeature::validTreePos($level, $pos)) {
			//removed waterlogged

			$foliageSetter->set($pos, $config->foliageProvider->getState($random, $pos));
			return true;
		}

		return false;
	}
}
