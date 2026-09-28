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

namespace pocketmine\level\generator\vanilla\level\generator\feature;

use pocketmine\level\generator\vanilla\block\Block;
use pocketmine\level\generator\vanilla\block\BlockIds;
use pocketmine\level\generator\vanilla\block\DoublePlant as BlockDoublePlant;
use pocketmine\level\generator\vanilla\block\Flower as BlockFlower;
use pocketmine\level\generator\vanilla\block\Leaves;
use pocketmine\level\generator\vanilla\block\TallGrass as BlockTallGrass;
use pocketmine\level\generator\vanilla\block\Water;
use pocketmine\level\generator\vanilla\level\ChunkManager;
use pocketmine\level\generator\vanilla\level\generator\feature\configurations\HugeMushroomFeatureConfiguration;
use pocketmine\level\generator\vanilla\level\Level;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\utils\Random;

abstract class AbstractHugeMushroomFeature extends Feature {

	public function __construct(
		public HugeMushroomFeatureConfiguration $config
	){}

	protected function placeTrunk(ChunkManager $level, Random $random, Vector3 $origin, HugeMushroomFeatureConfiguration $config, int $treeHeight) : void{
		for ($dy = 0; $dy < $treeHeight; $dy++) {
			$blockPos = $origin->up($dy);
			$this->placeMushroomBlock($level, $blockPos, $config->stemProvider->getState($random, $origin));
		}
	}

	protected function placeMushroomBlock(ChunkManager $level, Vector3 $blockPos, Block $newState) : void{
		$currentState = $level->getBlockAt($blockPos->getFloorX(), $blockPos->getFloorY(), $blockPos->getFloorZ());
		$currentStateLegacyId = $currentState->getId();

		if (
			$currentStateLegacyId === BlockIds::AIR ||
			$currentStateLegacyId === BlockIds::DEAD_BUSH ||
			$currentStateLegacyId === BlockIds::VINE ||
			$currentStateLegacyId === BlockIds::GLOW_LICHEN ||
			$currentStateLegacyId === BlockIds::HANGING_ROOTS ||
			$currentStateLegacyId === BlockIds::PITCHER_PLANT ||
			$currentStateLegacyId === BlockIds::SEAGRASS ||
			$currentStateLegacyId === BlockIds::BUSH ||
			$currentStateLegacyId === BlockIds::FIREFLY_BUSH ||
			$currentStateLegacyId === BlockIds::WARPED_ROOTS ||
			$currentStateLegacyId === BlockIds::NETHER_SPROUTS ||
			$currentStateLegacyId === BlockIds::CRIMSON_ROOTS ||
			$currentStateLegacyId === BlockIds::LEAF_LITTER ||
			$currentStateLegacyId === BlockIds::SHORT_DRY_GRASS ||
			$currentStateLegacyId === BlockIds::TALL_DRY_GRASS ||
			$currentStateLegacyId === BlockIds::BROWN_MUSHROOM ||
			$currentStateLegacyId === BlockIds::RED_MUSHROOM ||
			$currentStateLegacyId === BlockIds::BROWN_MUSHROOM_BLOCK ||
			$currentStateLegacyId === BlockIds::RED_MUSHROOM_BLOCK ||
			$currentState instanceof BlockDoublePlant ||
			$currentState instanceof BlockTallGrass ||
			$currentState instanceof Leaves ||
			$currentState instanceof BlockFlower ||
			$currentState instanceof Water
		) {
			$this->setBlock($level, $blockPos, $newState);
		}
	}

	protected function getTreeHeight(Random $random) : int{
		$treeHeight = $random->nextBoundedInt(3) + 4;
		if ($random->nextBoundedInt(12) == 0) {
			$treeHeight *= 2;
		}

		return $treeHeight;
	}

	protected function isValidPosition(
		ChunkManager                     $level,
		Vector3                          $origin,
		int                              $treeHeight,
		HugeMushroomFeatureConfiguration $config
	) : bool{
		$y = $origin->getY();
		if ($y >= Level::Y_MIN + 1 && $y + $treeHeight + 1 <= $level->getWorldHeight()) {
			$belowPos = $origin->down();
			$belowState = $level->getBlockAt($belowPos->getFloorX(), $belowPos->getFloorY(), $belowPos->getFloorZ());
			$belowStateLegacyId = $belowState->getId();
			if (!$this->isDirt($belowState) && $belowStateLegacyId !== BlockIds::MYCELIUM && $belowStateLegacyId !== BlockIds::PODZOL && $belowStateLegacyId !== BlockIds::CRIMSON_NYLIUM && $belowStateLegacyId !== BlockIds::WARPED_NYLIUM) {
				return false;
			} else {
				for ($dy = 0; $dy <= $treeHeight; $dy++) {
					$radius = $this->getTreeRadiusForHeight(-1, -1, $config->foliageRadius, $dy);
					for ($dx = -$radius; $dx <= $radius; $dx++) {
						for ($dz = -$radius; $dz <= $radius; $dz++) {
							$blockPos = $origin->add($dx, $dy, $dz);
							$state = $level->getBlockAt($blockPos->getFloorX(), $blockPos->getFloorY(), $blockPos->getFloorZ());
							if ($state->getId() !== BlockIds::AIR && !($state instanceof Leaves)) {
								return false;
							}
						}
					}
				}

				return true;
			}
		} else {
			return false;
		}
	}

	public function place(FeaturePlaceContext $context) : bool{
		$level = $context->level();
		$origin = $context->origin();
		$random = $context->random();
		$config = $this->config;

		$treeHeight = $this->getTreeHeight($random);
		if (!$this->isValidPosition($level, $origin, $treeHeight, $config)) {
			return false;
		} else {
			$this->makeCap($level, $random, $origin, $treeHeight, $config);
			$this->placeTrunk($level, $random, $origin, $config, $treeHeight);
			return true;
		}
	}

	abstract protected function getTreeRadiusForHeight(int $trunkHeight, int $treeHeight, int $leafRadius, int $yo) : int;

	abstract protected function makeCap(
		ChunkManager                     $level,
		Random                           $random,
		Vector3                          $origin,
		int                              $treeHeight,
		HugeMushroomFeatureConfiguration $config
	) : void;

}
