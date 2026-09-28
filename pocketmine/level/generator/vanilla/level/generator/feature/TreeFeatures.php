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
use pocketmine\level\generator\vanilla\block\BlockFactory;
use pocketmine\level\generator\vanilla\block\BlockIds;
use pocketmine\level\generator\vanilla\block\Leaves;
use pocketmine\level\generator\vanilla\block\Leaves2;
use pocketmine\level\generator\vanilla\block\Log;
use pocketmine\level\generator\vanilla\block\Log2;
use pocketmine\level\generator\vanilla\level\generator\feature\configurations\FallenTreeConfigurationBuilder;
use pocketmine\level\generator\vanilla\level\generator\feature\configurations\HugeMushroomFeatureConfiguration;
use pocketmine\level\generator\vanilla\level\generator\feature\configurations\TreeConfigurationBuilder;
use pocketmine\level\generator\vanilla\level\generator\feature\featuresize\ThreeLayersFeatureSize;
use pocketmine\level\generator\vanilla\level\generator\feature\featuresize\TwoLayersFeatureSize;
use pocketmine\level\generator\vanilla\level\generator\feature\foliageplacers\AcaciaFoliagePlacer;
use pocketmine\level\generator\vanilla\level\generator\feature\foliageplacers\BlobFoliagePlacer;
use pocketmine\level\generator\vanilla\level\generator\feature\foliageplacers\BushFoliagePlacer;
use pocketmine\level\generator\vanilla\level\generator\feature\foliageplacers\CherryFoliagePlacer;
use pocketmine\level\generator\vanilla\level\generator\feature\foliageplacers\DarkOakFoliagePlacer;
use pocketmine\level\generator\vanilla\level\generator\feature\foliageplacers\FancyFoliagePlacer;
use pocketmine\level\generator\vanilla\level\generator\feature\foliageplacers\MegaJungleFoliagePlacer;
use pocketmine\level\generator\vanilla\level\generator\feature\foliageplacers\MegaPineFoliagePlacer;
use pocketmine\level\generator\vanilla\level\generator\feature\foliageplacers\PineFoliagePlacer;
use pocketmine\level\generator\vanilla\level\generator\feature\foliageplacers\RandomSpreadFoliagePlacer;
use pocketmine\level\generator\vanilla\level\generator\feature\foliageplacers\SpruceFoliagePlacer;
use pocketmine\level\generator\vanilla\level\generator\feature\rootplacers\AboveRootPlacement;
use pocketmine\level\generator\vanilla\level\generator\feature\rootplacers\MangroveRootPlacement;
use pocketmine\level\generator\vanilla\level\generator\feature\rootplacers\MangroveRootPlacer;
use pocketmine\level\generator\vanilla\level\generator\feature\stateproviders\BlockStateProvider;
use pocketmine\level\generator\vanilla\level\generator\feature\stateproviders\RandomizedIntStateProvider;
use pocketmine\level\generator\vanilla\level\generator\feature\stateproviders\RandomizedIntStateSetter;
use pocketmine\level\generator\vanilla\level\generator\feature\stateproviders\WeightedStateProvider;
use pocketmine\level\generator\vanilla\level\generator\feature\treedecorators\AlterGroundDecorator;
use pocketmine\level\generator\vanilla\level\generator\feature\treedecorators\AttachedToLeavesDecorator;
use pocketmine\level\generator\vanilla\level\generator\feature\treedecorators\AttachedToLogsDecorator;
use pocketmine\level\generator\vanilla\level\generator\feature\treedecorators\BeehiveDecorator;
use pocketmine\level\generator\vanilla\level\generator\feature\treedecorators\CocoaDecorator;
use pocketmine\level\generator\vanilla\level\generator\feature\treedecorators\CreakingHeartDecorator;
use pocketmine\level\generator\vanilla\level\generator\feature\treedecorators\LeaveVineDecorator;
use pocketmine\level\generator\vanilla\level\generator\feature\treedecorators\PaleMossDecorator;
use pocketmine\level\generator\vanilla\level\generator\feature\treedecorators\PlaceOnGroundDecorator;
use pocketmine\level\generator\vanilla\level\generator\feature\treedecorators\TrunkVineDecorator;
use pocketmine\level\generator\vanilla\level\generator\feature\trunkplacers\BendingTrunkPlacer;
use pocketmine\level\generator\vanilla\level\generator\feature\trunkplacers\CherryTrunkPlacer;
use pocketmine\level\generator\vanilla\level\generator\feature\trunkplacers\DarkOakTrunkPlacer;
use pocketmine\level\generator\vanilla\level\generator\feature\trunkplacers\FancyTrunkPlacer;
use pocketmine\level\generator\vanilla\level\generator\feature\trunkplacers\ForkingTrunkPlacer;
use pocketmine\level\generator\vanilla\level\generator\feature\trunkplacers\GiantTrunkPlacer;
use pocketmine\level\generator\vanilla\level\generator\feature\trunkplacers\MegaJungleTrunkPlacer;
use pocketmine\level\generator\vanilla\level\generator\feature\trunkplacers\StraightTrunkPlacer;
use pocketmine\level\generator\vanilla\level\generator\feature\trunkplacers\UpwardsBranchingTrunkPlacer;
use pocketmine\level\generator\vanilla\math\Facing;
use pocketmine\level\generator\vanilla\utils\valueproviders\ConstantInt;
use pocketmine\level\generator\vanilla\utils\valueproviders\UniformInt;
use pocketmine\level\generator\vanilla\utils\valueproviders\WeightedListInt;

final class TreeFeatures {

	public const CRIMSON_FUNGUS = "crimson_fungus";
	public const CRIMSON_FUNGUS_PLANTED = "crimson_fungus_planted";
	public const WARPED_FUNGUS = "warped_fungus";
	public const WARPED_FUNGUS_PLANTED = "warped_fungus_planted";
	public const HUGE_BROWN_MUSHROOM = "huge_brown_mushroom";
	public const HUGE_RED_MUSHROOM = "huge_red_mushroom";
	public const OAK = "oak";
	public const DARK_OAK = "dark_oak";
	public const PALE_OAK = "pale_oak";
	public const PALE_OAK_BONEMEAL = "pale_oak_bonemeal";
	public const PALE_OAK_CREAKING = "pale_oak_creaking";
	public const BIRCH = "birch";
	public const ACACIA = "acacia";
	public const SPRUCE = "spruce";
	public const PINE = "pine";
	public const JUNGLE_TREE = "jungle_tree";
	public const JUNGLE_TREE_NO_VINE = "jungle_tree_no_vine";
	public const MEGA_JUNGLE_TREE = "mega_jungle_tree";
	public const JUNGLE_BUSH = "jungle_bush";
	public const FANCY_OAK = "fancy_oak";
	public const MEGA_SPRUCE = "mega_spruce";
	public const MEGA_PINE = "mega_pine";
	public const SUPER_BIRCH_BEES_0002 = "super_birch_bees_0002";
	public const SUPER_BIRCH_BEES = "super_birch_bees";
	public const SWAMP_OAK = "swamp_oak";
	public const AZALEA_TREE = "azalea_tree";
	public const MANGROVE = "mangrove";
	public const TALL_MANGROVE = "tall_mangrove";
	public const CHERRY = "cherry";
	public const CHERRY_BEES_005 = "cherry_bees_005";
	public const OAK_BEES_0002 = "oak_bees_0002";
	public const OAK_BEES_002 = "oak_bees_002";
	public const OAK_BEES_005 = "oak_bees_005";
	public const OAK_BEES_0002_LEAF_LITTER = "oak_bees_0002_leaf_litter";
	public const OAK_LEAF_LITTER = "oak_leaf_litter";
	public const BIRCH_BEES_0002 = "birch_bees_0002";
	public const BIRCH_BEES_002 = "birch_bees_002";
	public const BIRCH_BEES_005 = "birch_bees_005";
	public const BIRCH_BEES_0002_LEAF_LITTER = "birch_bees_0002_leaf_litter";
	public const BIRCH_LEAF_LITTER = "birch_leaf_litter";
	public const FANCY_OAK_BEES_0002 = "fancy_oak_bees_0002";
	public const FANCY_OAK_BEES_002 = "fancy_oak_bees_002";
	public const FANCY_OAK_BEES_005 = "fancy_oak_bees_005";
	public const FANCY_OAK_BEES = "fancy_oak_bees";
	public const FANCY_OAK_BEES_0002_LEAF_LITTER = "fancy_oak_bees_0002_leaf_litter";
	public const FANCY_OAK_LEAF_LITTER = "fancy_oak_leaf_litter";
	public const DARK_OAK_LEAF_LITTER = "dark_oak_leaf_litter";
	public const FALLEN_OAK_TREE = "fallen_oak_tree";
	public const FALLEN_JUNGLE_TREE = "fallen_jungle_tree";
	public const FALLEN_SPRUCE_TREE = "fallen_spruce_tree";
	public const FALLEN_BIRCH_TREE = "fallen_birch_tree";
	public const FALLEN_SUPER_BIRCH_TREE = "fallen_super_birch_tree";

	private function __construct() {
		//NOOP
	}

	public static function bootstrap(FeatureFactory $featureFactory) : void{
		//TODO: CRIMSON_FUNGUS
		//TODO: CRIMSON_FUNGUS_PLANTED
		//TODO: WARPED_FUNGUS
		//TODO: WARPED_FUNGUS_PLANTED

		$featureFactory->register(self::HUGE_BROWN_MUSHROOM, new HugeBrownMushroomFeature(new HugeMushroomFeatureConfiguration(
			BlockStateProvider::simple(BlockFactory::get(BlockIds::BROWN_MUSHROOM_BLOCK)),
			BlockStateProvider::simple(BlockFactory::get(BlockIds::BROWN_MUSHROOM_BLOCK, 10)),
			3
		)));

		$featureFactory->register(self::HUGE_RED_MUSHROOM, new HugeRedMushroomFeature(new HugeMushroomFeatureConfiguration(
			BlockStateProvider::simple(BlockFactory::get(BlockIds::RED_MUSHROOM_BLOCK)),
			BlockStateProvider::simple(BlockFactory::get(BlockIds::RED_MUSHROOM_BLOCK, 10)),
			2
		)));

		$beehive0002 = new BeehiveDecorator(0.002);
		$beehive001 = new BeehiveDecorator(0.01);
		$beehive002 = new BeehiveDecorator(0.02);
		$beehive005 = new BeehiveDecorator(0.05);
		$beehive = new BeehiveDecorator(1.0);
		$sparseLeafLitter = new PlaceOnGroundDecorator(
			96, 4, 2, new WeightedStateProvider([]) //TODO:
		);
		$thickLeafLitter = new PlaceOnGroundDecorator(
			150, 2, 2, new WeightedStateProvider([]) //TODO:
		);

		$featureFactory->register(self::OAK, new TreeFeature(self::createOak()->build()));
		$featureFactory->register(self::DARK_OAK, new TreeFeature(self::createDarkOak()->ignoreVines()->build()));
		$featureFactory->register(
			self::PALE_OAK,
			new TreeFeature(
				self::createPaleOak()
					->decorators([new PaleMossDecorator(0.15, 0.4, 0.8)])
					->build()));
		$featureFactory->register(
			self::PALE_OAK_BONEMEAL,
			new TreeFeature(
				self::createPaleOak()
					->build()));
		$featureFactory->register(
			self::PALE_OAK_CREAKING,
			new TreeFeature(
				self::createPaleOak()
					->decorators([new PaleMossDecorator(0.15, 0.4, 0.8), new CreakingHeartDecorator(1.0)])
					->build()));
		$featureFactory->register(self::BIRCH, new TreeFeature(self::createBirch()->build()));
		$featureFactory->register(
			self::ACACIA,
			new TreeFeature(
				(new TreeConfigurationBuilder(
					BlockStateProvider::simple(BlockFactory::get(BlockIds::LOG2, Log2::ACACIA)),
					new ForkingTrunkPlacer(5, 2, 2),
					BlockStateProvider::simple(BlockFactory::get(BlockIds::LEAVES2, Leaves2::ACACIA)),
					new AcaciaFoliagePlacer(ConstantInt::of(2), ConstantInt::of(0)),
					null,
					new TwoLayersFeatureSize(1, 0, 2)
				))
					->ignoreVines()
					->build()
			));
		$featureFactory->register(self::CHERRY, new TreeFeature(self::createCherry()->build()));
		$featureFactory->register(self::CHERRY_BEES_005, new TreeFeature(self::createCherry()->decorators([$beehive005])->build()));
		$featureFactory->register(
			self::PINE,
			new TreeFeature(
				(new TreeConfigurationBuilder(
					BlockStateProvider::simple(BlockFactory::get(BlockIds::LOG, Log::SPRUCE)),
					new StraightTrunkPlacer(6, 4, 0),
					BlockStateProvider::simple(BlockFactory::get(BlockIds::LEAVES, Leaves::SPRUCE)),
					new PineFoliagePlacer(ConstantInt::of(1), ConstantInt::of(1), UniformInt::of(3, 4)),
					null,
					new TwoLayersFeatureSize(2, 0, 2)
				))
					->ignoreVines()
					->build()
			));
		$featureFactory->register(
			self::JUNGLE_TREE,
			new TreeFeature(
				self::createJungleTree()
					->decorators([new CocoaDecorator(0.2), new TrunkVineDecorator(), new LeaveVineDecorator(0.25)])
					->ignoreVines()
					->build()
			));
		$featureFactory->register(self::FANCY_OAK, new TreeFeature(self::createFancyOak()->build()));
		$featureFactory->register(self::JUNGLE_TREE_NO_VINE, new TreeFeature(self::createJungleTree()->ignoreVines()->build()));
		$featureFactory->register(
			self::MEGA_JUNGLE_TREE,
			new TreeFeature(
				(new TreeConfigurationBuilder(
					BlockStateProvider::simple(BlockFactory::get(BlockIds::LOG, Log::JUNGLE)),
					new MegaJungleTrunkPlacer(10, 2, 19),
					BlockStateProvider::simple(BlockFactory::get(BlockIds::LEAVES, Leaves::JUNGLE)),
					new MegaJungleFoliagePlacer(ConstantInt::of(2), ConstantInt::of(0), 2),
					null,
					new TwoLayersFeatureSize(1, 1, 2)
				))
					->decorators([new TrunkVineDecorator(), new LeaveVineDecorator(0.25)])
					->build()
			));
		$featureFactory->register(
			self::SPRUCE,
			new TreeFeature(
				(new TreeConfigurationBuilder(
					BlockStateProvider::simple(BlockFactory::get(BlockIds::LOG, Log::SPRUCE)),
					new StraightTrunkPlacer(5, 2, 1),
					BlockStateProvider::simple(BlockFactory::get(BlockIds::LEAVES, Leaves::SPRUCE)),
					new SpruceFoliagePlacer(UniformInt::of(2, 3), UniformInt::of(0, 2), UniformInt::of(1, 2)),
					null,
					new TwoLayersFeatureSize(2, 0, 2)
				))
					->ignoreVines()
					->build()
			));
		$featureFactory->register(
			self::MEGA_SPRUCE,
			new TreeFeature(
				(new TreeConfigurationBuilder(
					BlockStateProvider::simple(BlockFactory::get(BlockIds::LOG, Log::SPRUCE)),
					new GiantTrunkPlacer(13, 2, 14),
					BlockStateProvider::simple(BlockFactory::get(BlockIds::LEAVES, Leaves::SPRUCE)),
					new MegaPineFoliagePlacer(ConstantInt::of(0), ConstantInt::of(0), UniformInt::of(13, 17)),
					null,
					new TwoLayersFeatureSize(1, 1, 2)
				))
					->decorators([new AlterGroundDecorator(BlockStateProvider::simple(BlockFactory::get(BlockIds::PODZOL)))])
					->build()
			));
		$featureFactory->register(
			self::MEGA_PINE,
			new TreeFeature(
				(new TreeConfigurationBuilder(
					BlockStateProvider::simple(BlockFactory::get(BlockIds::LOG, Log::SPRUCE)),
					new GiantTrunkPlacer(13, 2, 14),
					BlockStateProvider::simple(BlockFactory::get(BlockIds::LEAVES, Leaves::SPRUCE)),
					new MegaPineFoliagePlacer(ConstantInt::of(0), ConstantInt::of(0), UniformInt::of(3, 7)),
					null,
					new TwoLayersFeatureSize(1, 1, 2)
				))
					->decorators([new AlterGroundDecorator(BlockStateProvider::simple(BlockFactory::get(BlockIds::PODZOL)))])
					->build()
			));
		$featureFactory->register(self::SUPER_BIRCH_BEES_0002, new TreeFeature(self::createSuperBirch()->decorators([$beehive0002])->build()));
		$featureFactory->register(self::SUPER_BIRCH_BEES, new TreeFeature(self::createSuperBirch()->decorators([$beehive])->build()));
		$featureFactory->register(self::SWAMP_OAK, new TreeFeature(self::createStraightBlobTree(BlockFactory::get(BlockIds::LOG), BlockFactory::get(BlockIds::LEAVES), 5, 3, 0, 3)->decorators([new LeaveVineDecorator(0.25)])->build()));
		$featureFactory->register(
			self::JUNGLE_BUSH,
			new TreeFeature(
				(new TreeConfigurationBuilder(
					BlockStateProvider::simple(BlockFactory::get(BlockIds::LOG, Log::JUNGLE)),
					new StraightTrunkPlacer(1, 0, 0),
					BlockStateProvider::simple(BlockFactory::get(BlockIds::LEAVES)),
					new BushFoliagePlacer(ConstantInt::of(2), ConstantInt::of(1), 2),
					null,
					new TwoLayersFeatureSize(0, 0, 0)
				))
					->build()
			));
		$featureFactory->register(
			self::AZALEA_TREE,
			new TreeFeature(
				(new TreeConfigurationBuilder(
					BlockStateProvider::simple(BlockFactory::get(BlockIds::LOG)),
					new BendingTrunkPlacer(4, 2, 0, 3, UniformInt::of(1, 2)),
					new WeightedStateProvider([
						BlockFactory::get(BlockIds::AZALEA_LEAVES),
						BlockFactory::get(BlockIds::AZALEA_LEAVES),
						BlockFactory::get(BlockIds::AZALEA_LEAVES),
						BlockFactory::get(BlockIds::AZALEA_LEAVES_FLOWERED)
					]),
					new RandomSpreadFoliagePlacer(ConstantInt::of(3), ConstantInt::of(0), ConstantInt::of(2), 50),
					null,
					new TwoLayersFeatureSize(1, 0, 1)
				))
					->dirt(BlockStateProvider::simple(BlockFactory::get(BlockIds::DIRT_WITH_ROOTS)))
					->forceDirt()
					->build()
			));
		$featureFactory->register(
			self::MANGROVE,
			new TreeFeature(
				(new TreeConfigurationBuilder(
					BlockStateProvider::simple(BlockFactory::get(BlockIds::MANGROVE_LOG)),
					new UpwardsBranchingTrunkPlacer(2, 1, 4, UniformInt::of(1, 4), 0.5, UniformInt::of(0, 1), [
						BlockFactory::get(BlockIds::MUD),
						BlockFactory::get(BlockIds::MUDDY_MANGROVE_ROOTS),
						BlockFactory::get(BlockIds::MANGROVE_ROOTS),
						BlockFactory::get(BlockIds::MANGROVE_LEAVES),
						BlockFactory::get(BlockIds::MANGROVE_LOG),
						BlockFactory::get(BlockIds::MANGROVE_PROPAGULE),
						BlockFactory::get(BlockIds::MOSS_CARPET),
						BlockFactory::get(BlockIds::VINE)
					]),
					BlockStateProvider::simple(BlockFactory::get(BlockIds::MANGROVE_LEAVES)),
					new RandomSpreadFoliagePlacer(ConstantInt::of(3), ConstantInt::of(0), ConstantInt::of(2), 70),
					new MangroveRootPlacer(
						UniformInt::of(1, 3),
						BlockStateProvider::simple(BlockFactory::get(BlockIds::MANGROVE_ROOTS)),
						new AboveRootPlacement(BlockStateProvider::simple(BlockFactory::get(BlockIds::MOSS_CARPET)), 0.5),
						new MangroveRootPlacement(
							[
								BlockFactory::get(BlockIds::MUD),
								BlockFactory::get(BlockIds::MUDDY_MANGROVE_ROOTS),
								BlockFactory::get(BlockIds::MANGROVE_ROOTS),
								BlockFactory::get(BlockIds::MOSS_CARPET),
								BlockFactory::get(BlockIds::VINE),
								BlockFactory::get(BlockIds::MANGROVE_PROPAGULE),
								BlockFactory::get(BlockIds::SNOW),
							],
							[BlockFactory::get(BlockIds::MUD), BlockFactory::get(BlockIds::MUDDY_MANGROVE_ROOTS)],
							BlockStateProvider::simple(BlockFactory::get(BlockIds::MUDDY_MANGROVE_ROOTS)),
							8,
							15,
							0.2
						)
					),
					new TwoLayersFeatureSize(2, 0, 2)
				))
					->decorators([
						new LeaveVineDecorator(0.125),
						new AttachedToLeavesDecorator(
							0.14,
							1,
							0,
							new RandomizedIntStateProvider(
								BlockStateProvider::simple(BlockFactory::get(BlockIds::MANGROVE_PROPAGULE, 5)),
								new class(UniformInt::of(0, 4)) extends RandomizedIntStateSetter { //random age
									protected function changeMeta(int $oldMeta, int $value) : int
									{
										$hanging = (int) ($oldMeta / 5);
										return $value + ($hanging * 5);
									}
								}
							),
							2,
							[Facing::DOWN]
						),
						$beehive001
					])
					->forceDirt()
					->build()
			));
		$featureFactory->register(
			self::TALL_MANGROVE,
			new TreeFeature(
				(new TreeConfigurationBuilder(
					BlockStateProvider::simple(BlockFactory::get(BlockIds::MANGROVE_LOG)),
					new UpwardsBranchingTrunkPlacer(4, 1, 9, UniformInt::of(1, 6), 0.5, UniformInt::of(0, 1), [
						BlockFactory::get(BlockIds::MUD),
						BlockFactory::get(BlockIds::MUDDY_MANGROVE_ROOTS),
						BlockFactory::get(BlockIds::MANGROVE_ROOTS),
						BlockFactory::get(BlockIds::MANGROVE_LEAVES),
						BlockFactory::get(BlockIds::MANGROVE_LOG),
						BlockFactory::get(BlockIds::MANGROVE_PROPAGULE),
						BlockFactory::get(BlockIds::MOSS_CARPET),
						BlockFactory::get(BlockIds::VINE)
					]),
					BlockStateProvider::simple(BlockFactory::get(BlockIds::MANGROVE_LEAVES)),
					new RandomSpreadFoliagePlacer(ConstantInt::of(3), ConstantInt::of(0), ConstantInt::of(2), 70),
					new MangroveRootPlacer(
						UniformInt::of(3, 7),
						BlockStateProvider::simple(BlockFactory::get(BlockIds::MANGROVE_ROOTS)),
						new AboveRootPlacement(BlockStateProvider::simple(BlockFactory::get(BlockIds::MOSS_CARPET)), 0.5),
						new MangroveRootPlacement(
							[
								BlockFactory::get(BlockIds::MUD),
								BlockFactory::get(BlockIds::MUDDY_MANGROVE_ROOTS),
								BlockFactory::get(BlockIds::MANGROVE_ROOTS),
								BlockFactory::get(BlockIds::MOSS_CARPET),
								BlockFactory::get(BlockIds::VINE),
								BlockFactory::get(BlockIds::MANGROVE_PROPAGULE),
								BlockFactory::get(BlockIds::SNOW),
							],
							[BlockFactory::get(BlockIds::MUD), BlockFactory::get(BlockIds::MUDDY_MANGROVE_ROOTS)],
							BlockStateProvider::simple(BlockFactory::get(BlockIds::MUDDY_MANGROVE_ROOTS)),
							8,
							15,
							0.2
						)
					),
					new TwoLayersFeatureSize(3, 0, 2)
				))
					->decorators([
						new LeaveVineDecorator(0.125),
						new AttachedToLeavesDecorator(
							0.14,
							1,
							0,
							new RandomizedIntStateProvider(
								BlockStateProvider::simple(BlockFactory::get(BlockIds::MANGROVE_PROPAGULE, 5)),
								new class(UniformInt::of(0, 4)) extends RandomizedIntStateSetter { //random age
									protected function changeMeta(int $oldMeta, int $value) : int
									{
										$hanging = (int) ($oldMeta / 5);
										return $value + ($hanging * 5);
									}
								}
							),
							2,
							[Facing::DOWN]
						),
						$beehive001
					])
					->forceDirt()
					->build()
			));
		$featureFactory->register(self::OAK_BEES_0002_LEAF_LITTER, new TreeFeature(self::createOak()->decorators([$beehive0002, $sparseLeafLitter, $thickLeafLitter])->build()));
		$featureFactory->register(self::OAK_BEES_0002, new TreeFeature(self::createOak()->decorators([$beehive0002])->build()));
		$featureFactory->register(self::OAK_BEES_002, new TreeFeature(self::createOak()->decorators([$beehive002])->build()));
		$featureFactory->register(self::OAK_BEES_005, new TreeFeature(self::createOak()->decorators([$beehive005])->build()));
		$featureFactory->register(self::BIRCH_BEES_0002, new TreeFeature(self::createBirch()->decorators([$beehive0002])->build()));
		$featureFactory->register(self::BIRCH_BEES_0002_LEAF_LITTER, new TreeFeature(self::createBirch()->decorators([$beehive0002, $sparseLeafLitter, $thickLeafLitter])->build()));
		$featureFactory->register(self::BIRCH_BEES_002, new TreeFeature(self::createBirch()->decorators([$beehive002])->build()));
		$featureFactory->register(self::BIRCH_BEES_005, new TreeFeature(self::createBirch()->decorators([$beehive005])->build()));
		$featureFactory->register(self::FANCY_OAK_BEES_0002_LEAF_LITTER, new TreeFeature(self::createFancyOak()->decorators([$beehive0002, $sparseLeafLitter, $thickLeafLitter])->build()));
		$featureFactory->register(self::FANCY_OAK_BEES_0002, new TreeFeature(self::createFancyOak()->decorators([$beehive0002])->build()));
		$featureFactory->register(self::FANCY_OAK_BEES_002, new TreeFeature(self::createFancyOak()->decorators([$beehive002])->build()));
		$featureFactory->register(self::FANCY_OAK_BEES_005, new TreeFeature(self::createFancyOak()->decorators([$beehive005])->build()));
		$featureFactory->register(self::FANCY_OAK_BEES, new TreeFeature(self::createFancyOak()->decorators([$beehive])->build()));
		$featureFactory->register(self::OAK_LEAF_LITTER, new TreeFeature(self::createOak()->decorators([$sparseLeafLitter, $thickLeafLitter])->build()));
		$featureFactory->register(self::DARK_OAK_LEAF_LITTER, new TreeFeature(self::createDarkOak()->ignoreVines()->decorators([$sparseLeafLitter, $thickLeafLitter])->build()));
		$featureFactory->register(self::BIRCH_LEAF_LITTER, new TreeFeature(self::createBirch()->decorators([$sparseLeafLitter, $thickLeafLitter])->build()));
		$featureFactory->register(self::FANCY_OAK_LEAF_LITTER, new TreeFeature(self::createFancyOak()->decorators([$sparseLeafLitter, $thickLeafLitter])->build()));

		//TODO: FALLEN_OAK_TREE
		//TODO: FALLEN_BIRCH_TREE
		//TODO: FALLEN_SUPER_BIRCH_TREE;
		//TODO: FALLEN_JUNGLE_TREE
		//TODO: FALLEN_SPRUCE_TREE
	}

	private static function createStraightBlobTree(Block $oakLog, Block $oakLeaves, int $baseHeight, int $heightRandA, int $heightRandB, int $blobRadius) : TreeConfigurationBuilder {
		return new TreeConfigurationBuilder(
			BlockStateProvider::simple($oakLog),
			new StraightTrunkPlacer($baseHeight, $heightRandA, $heightRandB),
			BlockStateProvider::simple($oakLeaves),
			new BlobFoliagePlacer(ConstantInt::of($blobRadius), ConstantInt::of(0), 3),
			null,
			new TwoLayersFeatureSize(1, 0, 1)
		);
	}

	private static function createOak() : TreeConfigurationBuilder {
		return self::createStraightBlobTree(BlockFactory::get(BlockIds::LOG), BlockFactory::get(BlockIds::LEAVES), 4, 2, 0, 2)->ignoreVines();
	}

	private static function createDarkOak() : TreeConfigurationBuilder{
		return new TreeConfigurationBuilder(
			BlockStateProvider::simple(BlockFactory::get(BlockIds::LOG2, Log2::DARK_OAK)),
			new DarkOakTrunkPlacer(6, 2, 1),
			BlockStateProvider::simple(BlockFactory::get(BlockIds::LEAVES2, Leaves2::DARK_OAK)),
			new DarkOakFoliagePlacer(ConstantInt::of(0), ConstantInt::of(0)),
			null,
			new ThreeLayersFeatureSize(1, 1, 0, 1, 2, null)
		);
	}

	private static function createFallenOak() : FallenTreeConfigurationBuilder {
		return self::createFallenTrees(BlockFactory::get(BlockIds::LOG), 4, 7)->stumpDecorators([new TrunkVineDecorator()]);
	}

	private static function createFallenBirch(int $maxHeight) : FallenTreeConfigurationBuilder{
		return self::createFallenTrees(BlockFactory::get(BlockIds::LOG, Log::BIRCH), 5, $maxHeight);
	}

	private static function createFallenJungle() : FallenTreeConfigurationBuilder {
		return self::createFallenTrees(BlockFactory::get(BlockIds::LOG, Log::JUNGLE), 4, 11)->stumpDecorators([new TrunkVineDecorator()]);
	}

	private static function createFallenSpruce() : FallenTreeConfigurationBuilder{
		return self::createFallenTrees(BlockFactory::get(BlockIds::LOG, Log::SPRUCE), 6, 10);
	}

	private static function createFallenTrees(Block $logBlock, int $minLength, int $maxLength) : FallenTreeConfigurationBuilder{
		return (new FallenTreeConfigurationBuilder(BlockStateProvider::simple($logBlock), UniformInt::of($minLength, $maxLength)))
			->logDecorators(
				[
					new AttachedToLogsDecorator(
						0.1,
						new WeightedStateProvider([BlockFactory::get(BlockIds::RED_MUSHROOM), BlockFactory::get(BlockIds::BROWN_MUSHROOM)]),
						[Facing::UP]
					)
				]
			);
	}

	private static function createBirch() : TreeConfigurationBuilder {
		return self::createStraightBlobTree(BlockFactory::get(BlockIds::LOG, Log::BIRCH), BlockFactory::get(BlockIds::LEAVES, Leaves::BIRCH), 5, 2, 0, 2)->ignoreVines();
	}

	private static function createSuperBirch() : TreeConfigurationBuilder {
		return self::createStraightBlobTree(BlockFactory::get(BlockIds::LOG, Log::BIRCH), BlockFactory::get(BlockIds::LEAVES, Leaves::BIRCH), 5, 2, 6, 2)->ignoreVines();
	}

	private static function createJungleTree() : TreeConfigurationBuilder {
		return self::createStraightBlobTree(BlockFactory::get(BlockIds::LOG, Log::JUNGLE), BlockFactory::get(BlockIds::LEAVES, Leaves::JUNGLE), 4, 8, 0, 2);
	}

	private static function createFancyOak() : TreeConfigurationBuilder {
		return (new TreeConfigurationBuilder(
			BlockStateProvider::simple(BlockFactory::get(BlockIds::LOG)),
			new FancyTrunkPlacer(3, 11, 0),
			BlockStateProvider::simple(BlockFactory::get(BlockIds::LEAVES)),
			new FancyFoliagePlacer(ConstantInt::of(2), ConstantInt::of(4), 4),
			null,
			new TwoLayersFeatureSize(0, 0, 0, 4)
		))->ignoreVines();
	}

	private static function createCherry() : TreeConfigurationBuilder {
		return (new TreeConfigurationBuilder(
			BlockStateProvider::simple(BlockFactory::get(BlockIds::CHERRY_LOG)),
			new CherryTrunkPlacer(
				7,
				1,
				0,
				new WeightedListInt([
					ConstantInt::of(1),
					ConstantInt::of(2),
					ConstantInt::of(3),
				]),
				UniformInt::of(2, 4),
				UniformInt::of(-4, -3),
				UniformInt::of(-1, 0)
			),
			BlockStateProvider::simple(BlockFactory::get(BlockIds::CHERRY_LEAVES)),
			new CherryFoliagePlacer(ConstantInt::of(4), ConstantInt::of(0), ConstantInt::of(5), 0.25, 0.5, 0.16666667, 0.33333334),
			null,
			new TwoLayersFeatureSize(1, 0, 2)
		))->ignoreVines();
	}

	private static function createPaleOak() : TreeConfigurationBuilder {
		return (new TreeConfigurationBuilder(
			BlockStateProvider::simple(BlockFactory::get(BlockIds::PALE_OAK_LOG)),
			new DarkOakTrunkPlacer(6, 2, 1),
			BlockStateProvider::simple(BlockFactory::get(BlockIds::PALE_OAK_LEAVES)),
			new DarkOakFoliagePlacer(ConstantInt::of(0), ConstantInt::of(0)),
			null,
			new ThreeLayersFeatureSize(1, 1, 0, 1, 2, null)
		))->ignoreVines();
	}
}
