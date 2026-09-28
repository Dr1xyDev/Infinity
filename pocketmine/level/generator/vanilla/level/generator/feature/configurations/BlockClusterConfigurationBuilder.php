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

use pocketmine\level\generator\vanilla\block\Block;
use pocketmine\level\generator\vanilla\level\generator\feature\blockplacer\BlockPlacer;
use pocketmine\level\generator\vanilla\level\generator\feature\blocksupport\BlockSupport;
use pocketmine\level\generator\vanilla\level\generator\feature\stateproviders\BlockStateProvider;

class BlockClusterConfigurationBuilder implements FeatureConfiguration{
	public BlockStateProvider $stateProvider;
	public BlockPlacer $blockPlacer;
	public BlockSupport $supportBlock;
	/** @var Block[] */
	public array $whitelist = [];
	/** @var Block[] */
	public array $blacklist = [];
	public int $tryCount = 64;
	public int $xSpread = 7;
	public int $ySpread = 3;
	public int $zSpread = 7;
	public bool $isReplaceable = false;
	public bool $requiresWater = false;

	public function __construct(BlockStateProvider $stateProvider, BlockPlacer $blockPlacer, BlockSupport $supportBlock){
		$this->stateProvider = $stateProvider;
		$this->blockPlacer = $blockPlacer;
		$this->supportBlock = $supportBlock;
	}

	/**
	 * @param Block[] $whitelist
	 */
	public function whitelist(array $whitelist) : self{
		$this->whitelist = $whitelist;
		return $this;
	}

	/**
	 * @param Block[] $blacklist
	 */
	public function blacklist(array $blacklist) : self{
		$this->blacklist = $blacklist;
		return $this;
	}

	public function tries(int $tries) : self{
		$this->tryCount = $tries;
		return $this;
	}

	public function xSpread(int $xSpread) : self{
		$this->xSpread = $xSpread;
		return $this;
	}

	public function ySpread(int $ySpread) : self{
		$this->ySpread = $ySpread;
		return $this;
	}

	public function zSpread(int $zSpread) : self{
		$this->zSpread = $zSpread;
		return $this;
	}

	public function replaceable() : self{
		$this->isReplaceable = true;
		return $this;
	}

	public function requiresWater() : self{
		$this->requiresWater = true;
		return $this;
	}

	public function build() : BlockClusterConfiguration {
		return new BlockClusterConfiguration(
			$this->stateProvider,
			$this->blockPlacer,
			$this->supportBlock,
			$this->whitelist,
			$this->blacklist,
			$this->tryCount,
			$this->xSpread,
			$this->ySpread,
			$this->zSpread,
			$this->isReplaceable,
			$this->requiresWater
		);
	}
}
