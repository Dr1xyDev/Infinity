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

use pocketmine\level\generator\vanilla\block\BlockIds;
use pocketmine\level\generator\vanilla\block\Vine;
use pocketmine\level\generator\vanilla\level\generator\feature\stateproviders\BlockStateProvider;
use pocketmine\level\generator\vanilla\level\generator\feature\TreeFeature;
use pocketmine\level\generator\vanilla\math\AxisAlignedBB;
use pocketmine\level\generator\vanilla\math\Vector3;
use function count;
use function max;
use function min;

class PlaceOnGroundDecorator extends TreeDecorator{
	public function __construct(
		private int $tries,
		private int $radius,
		private int $height,
		private BlockStateProvider $blockStateProvider
	) {}

	protected function type() : TreeDecoratorType {
		return TreeDecoratorType::PLACE_ON_GROUND;
	}

	public function place(TreeDecoratorContext $context) : void{
		$blockPositions = TreeFeature::getLowestTrunkOrRootOfTree($context);
		if (count($blockPositions) !== 0) {
			$origin = $blockPositions[0];
			$minY = $origin->getY();
			$minX = $origin->getX();
			$maxX = $origin->getX();
			$minZ = $origin->getZ();
			$maxZ = $origin->getZ();

			foreach ($blockPositions as $position) {
				if ($position->getY() == $minY) {
					$minX = min($minX, $position->getX());
					$maxX = max($maxX, $position->getX());
					$minZ = min($minZ, $position->getZ());
					$maxZ = max($maxZ, $position->getZ());
				}
			}

			$random = $context->random();
			$bb = (new AxisAlignedBB($minX, $minY, $minZ, $maxX, $minY, $maxZ))->expand($this->radius, $this->height, $this->radius);

			for ($i = 0; $i < $this->tries; $i++) {
				$this->attemptToPlaceBlockAbove($context, new Vector3(
					$random->nextRange((int) $bb->minX, (int) $bb->maxX),
					$random->nextRange((int) $bb->minY, (int) $bb->maxY),
					$random->nextRange((int) $bb->minZ, (int) $bb->maxZ)
				));
			}
		}
	}

	private function attemptToPlaceBlockAbove(TreeDecoratorContext $context, Vector3 $pos) : void{
		$abovePos = $pos->up();

		$state = $context->level()->getBlockAt($abovePos->getFloorX(), $abovePos->getFloorY(), $abovePos->getFloorZ());
		$state2 = $context->level()->getBlockAt($abovePos->getFloorX(), $abovePos->getFloorY(), $abovePos->getFloorZ());

		if (
			($state->getId() === BlockIds::AIR || $state instanceof Vine) &&
			$state2->isSolid()
		) {
			$context->setBlock($abovePos, $this->blockStateProvider->getState($context->random(), $abovePos));
		}
	}
}
