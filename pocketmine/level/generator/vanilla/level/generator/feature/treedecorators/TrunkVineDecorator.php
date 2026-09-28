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

use pocketmine\level\generator\vanilla\block\Vine;

class TrunkVineDecorator extends TreeDecorator{

	protected function type() : TreeDecoratorType {
		return TreeDecoratorType::TRUNK_VINE;
	}

	public function place(TreeDecoratorContext $context) : void {
		$random = $context->random();

		foreach ($context->logs() as $pos) {
			if ($random->nextBoundedInt(3) > 0) {
				$west = $pos->west();
				if ($context->isAir($west)) {
					$context->placeVine($west, Vine::FLAG_EAST);
				}
			}

			if ($random->nextBoundedInt(3) > 0) {
				$east = $pos->east();
				if ($context->isAir($east)) {
					$context->placeVine($east, Vine::FLAG_WEST);
				}
			}

			if ($random->nextBoundedInt(3) > 0) {
				$north = $pos->north();
				if ($context->isAir($north)) {
					$context->placeVine($north, Vine::FLAG_SOUTH);
				}
			}

			if ($random->nextBoundedInt(3) > 0) {
				$south = $pos->south();
				if ($context->isAir($south)) {
					$context->placeVine($south, Vine::FLAG_NORTH);
				}
			}
		}
	}
}
