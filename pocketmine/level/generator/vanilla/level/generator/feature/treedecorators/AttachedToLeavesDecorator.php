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

namespace pocketmine\level\generator\vanilla\level\generator\feature\treedecorators;

use pocketmine\level\generator\vanilla\level\generator\feature\stateproviders\BlockStateProvider;
use pocketmine\level\generator\vanilla\level\Level;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\utils\Utils;
use function count;

class AttachedToLeavesDecorator extends TreeDecorator{
	/**
	 * @param int[] $directions
	 */
	public function __construct(
		private float $probability,
		private int $exclusionRadiusXZ,
		private int $exclusionRadiusY,
		private BlockStateProvider $blockProvider,
		private int $requiredEmptyBlocks,
		private array $directions
	) {}

	protected function type() : TreeDecoratorType {
		return TreeDecoratorType::ATTACHED_TO_LEAVES;
	}

	public function place(TreeDecoratorContext $context) : void{
		$propaguleBlacklist = [];
		$random = $context->random();

		foreach (Utils::shuffledCopy($context->leaves(), $random) as $leafPos) {
			/** @var Vector3 $leafPos */
			$direction = $this->directions[$random->nextRange(0, count($this->directions) - 1)];
			$placementPos = $leafPos->getSide($direction);
			if (
				!isset($propaguleBlacklist[Level::blockHash($placementPos->getFloorX(), $placementPos->getFloorY(), $placementPos->getFloorZ())]) &&
				$random->nextFloat() < $this->probability &&
				$this->hasRequiredEmptyBlocks($context, $leafPos, $direction)
			) {
				$corner1 = $placementPos->add(-$this->exclusionRadiusXZ, -$this->exclusionRadiusY, -$this->exclusionRadiusXZ);
				$corner2 = $placementPos->add($this->exclusionRadiusXZ, $this->exclusionRadiusY, $this->exclusionRadiusXZ);

				for ($x = $corner1->x; $x <= $corner2->x; $x++) {
					for ($y = $corner1->y; $y <= $corner2->y; $y++) {
						for ($z = $corner1->z; $z <= $corner2->z; $z++) {
							$propaguleBlacklist[Level::blockHash($x, $y, $z)] = true;
						}
					}
				}

				$context->setBlock($placementPos, $this->blockProvider->getState($random, $placementPos));
			}
		}
	}

	private function hasRequiredEmptyBlocks(TreeDecoratorContext $context, Vector3 $leafPos, int $direction) : bool {
		for ($i = 1; $i <= $this->requiredEmptyBlocks; $i++) {
			$offsetPos = $leafPos->getSide($direction, $i);
			if (!$context->isAir($offsetPos)) {
				return false;
			}
		}

		return true;
	}
}
