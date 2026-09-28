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

namespace pocketmine\level\generator\vanilla\level\generator\feature\configurations;

use pocketmine\level\generator\vanilla\level\generator\feature\featuresize\FeatureSize;
use pocketmine\level\generator\vanilla\level\generator\feature\foliageplacers\FoliagePlacer;
use pocketmine\level\generator\vanilla\level\generator\feature\rootplacers\RootPlacer;
use pocketmine\level\generator\vanilla\level\generator\feature\stateproviders\BlockStateProvider;
use pocketmine\level\generator\vanilla\level\generator\feature\treedecorators\TreeDecorator;
use pocketmine\level\generator\vanilla\level\generator\feature\trunkplacers\TrunkPlacer;

class TreeConfiguration implements FeatureConfiguration {
	/**
	 * @param TreeDecorator[] $decorators
	 */
	public function __construct(
		public BlockStateProvider $trunkProvider,
		public BlockStateProvider $dirtProvider,
		public TrunkPlacer $trunkPlacer,
		public BlockStateProvider $foliageProvider,
		public FoliagePlacer $foliagePlacer,
		public ?RootPlacer $rootPlacer,
		public FeatureSize $minimumSize,
		public array $decorators,
		public bool $ignoreVines,
		public bool $forceDirt
	){}

}
