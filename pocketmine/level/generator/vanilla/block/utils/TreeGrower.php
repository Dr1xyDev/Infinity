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

namespace pocketmine\level\generator\vanilla\block\utils;

use pocketmine\level\generator\vanilla\block\Block;
use pocketmine\level\generator\vanilla\block\BlockFactory;
use pocketmine\level\generator\vanilla\block\BlockIds;
use pocketmine\level\generator\vanilla\block\CactusFlower;
use pocketmine\level\generator\vanilla\block\Eyeblossom;
use pocketmine\level\generator\vanilla\block\Flower;
use pocketmine\level\generator\vanilla\level\generator\feature\Feature;
use pocketmine\level\generator\vanilla\level\generator\feature\FeatureFactory;
use pocketmine\level\generator\vanilla\level\generator\feature\FeaturePlaceContext;
use pocketmine\level\generator\vanilla\level\generator\feature\TreeFeatures;
use pocketmine\level\generator\vanilla\level\Level;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\utils\EnumTrait;
use pocketmine\level\generator\vanilla\utils\Random;

/**
 * This doc-block is generated automatically, do not modify it manually.
 * This must be regenerated whenever registry members are added, removed or changed.
 * @see build/generate-registry-annotations.php
 * @generate-registry-docblock
 *
 * @method static TreeGrower OAK()
 * @method static TreeGrower SPRUCE()
 * @method static TreeGrower MANGROVE()
 * @method static TreeGrower AZALEA()
 * @method static TreeGrower BIRCH()
 * @method static TreeGrower JUNGLE()
 * @method static TreeGrower ACACIA()
 * @method static TreeGrower CHERRY()
 * @method static TreeGrower DARK_OAK()
 * @method static TreeGrower PALE_OAK()
 */
class TreeGrower{
	use EnumTrait {
		register as Enum_register;
		__construct as Enum___construct;
	}

	protected static function setup() : void{
		self::registerAll(
			new TreeGrower("oak", 0.1, null, null, TreeFeatures::OAK, TreeFeatures::FANCY_OAK, TreeFeatures::OAK_BEES_005, TreeFeatures::FANCY_OAK_BEES_005),
			new TreeGrower("spruce", 0.5, TreeFeatures::MEGA_SPRUCE, TreeFeatures::MEGA_PINE, TreeFeatures::SPRUCE, null, null, null),
			new TreeGrower("mangrove", 0.85, null, null, TreeFeatures::MANGROVE, TreeFeatures::TALL_MANGROVE, null, null),
			new TreeGrower("azalea", 0.0, null, null, TreeFeatures::AZALEA_TREE, null, null, null),
			new TreeGrower("birch", 0.0, null, null, TreeFeatures::BIRCH, null, TreeFeatures::BIRCH_BEES_005, null),
			new TreeGrower("jungle", 0.0, TreeFeatures::MEGA_JUNGLE_TREE, null, TreeFeatures::JUNGLE_TREE_NO_VINE, null, null, null),
			new TreeGrower("acacia", 0.0, null, null, TreeFeatures::ACACIA, null, null, null),
			new TreeGrower("cherry", 0.0, null, null, TreeFeatures::CHERRY, null, TreeFeatures::CHERRY_BEES_005, null),
			new TreeGrower("dark_oak", 0.0, TreeFeatures::DARK_OAK, null, null, null, null, null),
			new TreeGrower("pale_oak", 0.0, TreeFeatures::PALE_OAK_BONEMEAL, null, null, null, null, null),
		);
	}

	protected static function register(TreeGrower $member) : void{
		self::Enum_register($member);
	}

	public function __construct(
		string $enumName,
		private float $secondaryChance,
		private ?string $megaTree,
		private ?string $secondaryMegaTree,
		private ?string $tree,
		private ?string $secondaryTree,
		private ?string $flowers,
		private ?string $secondaryFlowers
	){
		$this->Enum___construct($enumName);
	}

	private function getFeatureName(Random $random, bool $hasFlowers) : ?string{
		if ($random->nextFloat() < $this->secondaryChance) {
			if ($hasFlowers && $this->secondaryFlowers !== null) {
				return $this->secondaryFlowers;
			}

			if ($this->secondaryTree) {
				return $this->secondaryTree;
			}
		}

		return $hasFlowers && $this->flowers !== null ? $this->flowers : $this->tree;
	}

	private function getMegaFeatureName(Random $random) : ?string{
		return $this->secondaryMegaTree !== null && $random->nextFloat() < $this->secondaryChance ? $this->secondaryMegaTree : $this->megaTree;
	}

	public function growTree(Level $level, Vector3 $pos, Block $block, Random $random) : bool{
		$air = BlockFactory::get(BlockIds::AIR);

		$megaFeatureKey = $this->getMegaFeatureName($random);
		if ($megaFeatureKey !== null) {
		   $featureHolder = $this->getFeature($megaFeatureKey);
			if ($featureHolder !== null) {
				for ($dx = 0; $dx >= -1; $dx--) {
					for ($dz = 0; $dz >= -1; $dz--) {
						if ($this->isTwoByTwoSapling($block, $level, $pos, $dx, $dz)) {
							$level->setBlock($pos->add($dx, 0, $dz), $air);
							$level->setBlock($pos->add($dx + 1, 0, $dz), $air);
							$level->setBlock($pos->add($dx, 0, $dz + 1), $air);
							$level->setBlock($pos->add($dx + 1, 0, $dz + 1), $air);
							if ($featureHolder->place(new FeaturePlaceContext($level, $random, $pos->add($dx, 0, $dz)))) {
								return true;
							}

							$level->setBlock($pos->add($dx, 0, $dz), $block);
							$level->setBlock($pos->add($dx + 1, 0, $dz), $block);
							$level->setBlock($pos->add($dx, 0, $dz + 1), $block);
							$level->setBlock($pos->add($dx + 1, 0, $dz + 1), $block);
							return false;
						}
					}
				}
			}
		}

	   $featureKey = $this->getFeatureName($random, $this->hasFlowers($level, $pos));
		if ($featureKey === null) {
			return false;
		} else {
			$featureHolder = $this->getFeature($featureKey);
			if ($featureHolder === null) {
				return false;
			} else {
				$level->setBlock($pos, $air);
				if ($featureHolder->place(new FeaturePlaceContext($level, $random, $pos))) {
					return true;
				} else {
					$level->setBlock($pos, $block);
					return false;
				}
			}
		}
	}

	private function isTwoByTwoSapling(Block $block, Level $level, Vector3 $pos, int $ox, int $oz) : bool{
		return $level->getBlockAt($pos->getFloorX() + $ox, $pos->getFloorY(), $pos->getFloorZ() + $oz)->isSameType($block) &&
			$level->getBlockAt($pos->getFloorX() + $ox + 1, $pos->getFloorY(), $pos->getFloorZ() + $oz)->isSameType($block) &&
			$level->getBlockAt($pos->getFloorX() + $ox, $pos->getFloorY(), $pos->getFloorZ() + $oz + 1)->isSameType($block) &&
			$level->getBlockAt($pos->getFloorX() + $ox + 1, $pos->getFloorY(), $pos->getFloorZ() + $oz + 1)->isSameType($block);
	}

	private function hasFlowers(Level $level, Vector3 $pos) : bool{
		$min = $pos->down()->north(2)->west(2);
		$max = $pos->up()->south(2)->east(2);

		for ($x = $min->getX(); $x <= $max->getX(); $x++) {
			for ($y = $min->getY(); $y <= $max->getY(); $y++) {
				for ($z = $min->getZ(); $z <= $max->getZ(); $z++) {
					$block = $level->getBlockAt($x, $y, $z);
					if (
						$block instanceof Flower ||
						$block instanceof CactusFlower ||
						$block instanceof Eyeblossom
					) {
						return true;
					}
				}
			}
		}

		return false;
	}

	private function getFeature(string $name) : ?Feature {
		return FeatureFactory::getInstance()->get($name);
	}
}
