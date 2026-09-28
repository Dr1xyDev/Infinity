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
use pocketmine\level\generator\vanilla\math\Facing;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\utils\Utils;
use function array_filter;
use function array_values;
use function count;
use function max;
use function min;

class BeehiveDecorator extends TreeDecorator{
	private const int WORLDGEN_FACING = Facing::SOUTH;
	private static ?array $SPAWN_DIRECTIONS = null;

	public function __construct(
		private float $probability
	) {
		if (self::$SPAWN_DIRECTIONS === null) {
			self::$SPAWN_DIRECTIONS = array_values(
				array_filter(Facing::HORIZONTAL, fn($dir) => $dir !== Facing::opposite(self::WORLDGEN_FACING))
			);
		}
	}

	protected function type() : TreeDecoratorType {
		return TreeDecoratorType::BEEHIVE;
	}

	public function place(TreeDecoratorContext $context) : void {
		$leaves = $context->leaves();
		$logs = $context->logs();
		if (count($logs) !== 0) {
			$random = $context->random();
			if (!($random->nextFloat() >= $this->probability)) {
				$hiveY = count($leaves) !== 0
					? max($leaves[0]->getY() - 1, $logs[0]->getY() + 1)
					: min($logs[0]->getY() + 1 + $random->nextBoundedInt(3), $logs[count($logs) - 1]->getY());

				/** @var Vector3[] $hivePlacements */
				$hivePlacements = [];
				foreach ($logs as $pos) {
					if ($pos->getY() === $hiveY) {
						foreach (self::$SPAWN_DIRECTIONS as $direction) {
							$hivePlacements[] = $pos->getSide($direction);
						}
					}
				}

				if (count($hivePlacements) !== 0) {
					$hivePlacements = Utils::shuffledCopy($hivePlacements, $random);

					$hivePos = null;

					foreach ($hivePlacements as $pos) {
						if ($context->isAir($pos) && $context->isAir($pos->getSide(self::WORLDGEN_FACING))) {
							$hivePos = $pos;
							break;
						}
					}

					if ($hivePos != null) {
						$context->setBlock($hivePos, BlockFactory::get(BlockIds::BEE_NEST)->setDamage(match(self::WORLDGEN_FACING){
							Facing::NORTH => 2,
							Facing::SOUTH => 0,
							Facing::WEST => 1,
							Facing::EAST => 3
						}));
						//TODO: BlockEntity
					}
				}
			}
		}
	}
}
