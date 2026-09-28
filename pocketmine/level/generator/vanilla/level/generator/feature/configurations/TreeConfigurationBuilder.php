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

use pocketmine\level\generator\vanilla\block\BlockFactory;
use pocketmine\level\generator\vanilla\block\BlockIds;
use pocketmine\level\generator\vanilla\level\generator\feature\featuresize\FeatureSize;
use pocketmine\level\generator\vanilla\level\generator\feature\foliageplacers\FoliagePlacer;
use pocketmine\level\generator\vanilla\level\generator\feature\rootplacers\RootPlacer;
use pocketmine\level\generator\vanilla\level\generator\feature\stateproviders\BlockStateProvider;
use pocketmine\level\generator\vanilla\level\generator\feature\treedecorators\TreeDecorator;
use pocketmine\level\generator\vanilla\level\generator\feature\trunkplacers\TrunkPlacer;

class TreeConfigurationBuilder {

	public BlockStateProvider $trunkProvider;
	private TrunkPlacer $trunkPlacer;
	public BlockStateProvider $foliageProvider;
	private FoliagePlacer $foliagePlacer;
	private ?RootPlacer $rootPlacer = null;
	private BlockStateProvider $dirtProvider;
	private FeatureSize $minimumSize;
	/** @var TreeDecorator[] */
	private array $decorators = [];
	private bool $ignoreVines = false;
	private bool $forceDirt = false;

	public function __construct(
		BlockStateProvider $trunkProvider,
		TrunkPlacer $trunkPlacer,
		BlockStateProvider $foliageProvider,
		FoliagePlacer $foliagePlacer,
		?RootPlacer $rootPlacer,
		FeatureSize $minimumSize
	){
		$this->trunkProvider = $trunkProvider;
		$this->trunkPlacer = $trunkPlacer;
		$this->foliageProvider = $foliageProvider;
		$this->dirtProvider = BlockStateProvider::simple(BlockFactory::get(BlockIds::DIRT));
		$this->foliagePlacer = $foliagePlacer;
		$this->rootPlacer = $rootPlacer;
		$this->minimumSize = $minimumSize;
	}

	public function dirt(BlockStateProvider $dirtProvider) : TreeConfigurationBuilder{
		$this->dirtProvider = $dirtProvider;
		return $this;
	}

	/**
	 * @param TreeDecorator[] $decorators
	 */
	public function decorators(array $decorators) : TreeConfigurationBuilder{
		$this->decorators = $decorators;
		return $this;
	}

	public function ignoreVines() : TreeConfigurationBuilder{
		$this->ignoreVines = true;
		return $this;
	}

	public function forceDirt() : TreeConfigurationBuilder{
		$this->forceDirt = true;
		return $this;
	}

	public function build() : TreeConfiguration{
		return new TreeConfiguration(
			$this->trunkProvider,
			$this->dirtProvider,
			$this->trunkPlacer,
			$this->foliageProvider,
			$this->foliagePlacer,
			$this->rootPlacer,
			$this->minimumSize,
			$this->decorators,
			$this->ignoreVines,
			$this->forceDirt
		);
	}
}
