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
use pocketmine\level\generator\vanilla\level\ChunkManager;
use pocketmine\level\generator\vanilla\level\generator\feature\configurations\TreeConfiguration;
use pocketmine\level\generator\vanilla\level\generator\feature\setter\Setter;
use pocketmine\level\generator\vanilla\math\Facing;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\utils\Random;
use pocketmine\level\generator\vanilla\utils\valueproviders\IntProvider;
use function count;
use function max;

class UpwardsBranchingTrunkPlacer extends TrunkPlacer{
	/**
	 * @param Block[] $canGrowThrough
	 */
	public function __construct(
		int $baseHeight,
		int $heightRandA,
		int $heightRandB,
		private IntProvider $extraBranchSteps,
		private float $placeBranchPerLogProbability,
		private IntProvider $extraBranchLength,
		private array $canGrowThrough
	){
		parent::__construct($baseHeight, $heightRandA, $heightRandB);
	}

	public function type() : TrunkPlacerType {
		return TrunkPlacerType::UPWARDS_BRANCHING_TRUNK_PLACER;
	}

	public function placeTrunk(ChunkManager $level, Setter $trunkSetter, Random $random, int $treeHeight, Vector3 $origin, TreeConfiguration $config) : array {
		$attachments = [];

		for ($heightPos = 0; $heightPos < $treeHeight; $heightPos++) {
			$currentHeight = $origin->getY() + $heightPos;
			if ($this->placeLog($level, $trunkSetter, $random, $logPos = new Vector3($origin->getX(), $currentHeight, $origin->getZ()), $config)
				&& $heightPos < $treeHeight - 1
				&& $random->nextFloat() < $this->placeBranchPerLogProbability) {
				$branchDir = Facing::HORIZONTAL[$random->nextRange(0, count(Facing::HORIZONTAL) - 1)];
				$branchLen = $this->extraBranchLength->sample($random);
				$branchPos = max(0, $branchLen - $this->extraBranchLength->sample($random) - 1);
				$branchSteps = $this->extraBranchSteps->sample($random);
				$this->placeBranch($level, $trunkSetter, $random, $treeHeight, $config, $attachments, $logPos, $currentHeight, $branchDir, $branchPos, $branchSteps);
			}

			if ($heightPos == $treeHeight - 1) {
				$attachments[] = new FoliageAttachment($logPos = new Vector3($origin->getX(), $currentHeight + 1, $origin->getZ()), 0, false);
			}
		}

		return $attachments;
	}

	public function placeBranch(
		ChunkManager      $level,
		Setter            $trunkSetter,
		Random            $random,
		int               $treeHeight,
		TreeConfiguration $config,
		array             &$attachments,
		Vector3           $logPos,
		int               $currentHeight,
		int               $branchDir,
		int               $branchPos,
		int               $branchSteps
	) : void {
		$heightAlongBranch = $currentHeight + $branchPos;
		$logX = $logPos->getX();
		$logZ = $logPos->getZ();
		$branchPlacementIndex = $branchPos;

		while ($branchPlacementIndex < $treeHeight && $branchSteps > 0) {
			if ($branchPlacementIndex >= 1) {
				$placementHeight = $currentHeight + $branchPlacementIndex;
				$logX += Facing::OFFSET[$branchDir][0];
				$logZ += Facing::OFFSET[$branchDir][2];
				$heightAlongBranch = $placementHeight;
				if ($this->placeLog($level, $trunkSetter, $random, $logPos = new Vector3($logX, $placementHeight, $logZ), $config)) {
					$heightAlongBranch = $placementHeight + 1;
				}

				$attachments[] = new FoliageAttachment(clone $logPos, 0, false);
			}

			$branchPlacementIndex++;
			$branchSteps--;
		}

		if ($heightAlongBranch - $currentHeight > 1) {
			$foliagePos = new Vector3($logX, $heightAlongBranch, $logZ);
			$attachments[] = new FoliageAttachment($foliagePos, 0, false);
			$attachments[] = new FoliageAttachment($foliagePos->down(2), 0, false);
		}
	}

	protected function validTreePos(ChunkManager $level, Vector3 $pos) : bool{
		$state = $level->getBlockAt($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ());

		$canGrowThrough = false;
		foreach ($this->canGrowThrough as $block) {
			if ($state->isSameType($block)) {
				$canGrowThrough = true;
				break;
			}
		}

		return parent::validTreePos($level, $pos) || $canGrowThrough;
	}
}
