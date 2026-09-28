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

namespace pocketmine\level\generator\vanilla\level\generator\surfacebuilders;

use pocketmine\level\generator\vanilla\block\Block;
use pocketmine\level\generator\vanilla\block\BlockFactory;
use pocketmine\level\generator\vanilla\block\BlockIds;
use pocketmine\level\generator\vanilla\block\Dirt;
use pocketmine\level\generator\vanilla\level\biome\Biome;
use pocketmine\level\generator\vanilla\level\format\Chunk;
use pocketmine\level\generator\vanilla\utils\Random;

class ShatteredSavannaSurfaceBuilder extends DefaultSurfaceBuilder {

	protected SurfaceBuilderConfig $stoneStoneGravel;
	protected SurfaceBuilderConfig $coarseDirtDirtGravel;
	protected SurfaceBuilderConfig $grassDirtGravel;

	public function __construct(){
		$stone = BlockFactory::get(BlockIds::STONE);
		$gravel = BlockFactory::get(BlockIds::GRAVEL);
		$grass = BlockFactory::get(BlockIds::GRASS);
		$dirt = BlockFactory::get(BlockIds::DIRT);
		$coarseDirt = BlockFactory::get(BlockIds::DIRT, Dirt::TYPE_COARSE);

		$this->stoneStoneGravel = new SurfaceBuilderConfig($stone, $stone, $gravel);
		$this->coarseDirtDirtGravel = new SurfaceBuilderConfig($coarseDirt, $dirt, $gravel);
		$this->grassDirtGravel = new SurfaceBuilderConfig($grass, $dirt, $gravel);
	}

	public function buildSurface(Random $random, Chunk $chunk, Biome $biome, int $x, int $z, int $startHeight, float $noise, Block $defaultBlock, Block $defaultFluid, int $seaLevel, int $seed, SurfaceBuilderConfig $config) : void{
		if ($noise > 1.75) {
			parent::buildSurface($random, $chunk, $biome, $x, $z, $startHeight, $noise, $defaultBlock, $defaultFluid, $seaLevel, $seed, $this->stoneStoneGravel);
		} elseif ($noise > -0.5) {
			parent::buildSurface($random, $chunk, $biome, $x, $z, $startHeight, $noise, $defaultBlock, $defaultFluid, $seaLevel, $seed, $this->coarseDirtDirtGravel);
		} else {
			parent::buildSurface($random, $chunk, $biome, $x, $z, $startHeight, $noise, $defaultBlock, $defaultFluid, $seaLevel, $seed, $this->grassDirtGravel);
		}
	}
}
