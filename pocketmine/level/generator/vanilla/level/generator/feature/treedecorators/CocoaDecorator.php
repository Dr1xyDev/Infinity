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
use function count;

class CocoaDecorator extends TreeDecorator{

	public function __construct(
		private float $probability
	){}

	protected function type() : TreeDecoratorType {
		return TreeDecoratorType::COCOA;
	}

	public function place(TreeDecoratorContext $context) : void {
		$random = $context->random();
		if (!($random->nextFloat() >= $this->probability)) {
			$logs = $context->logs();
			if (count($logs) !== 0) {
				$treeY = $logs[0]->getY();
				foreach ($logs as $pos) {
					if ($pos->getY() - $treeY > 2) {
						continue;
					}

					foreach (Facing::HORIZONTAL as $direction) {
						if ($random->nextFloat() <= 0.25) {
							$opposite = Facing::opposite($direction);
							$cocoaPos = $pos->add(Facing::OFFSET[$opposite][0], 0, Facing::OFFSET[$opposite][2]);
							if ($context->isAir($cocoaPos)) {
								$context->setBlock(
									$cocoaPos,
									BlockFactory::get(BlockIds::COCOA, Facing::opposite(match($direction){
											Facing::NORTH => 2,
											Facing::SOUTH => 0,
											Facing::WEST => 1,
											Facing::EAST => 3
										}) | ($random->nextBoundedInt(3) << 2))
								);
							}
						}
					}
				}
			}
		}
	}
}
