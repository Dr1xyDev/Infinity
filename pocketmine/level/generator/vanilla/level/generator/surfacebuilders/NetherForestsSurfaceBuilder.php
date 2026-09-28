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
use pocketmine\level\generator\vanilla\level\biome\Biome;
use pocketmine\level\generator\vanilla\level\format\Chunk;
use pocketmine\level\generator\vanilla\level\generator\noise\synth\PerlinSimplexNoise;
use pocketmine\level\generator\vanilla\utils\Random;

class NetherForestsSurfaceBuilder extends SurfaceBuilder {

	protected ?int $seed = null;

	protected ?PerlinSimplexNoise $noise = null;

	public function buildSurface(Random $random, Chunk $chunk, Biome $biome, int $x, int $z, int $startHeight, float $noise, Block $defaultBlock, Block $defaultFluid, int $seaLevel, int $seed, SurfaceBuilderConfig $config) : void{
		$i = $seaLevel;
		$j = $x & Chunk::COORD_MASK;
		$k = $z & Chunk::COORD_MASK;
		$d0 = $this->noise->getValue3D($x * 0.1, $seaLevel, $z * 0.1);
		$flag = $d0 > 0.15 + $random->nextFloat() * 0.35;
		$d1 = $this->noise->getValue3D($x * 0.1, 109.0, $z * 0.1);
		$flag1 = $d1 > 0.25 + $random->nextFloat() * 0.9;
		$l = (int) ($noise / 3.0 + 3.0 + $random->nextFloat() * 0.25);
		$i1 = -1;
		$blockState = $config->getUnder();

		for($j1 = 127; $j1 >= 0; --$j1) {
		 $blockState1 = $config->getTop();
			$blockState2 = BlockFactory::fromFullBlock($chunk->getFullBlock($j, $j1, $k));
		 if ($blockState2->getId() === BlockIds::AIR) {
			 $i1 = -1;
		 } elseif ($blockState2->isSameType($defaultBlock)) {
			 if ($i1 == -1) {
				 $flag2 = false;
			   if ($l <= 0) {
				   $flag2 = true;
				   $blockState = $config->getUnder();
			   }

			   if ($flag) {
				   $blockState1 = $config->getUnder();
			   } elseif ($flag1) {
				   $blockState1 = $config->getUnderWater();
			   }

			   if ($j1 < $i && $flag2) {
				   $blockState1 = $defaultFluid;
			   }

			   $i1 = $l;
			   if ($j1 >= $i - 1) {
				   $chunk->setFullBlock($j, $j1, $k, $blockState1->getFullId());
			   } else {
				   $chunk->setFullBlock($j, $j1, $k, $blockState->getFullId());
			   }
			} elseif ($i1 > 0) {
				 --$i1;
				 $chunk->setFullBlock($j, $j1, $k, $blockState->getFullId());
			}
		 }
	  }
	}

	public function setSeed(int $seed) : void{
		if ($this->seed !== $seed || $this->noise === null) {
			$random = new Random($seed);
			$this->noise = new PerlinSimplexNoise($random, 1);
		}

		$this->seed = $seed;
	}
}
