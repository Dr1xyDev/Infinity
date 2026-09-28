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
use pocketmine\level\generator\vanilla\math\Facing;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\utils\Random;
use pocketmine\level\generator\vanilla\utils\valueproviders\IntProvider;
use pocketmine\level\generator\vanilla\utils\valueproviders\UniformInt;
use function abs;
use function count;
use function max;

class CherryTrunkPlacer extends TrunkPlacer{

	private UniformInt $secondBranchStartOffsetFromTop;

	public function __construct(
		int $baseHeight,
		int $heightRandA,
		int $heightRandB,
		private IntProvider $branchCount,
		private IntProvider $branchHorizontalLength,
		private UniformInt $branchStartOffsetFromTop,
		private IntProvider $branchEndOffsetFromTop,
	){
		parent::__construct($baseHeight, $heightRandA, $heightRandB);
		$this->secondBranchStartOffsetFromTop = UniformInt::of($branchStartOffsetFromTop->getMinValue(), $branchStartOffsetFromTop->getMaxValue() - 1);
	}

	public function type() : TrunkPlacerType {
		return TrunkPlacerType::CHERRY_TRUNK_PLACER;
	}

	public function placeTrunk(ChunkManager $level, Setter $trunkSetter, Random $random, int $treeHeight, Vector3 $origin, TreeConfiguration $config) : array {
		$this->setDirtAt($level, $trunkSetter, $random, $origin->down(), $config);
		$firstBranchOffsetFromOrigin = max(0, $treeHeight - 1 + $this->branchStartOffsetFromTop->sample($random));
		$secondBranchOffsetFromOrigin = max(0, $treeHeight - 1 + $this->secondBranchStartOffsetFromTop->sample($random));
		if ($secondBranchOffsetFromOrigin >= $firstBranchOffsetFromOrigin) {
			$secondBranchOffsetFromOrigin++;
		}

		$branchCount = $this->branchCount->sample($random);
		$hasMiddleBranch = $branchCount == 3;
		$hasBothSideBranches = $branchCount >= 2;
		if ($hasMiddleBranch) {
			$trunkHeight = $treeHeight;
		} elseif ($hasBothSideBranches) {
			$trunkHeight = max($firstBranchOffsetFromOrigin, $secondBranchOffsetFromOrigin) + 1;
		} else {
			$trunkHeight = $firstBranchOffsetFromOrigin + 1;
		}

		for ($y = 0; $y < $trunkHeight; $y++) {
			$this->placeLog($level, $trunkSetter, $random, $origin->up($y), $config);
		}

		$attachments = [];
		if ($hasMiddleBranch) {
			$attachments[] = new FoliageAttachment($origin->up($trunkHeight), 0, false);
		}

		$treeDirection = Facing::HORIZONTAL[$random->nextRange(0, count(Facing::HORIZONTAL) - 1)];

		$sidewaysStateModifier = function (Block $state) use ($treeDirection) : Block {
			$pillarRotations = PillarRotationHelper::getRotations($state);
			if ($pillarRotations !== null) {
				$state->setDamage($pillarRotations->fromAxis(Facing::axis($treeDirection)));
			}

			return $state;
		};

		$attachments[] = $this->generateBranch(
				$level,
				$trunkSetter,
				$random,
				$treeHeight,
				$origin,
				$config,
				$sidewaysStateModifier,
				$treeDirection,
				$firstBranchOffsetFromOrigin,
				$firstBranchOffsetFromOrigin < $trunkHeight - 1
		);

		if ($hasBothSideBranches) {
			$attachments[] = $this->generateBranch(
				$level,
				$trunkSetter,
				$random,
				$treeHeight,
				$origin,
				$config,
				$sidewaysStateModifier,
				Facing::opposite($treeDirection),
				$secondBranchOffsetFromOrigin,
				$secondBranchOffsetFromOrigin < $trunkHeight - 1
			);
		}

		return $attachments;
	}

	public function generateBranch(
		ChunkManager      $level,
		Setter            $trunkSetter,
		Random            $random,
		int               $treeHeight,
		Vector3           $origin,
		TreeConfiguration $config,
		\Closure          $sidewaysStateModifier,
		int               $branchDirection,
		int               $offsetFromOrigin,
		bool              $middleContinuesUpwards
	) : FoliageAttachment {
		$logPos = $origin->up($offsetFromOrigin);
		$branchEndPosOffsetFromOrigin = $treeHeight - 1 + $this->branchEndOffsetFromTop->sample($random);
		$extendBranchAwayFromTrunk = $middleContinuesUpwards || $branchEndPosOffsetFromOrigin < $offsetFromOrigin;
		$distanceToTrunk = $this->branchHorizontalLength->sample($random) + ($extendBranchAwayFromTrunk ? 1 : 0);
		$branchEndPos = ($distanceToTrunk === 0 ? $origin : $origin->getSide($branchDirection, $distanceToTrunk))->up($branchEndPosOffsetFromOrigin);
		$stepsHorizontally = $extendBranchAwayFromTrunk ? 2 : 1;
;
		for ($i = 0; $i < $stepsHorizontally; $i++) {
			$this->placeLog($level, $trunkSetter, $random, $logPos = $logPos->getSide($branchDirection), $config, $sidewaysStateModifier);
		}

		$verticalDirection = $branchEndPos->getY() > $logPos->getY() ? Facing::UP : Facing::DOWN;

		while (true) {
			$manhattan = $branchEndPos->subtractVector($logPos)->abs();
			$distance = (int) ($manhattan->x + $manhattan->y + $manhattan->z);
			if ($distance == 0) {
				return new FoliageAttachment($branchEndPos->up(), 0, false);
			}

			$chanceToGrowVertically = (float) abs($branchEndPos->getY() - $logPos->getY()) / $distance;
			$growVertically = $random->nextFloat() < $chanceToGrowVertically;
			$logPos = $logPos->getSide($growVertically ? $verticalDirection : $branchDirection);
			$this->placeLog($level, $trunkSetter, $random, $logPos, $config, $growVertically ? null : $sidewaysStateModifier);
		}
	}
}
