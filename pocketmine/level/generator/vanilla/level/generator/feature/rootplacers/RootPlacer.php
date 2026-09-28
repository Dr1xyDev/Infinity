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

namespace pocketmine\level\generator\vanilla\level\generator\feature\rootplacers;

use pocketmine\level\generator\vanilla\block\Block;
use pocketmine\level\generator\vanilla\block\BlockIds;
use pocketmine\level\generator\vanilla\level\ChunkManager;
use pocketmine\level\generator\vanilla\level\generator\feature\configurations\TreeConfiguration;
use pocketmine\level\generator\vanilla\level\generator\feature\setter\Setter;
use pocketmine\level\generator\vanilla\level\generator\feature\stateproviders\BlockStateProvider;
use pocketmine\level\generator\vanilla\level\generator\feature\TreeFeature;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\utils\Random;
use pocketmine\level\generator\vanilla\utils\valueproviders\IntProvider;

abstract class RootPlacer {

	public function __construct(
		protected IntProvider $trunkOffsetY,
		protected BlockStateProvider $rootProvider,
		protected ?AboveRootPlacement $aboveRootPlacement
	){}

	abstract public function type() : RootPlacerType;

	abstract public function placeRoots(ChunkManager $level, Setter $rootSetter, Random $random, Vector3 $origin, Vector3 $trunkOrigin, TreeConfiguration $config) : bool;

	protected function canPlaceRoot(ChunkManager $level, Vector3 $pos) : bool {
		return TreeFeature::validTreePos($level, $pos);
	}

	protected function placeRoot(ChunkManager $level, Setter $rootSetter, Random $random, Vector3 $pos, TreeConfiguration $config) : void {
		if ($this->canPlaceRoot($level, $pos)) {
			$rootSetter->set($pos, $this->getPotentiallyWaterloggedState($level, $pos, $this->rootProvider->getState($random, $pos)));

			if ($this->aboveRootPlacement !== null) {
				$abovePlacement = $this->aboveRootPlacement;
				$above = $pos->up();

				$aboveState = $level->getBlockAt($above->getFloorX(), $above->getFloorY(), $above->getFloorZ());
				if ($random->nextFloat() < $abovePlacement->aboveRootPlacementChance() && $aboveState->getId() === BlockIds::AIR) {
					$rootSetter->set($above, $this->getPotentiallyWaterloggedState($level, $above, $abovePlacement->aboveRootProvider()->getState($random, $above)));
				}
			}
		}
	}

	protected function getPotentiallyWaterloggedState(ChunkManager $level, Vector3 $pos, Block $state) : Block{
		//TODO: waterlogged
		return $state;
	}

	public function getTrunkOrigin(Vector3 $origin, Random $random) : Vector3 {
		return $origin->up($this->trunkOffsetY->sample($random));
	}
}
