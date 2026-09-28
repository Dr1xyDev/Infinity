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

use pocketmine\level\generator\vanilla\level\generator\feature\Feature;
use pocketmine\level\generator\vanilla\level\generator\feature\stateproviders\BlockStateProvider;
use pocketmine\level\generator\vanilla\level\generator\feature\TreeFeature;
use pocketmine\level\generator\vanilla\math\Vector3;
use function abs;
use function count;

class AlterGroundDecorator extends TreeDecorator{
	public function __construct(
		private BlockStateProvider $provider
	) {}

	protected function type() : TreeDecoratorType {
		return TreeDecoratorType::ALTER_GROUND;
	}

	public function place(TreeDecoratorContext $context) : void{
		$blockPositions = TreeFeature::getLowestTrunkOrRootOfTree($context);
		if (count($blockPositions) !== 0) {
			$minY = $blockPositions[0]->getY();
			foreach ($blockPositions as $pos) {
				if ($pos->getY() !== $minY) {
					continue;
				}

				$this->placeCircle($context, $pos->west()->north());
				$this->placeCircle($context, $pos->east(2)->north());
				$this->placeCircle($context, $pos->west()->south(2));
				$this->placeCircle($context, $pos->east(2)->south(2));

				for ($i = 0; $i < 5; $i++) {
					$placement = $context->random()->nextBoundedInt(64);
					$xx = $placement % 8;
					$zz = $placement / 8;

					if ($xx === 0 || $xx === 7 || $zz === 0 || $zz === 7) {
						$this->placeCircle($context, $pos->add(-3 + $xx, 0, -3 + $zz));
					}
				}
			}
		}
	}

	private function placeCircle(TreeDecoratorContext $context, Vector3 $pos) : void {
		for ($xx = -2; $xx <= 2; $xx++) {
			for ($zz = -2; $zz <= 2; $zz++) {
				if (abs($xx) != 2 || abs($zz) != 2) {
					$this->placeBlockAt($context, $pos->add($xx, 0, $zz));
				}
			}
		}
	}

	private function placeBlockAt(TreeDecoratorContext $context, Vector3 $pos) : void {
		for ($dy = 2; $dy >= -3; $dy--) {
			$blockPos = $pos->up($dy);
			if (Feature::isGrassOrDirt($context->level(), $blockPos)) {
				$context->setBlock($blockPos, $this->provider->getState($context->random(), $pos));
				break;
			}

			if (!$context->isAir($blockPos) && $dy < 0) {
				break;
			}
		}
	}
}
