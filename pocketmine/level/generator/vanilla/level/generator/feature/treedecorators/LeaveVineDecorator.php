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
use pocketmine\level\generator\vanilla\math\Vector3;

class LeaveVineDecorator extends TreeDecorator{

	public function __construct(
		private float $probability
	){}

	protected function type() : TreeDecoratorType {
		return TreeDecoratorType::LEAVE_VINE;
	}

	public function place(TreeDecoratorContext $context) : void {
		$random = $context->random();

		foreach ($context->leaves() as $pos) {
			if ($random->nextFloat() < $this->probability) {
				$west = $pos->west();
				if ($context->isAir($west)) {
					$this->addHangingVine($west, Vine::FLAG_EAST, $context);
				}
			}

			if ($random->nextFloat() < $this->probability) {
				$east = $pos->east();
				if ($context->isAir($east)) {
					$this->addHangingVine($east, Vine::FLAG_WEST, $context);
				}
			}

			if ($random->nextFloat() < $this->probability) {
				$north = $pos->north();
				if ($context->isAir($north)) {
					$this->addHangingVine($north, Vine::FLAG_SOUTH, $context);
				}
			}

			if ($random->nextFloat() < $this->probability) {
				$south = $pos->south();
				if ($context->isAir($south)) {
					$this->addHangingVine($south, Vine::FLAG_NORTH, $context);
				}
			}
		}
	}

	private function addHangingVine(Vector3 $pos, int $direction, TreeDecoratorContext $context) : void {
		$context->placeVine($pos, $direction);
		$maxDir = 4;

		for ($var4 = $pos->down(); $context->isAir($var4) && $maxDir > 0; $maxDir--) {
			$context->placeVine($var4, $direction);
			$var4 = $var4->down();
		}
	}
}
