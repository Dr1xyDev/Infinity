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
use pocketmine\level\generator\vanilla\level\generator\feature\configurations\TreeConfiguration;
use pocketmine\level\generator\vanilla\level\generator\feature\setter\CollectorSetter;
use pocketmine\level\generator\vanilla\level\generator\feature\setter\Setter;
use pocketmine\level\generator\vanilla\level\generator\feature\treedecorators\TreeDecoratorContext;
use pocketmine\level\generator\vanilla\level\Level;
use pocketmine\level\generator\vanilla\math\AxisAlignedBB;
use pocketmine\level\generator\vanilla\math\Facing;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\phys\shapes\BitSetDiscreteVoxelShape;
use pocketmine\level\generator\vanilla\phys\shapes\DiscreteVoxelShape;
use pocketmine\level\generator\vanilla\utils\Random;
use function array_merge;
use function array_shift;
use function count;
use function max;
use function min;

class TreeFeature extends Feature{

	public static function isVine(ChunkManager $level, Vector3 $pos) : bool {
		$state = $level->getBlockAt($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ());
		return $state->getId() === BlockIds::VINE;
	}

	public static function isAirOrLeaves(ChunkManager $level, Vector3 $pos) : bool {
		$state = $level->getBlockAt($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ());
		return $state->getId() === BlockIds::AIR || $state instanceof Leaves;
	}

	public static function setBlockKnownShape(ChunkManager $level, Vector3 $pos, Block $block) : void {
		$level->setBlockAt($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ(), $block);
	}

	public static function validTreePos(ChunkManager $level, Vector3 $pos) : bool {
		$state = $level->getBlockAt($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ());
		$stateLegacyId = $state->getId();

		return
			$stateLegacyId === BlockIds::AIR ||
			$stateLegacyId === BlockIds::DEAD_BUSH ||
			$stateLegacyId === BlockIds::VINE ||
			$stateLegacyId === BlockIds::GLOW_LICHEN ||
			$stateLegacyId === BlockIds::HANGING_ROOTS ||
			$stateLegacyId === BlockIds::PITCHER_PLANT ||
			$stateLegacyId === BlockIds::SEAGRASS ||
			$stateLegacyId === BlockIds::BUSH ||
			$stateLegacyId === BlockIds::FIREFLY_BUSH ||
			$stateLegacyId === BlockIds::WARPED_ROOTS ||
			$stateLegacyId === BlockIds::NETHER_SPROUTS ||
			$stateLegacyId === BlockIds::CRIMSON_ROOTS ||
			$stateLegacyId === BlockIds::LEAF_LITTER ||
			$stateLegacyId === BlockIds::SHORT_DRY_GRASS ||
			$stateLegacyId === BlockIds::TALL_DRY_GRASS ||
			$state instanceof BlockDoublePlant ||
			$state instanceof BlockTallGrass ||
			$state instanceof Leaves ||
			$state instanceof BlockFlower ||
			$state instanceof Water;
	}

	public static function encapsulatingPositions(iterable $positions) : ?AxisAlignedBB {
		$first = true;
		$minX = $minY = $minZ = 0;
		$maxX = $maxY = $maxZ = 0;

		foreach ($positions as $pos) {
			/** @var Vector3 $pos */
			if ($first) {
				$minX = $maxX = $pos->x;
				$minY = $maxY = $pos->y;
				$minZ = $maxZ = $pos->z;
				$first = false;
			} else {
				$minX = min($minX, $pos->x);
				$minY = min($minY, $pos->y);
				$minZ = min($minZ, $pos->z);
				$maxX = max($maxX, $pos->x);
				$maxY = max($maxY, $pos->y);
				$maxZ = max($maxZ, $pos->z);
			}
		}

		return $first ? null : new AxisAlignedBB($minX, $minY, $minZ, $maxX + 1, $maxY + 1, $maxZ + 1);
	}

	public function __construct(
		public TreeConfiguration $config
	){}

	private function doPlace(ChunkManager $level, Random $random, Vector3 $origin, Setter $rootSetter, Setter $trunkSetter, CollectorSetter $foliageSetter, TreeConfiguration $config) : bool {
		$treeHeight = $config->trunkPlacer->getTreeHeight($random);
		$foliageHeight = $config->foliagePlacer->foliageHeight($random, $treeHeight, $config);
		$trunkHeight = $treeHeight - $foliageHeight;
		$leafRadius = $config->foliagePlacer->foliageRadius($random, $trunkHeight);
		$trunkOrigin = $config->rootPlacer ? $config->rootPlacer->getTrunkOrigin($origin, $random) : $origin;
		$minY = min($origin->getY(), $trunkOrigin->getY());
		$maxY = max($origin->getY(), $trunkOrigin->getY()) + $treeHeight + 1;
		if ($minY >= Level::Y_MIN + 1 && $maxY + 1 <= $level->getWorldHeight()) {
			$minClippedHeight = $config->minimumSize->minClippedHeight();
			$clippedTreeHeight = $this->getMaxFreeTreeHeight($level, $treeHeight, $trunkOrigin, $config);
			if ($clippedTreeHeight >= $treeHeight || $minClippedHeight !== null && $clippedTreeHeight >= $minClippedHeight) {
				if ($config->rootPlacer !== null && !$config->rootPlacer->placeRoots($level, $rootSetter, $random, $origin, $trunkOrigin, $config)) {
					return false;
				} else {
					$foliageAttachments = $config->trunkPlacer->placeTrunk($level, $trunkSetter, $random, $clippedTreeHeight, $trunkOrigin, $config);
					foreach ($foliageAttachments as $foliageAttachment) {
						$config->foliagePlacer->createFoliage($level, $foliageSetter, $random, $config, $clippedTreeHeight, $foliageAttachment, $foliageHeight, $leafRadius, $config->foliagePlacer->offset($random));
					}
					return true;
				}
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	private function getMaxFreeTreeHeight(ChunkManager $level, int $maxTreeHeight, Vector3 $treePos, TreeConfiguration $config) : int {
		for ($y = 0; $y <= $maxTreeHeight + 1; $y++) {
			$r = $config->minimumSize->getSizeAtHeight($maxTreeHeight, $y);

			for ($x = -$r; $x <= $r; $x++) {
				for ($z = -$r; $z <= $r; $z++) {
					$blockPos = $treePos->add($x, $y, $z);
					if (!$config->trunkPlacer->isFree($level, $blockPos) || !$config->ignoreVines && self::isVine($level, $blockPos)) {
						return $y - 2;
					}
				}
			}
		}

		return $maxTreeHeight;
	}

	protected function setBlock(ChunkManager $level, Vector3 $pos, Block $blockState) : void {
		self::setBlockKnownShape($level, $pos, $blockState);
	}

	public function place(FeaturePlaceContext $context) : bool {
		$level = $context->level();
		$random = $context->random();
		$origin = $context->origin()->floor();
		$config = $this->config;

		$defaultSetter = new class($level) implements CollectorSetter {
			/** @var Vector3[] */
			private array $positions = [];

			public function __construct(private ChunkManager $level){}

			public function set(Vector3 $pos, Block $block) : void{
				$this->positions[Level::blockHash($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ())] = $pos;
				$this->level->setBlockAt($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ(), $block);
			}

			public function isSet(Vector3 $pos) : bool{
				return isset($this->positions[Level::blockHash($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ())]);
			}

			public function getPositions() : array{
				return $this->positions;
			}
		};

		$rootSetter = clone $defaultSetter;
		$trunkSetter = clone $defaultSetter;
		$foliageSetter = clone $defaultSetter;
		$decorationSetter = clone $defaultSetter;
		$result = $this->doPlace($level, $random, $origin, $rootSetter, $trunkSetter, $foliageSetter, $config);
		if ($result && (count($trunkSetter->getPositions()) !== 0 || count($foliageSetter->getPositions()) !== 0)) {
			if (count($config->decorators) !== 0) {
				$decoratorContext = new TreeDecoratorContext($level, $decorationSetter, $random, $trunkSetter->getPositions(), $foliageSetter->getPositions(), $rootSetter->getPositions());
				foreach ($config->decorators as $decorator) {
					$decorator->place($decoratorContext);
				}
			}

			/*
			$allPositions = [];
			foreach ($rootSetter->getPositions() as $p) $allPositions[] = $p;
			foreach ($trunkSetter->getPositions() as $p) $allPositions[] = $p;
			foreach ($foliageSetter->getPositions() as $p) $allPositions[] = $p;
			foreach ($decorationSetter->getPositions() as $p) $allPositions[] = $p;

			$bounds = TreeFeature::encapsulatingPositions($allPositions);

			if ($bounds !== null) {
				$shape = $this->updateLeaves($level, $bounds, $trunkSetter->getPositions(), $decorationSetter->getPositions(), $rootSetter->getPositions());

				$this->scheduleLeavesUpdateAtEdge($level, $bounds);

				return true;
			}

			return false;*/

			return true;
		} else {
			return false;
		}
	}

	/**
	 * This feature of Minecraft: java Client, if Mojang makes it available in the Bedrock edition, can be implemented
	 *
	 * @param Vector3[] $logs
	 * @param Vector3[] $decorationSet
	 * @param Vector3[] $rootPositions
	 */
	private static function updateLeaves(ChunkManager $level, AxisAlignedBB $bounds, array $logs, array $decorationSet, array $rootPositions) : DiscreteVoxelShape {
		$shape = new BitSetDiscreteVoxelShape($bounds->getXLength() + 1, $bounds->getYLength() + 1, $bounds->getZLength() + 1);
		$maxDistance = 7;
		$toCheck = [];

		for ($i = 0; $i < 7; $i++) {
			$toCheck[] = [];
		}

		foreach (array_merge($decorationSet, $rootPositions) as $hash => $pos) {
			if ($bounds->isVectorInside($pos)) {
				$shape->fill((int) ($pos->getX() - $bounds->minX), (int) ($pos->getY() - $bounds->minY), (int) ($pos->getZ() - $bounds->minZ));
			}
		}

		$smallestDistance = 0;
		foreach ($logs as $pos) {
			$toCheck[0][] = $pos;
		}

		while (true) {
			while ($smallestDistance >= $maxDistance || count($toCheck[$smallestDistance]) !== 0) {
				if ($smallestDistance >= $maxDistance) {
					return $shape;
				}

				/** @var Vector3 $posx */
				$posx = array_shift($toCheck[$smallestDistance]);

				if ($bounds->isVectorInside($posx)) {
					if ($smallestDistance != 0) {
						$state = $level->getBlockAt($posx->getFloorX(), $posx->getFloorY(), $posx->getFloorZ());
						self::setBlockKnownShape($level, $posx, $state); //distance xd mojang wtf
					}

					$shape->fill((int) ($posx->getX() - $bounds->minX), (int) ($posx->getY() - $bounds->minY), (int) ($posx->getZ() - $bounds->minZ));

					foreach (Facing::ALL as $direction) {
						$neighborPos = $posx->getSide($direction);
						if ($bounds->isVectorInside($neighborPos)) {
							$xInShape = $neighborPos->getX() - $bounds->minX;
							$yInShape = $neighborPos->getY() - $bounds->minY;
							$zinShape = $neighborPos->getZ() - $bounds->minZ;
							if (!$shape->isFull($xInShape, $yInShape, $zinShape)) {
								$currentState = $level->getBlockAt($neighborPos->getFloorX(), $neighborPos->getFloorY(), $neighborPos->getFloorZ());
								$distance = 0; //TODO:
								if ($distance !== null) {
									$newDistance = min($distance, $smallestDistance + 1);
									if ($newDistance < $maxDistance) {
										$toCheck[$newDistance][] = $neighborPos;
										$smallestDistance = min($smallestDistance, $newDistance);
									}
								}
							}
						}
					}
				}
			}

			$smallestDistance++;
		}
	}

	/**
	 * @return Vector3[]
	 */
	public static function getLowestTrunkOrRootOfTree(TreeDecoratorContext $context) : array {
		$blockPositions = [];
		$roots = $context->roots();
		$logs = $context->logs();
		if (count($roots) === 0) {
			$blockPositions = array_merge($blockPositions, $logs);
		} elseif (count($logs) !== 0 && $roots[0]->getY() === $logs[0]->getY()) {
			$blockPositions = array_merge($blockPositions, $logs);
			$blockPositions = array_merge($blockPositions, $roots);
		} else {
			$blockPositions = array_merge($blockPositions, $roots);
		}

		return $blockPositions;
	}
}
