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
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\utils\Random;
use function array_key_first;
use function count;
use const PHP_FLOAT_MAX;

abstract class ValleySurfaceBuilder extends SurfaceBuilder {

	protected ?int $seed = null;

	/** @var PerlinSimplexNoise[] */
	protected ?array $noiseMapUnder = null;
	/** @var PerlinSimplexNoise[] */
	protected ?array $noiseMapAbove = null;
	protected ?PerlinSimplexNoise $patchNoise = null;

	public function buildSurface(Random $random, Chunk $chunk, Biome $biome, int $x, int $z, int $startHeight, float $noise, Block $defaultBlock, Block $defaultFluid, int $seaLevel, int $seed, SurfaceBuilderConfig $config) : void{
		$i = $seaLevel + 1;
		$j = $x & Chunk::COORD_MASK;
		$k = $z & Chunk::COORD_MASK;
		$l = (int) ($noise / 3.0 + 3.0 + $random->nextFloat() * 0.25);
		$i1 = (int) ($noise / 3.0 + 3.0 + $random->nextFloat() * 0.25);
		$d0 = 0.03125;
		$flag = $this->patchNoise->getValue3D($x * 0.03125, 109.0, $z * 0.03125) * 75.0 + $random->nextFloat() > 0.0;
		$blockstate = $this->getMaxNoiseBlock($this->noiseMapAbove, $x, $seaLevel, $z);
		$blockstate1 = $this->getMaxNoiseBlock($this->noiseMapUnder, $x, $seaLevel, $z);
		$blockstate2 = BlockFactory::fromFullBlock($chunk->getFullBlock($j, 128, $k));

		for ($j1 = 127; $j1 >= 0; --$j1) {
			$mutable = new Vector3($j, $j1, $k);
			$blockstate3 = BlockFactory::fromFullBlock($chunk->getFullBlock($mutable->getFloorX(), $mutable->getFloorY(), $mutable->getFloorZ()));
			if ($blockstate2->isSameType($defaultBlock) && ($blockstate3->getId() === BlockIds::AIR || $blockstate3 === $defaultFluid)) {
				for ($k1 = 0; $k1 < $l; ++$k1) {
					$mutable = $mutable->up();
					if (!BlockFactory::fromFullBlock($chunk->getFullBlock($mutable->getFloorX(), $mutable->getFloorY(), $mutable->getFloorZ()))->isSameType($defaultBlock)) {
						break;
					}

					$chunk->setFullBlock($mutable->getFloorX(), $mutable->getFloorY(), $mutable->getFloorZ(), $blockstate->getFullId());
				}

				$mutable = new Vector3($j, $j1, $k);
			}

			if (($blockstate2->getId() === BlockIds::AIR || $blockstate2 == $defaultFluid) && $blockstate3->isSameType($defaultBlock)) {
				for ($l1 = 0; $l1 < $i1 && BlockFactory::fromFullBlock($chunk->getFullBlock($mutable->getFloorX(), $mutable->getFloorY(), $mutable->getFloorZ()))->isSameType($defaultBlock); ++$l1) {
					if ($flag && $j1 >= $i - 4 && $j1 <= $i + 1) {
						$chunk->setFullBlock($mutable->getFloorX(), $mutable->getFloorY(), $mutable->getFloorZ(), $this->getPatchBlock()->getFullId());
					} else {
						$chunk->setFullBlock($mutable->getFloorX(), $mutable->getFloorY(), $mutable->getFloorZ(), $blockstate1->getFullId());
					}

					$mutable = $mutable->down();
				}
			}

			$blockstate2 = $blockstate3;
		}
	}

	/**
	 * @param PerlinSimplexNoise[] $noiseMap
	 */
	private function getMaxNoiseBlock(array $noiseMap, int $x, int $seaY, int $z) : Block{
		$bestBlock = array_key_first($noiseMap);
		$bestValue = -PHP_FLOAT_MAX;

		foreach ($noiseMap as $fullId => $generator) {
			$value = $generator->getValue3D($x, $seaY, $z);
			if ($value > $bestValue) {
				$bestValue = $value;
				$bestBlock = $fullId;
			}
		}

		return BlockFactory::fromFullBlock($bestBlock);
	}

	public function setSeed(int $seed) : void{
		if ($this->seed !== $seed || $this->patchNoise === null || $this->noiseMapUnder === null || $this->noiseMapAbove === null) {
			$this->noiseMapUnder = $this->createNoiseMap($this->getUnderBlocks(), $seed);
			$this->noiseMapAbove = $this->createNoiseMap($this->getAboveBlocks(), $seed + count($this->getUnderBlocks()));
			$patchRandom = new Random($seed + count($this->getUnderBlocks()) + count($this->getAboveBlocks()));
			$this->patchNoise = new PerlinSimplexNoise($patchRandom, 1);
	  }

		$this->seed = $seed;
	}

	/**
	 * @param Block[] $blocks
	 * @return PerlinSimplexNoise[]
	 */
	private function createNoiseMap(array $blocks, int $seed) : array{
		$map = [];
		foreach ($blocks as $block) {
			$random = new Random($seed);
			$map[$block->getFullId()] = new PerlinSimplexNoise($random, 1);
			++$seed;
		}

		return $map;
	}

	/**
	 * @return Block[]
	 */
	abstract protected function getUnderBlocks() : array;

	/**
	 * @return Block[]
	 */
	abstract protected function getAboveBlocks() : array;

	abstract protected function getPatchBlock() : Block;
}
