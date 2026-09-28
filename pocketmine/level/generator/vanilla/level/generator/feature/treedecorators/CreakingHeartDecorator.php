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

use pocketmine\level\generator\vanilla\block\BlockFactory;
use pocketmine\level\generator\vanilla\block\BlockIds;
use pocketmine\level\generator\vanilla\block\Log;
use pocketmine\level\generator\vanilla\math\Facing;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\utils\Utils;
use function count;

class CreakingHeartDecorator extends TreeDecorator{
	public function __construct(
		private float $probability
	) {}

	protected function type() : TreeDecoratorType {
		return TreeDecoratorType::CREAKING_HEART;
	}

	public function place(TreeDecoratorContext $context) : void {
		$random = $context->random();
		$level = $context->level();
		$logs = $context->logs();
		if (count($logs) !== 0) {
			if (!($random->nextFloat() >= $this->probability)) {
				/** @var Vector3[] $heartPlacements */
				$heartPlacements = Utils::shuffledCopy($logs, $random);

				foreach ($heartPlacements as $pos) {
					$surrounded = true;

					foreach (Facing::ALL as $direction) {
						$adjacentPos = $pos->getSide($direction);
						$state = $level->getBlockAt($adjacentPos->getFloorX(), $adjacentPos->getFloorY(), $adjacentPos->getFloorZ());
						if (!($state instanceof Log)) {
							$surrounded = false;
							break;
						}
					}

					if ($surrounded) {
						$context->setBlock($pos, BlockFactory::get(BlockIds::CREAKING_HEART, 12)); //dormant and natural
						return;
					}
				}
			}
		}
	}
}
