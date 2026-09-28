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
use pocketmine\level\generator\vanilla\level\biome\BiomeNoise;
use pocketmine\level\generator\vanilla\level\format\Chunk;
use pocketmine\level\generator\vanilla\level\generator\noise\synth\PerlinSimplexNoise;
use pocketmine\level\generator\vanilla\utils\Random;

class SwampSurfaceBuilder extends DefaultSurfaceBuilder {

	protected PerlinSimplexNoise $infoNoise;

	public function __construct(){
		$this->infoNoise = BiomeNoise::getInstance()->getInfoNoise();
	}

	public function buildSurface(Random $random, Chunk $chunk, Biome $biome, int $x, int $z, int $startHeight, float $noise, Block $defaultBlock, Block $defaultFluid, int $seaLevel, int $seed, SurfaceBuilderConfig $config) : void{
		$groundValue = $this->infoNoise->getValue2D($x * 0.25, $z * 0.25);
		if ($groundValue > 0.0) {
			$i = $x & Chunk::COORD_MASK;
			$j = $z & Chunk::COORD_MASK;

			for ($k = $startHeight; $k >= 0; --$k) {
				$block = BlockFactory::fromFullBlock($chunk->getFullBlock($i, $k, $j));

				if ($block->getId() === BlockIds::AIR) {
					if ($k == 62 && !$block->isSameType($defaultFluid)) {
						$chunk->setFullBlock($i, $k, $j, $defaultFluid->getFullId());
					}
					break;
				}
			}
		}

		parent::buildSurface($random, $chunk, $biome, $x, $z, $startHeight, $noise, $defaultBlock, $defaultFluid, $seaLevel, $seed, $config);
	}
}
