<?php

/*
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.0
 *               InfinityProject By @Dr1xyDev
 *   YT:         @Dr1xyDev
 *   GitHub:     github.com/Dr1xyDev/Infinity
*/

namespace pocketmine\level\generator\normal\river;

use pocketmine\block\Block;
use pocketmine\level\ChunkManager;
use pocketmine\level\generator\biome\Biome;
use pocketmine\level\generator\populator\Populator;
use pocketmine\utils\Random;

class RiverDecorator extends Populator{

        /** @var ChunkManager */
        private $level;

        /** @var RiverNoise */
        private $riverNoise;

        /** @var int */
        private $waterHeight;

        public function __construct(RiverNoise $riverNoise, int $waterHeight){
                $this->riverNoise = $riverNoise;
                $this->waterHeight = $waterHeight;
        }

        public function populate(ChunkManager $level, $chunkX, $chunkZ, Random $random){
                $this->level = $level;
                $chunk = $level->getChunk($chunkX, $chunkZ);

                for($x = 0; $x < 16; ++$x){
                        for($z = 0; $z < 16; ++$z){
                                $biomeId = $chunk->getBiomeId($x, $z);

                                if($biomeId !== Biome::RIVER){
                                        continue;
                                }

                                $worldX = $chunkX * 16 + $x;
                                $worldZ = $chunkZ * 16 + $z;

                                // Compute distance and mask for this position
                                $estimatedDist = $this->riverNoise->getEstimatedDistance($worldX, $worldZ);
                                $widthNoise = $this->riverNoise->getWidthNoise($worldX, $worldZ);
                                $targetWidth = RiverMask::clamp(8.0 + $widthNoise * 2.0, 3, 16);
                                $bankNoise = $this->riverNoise->getBankNoise($worldX, $worldZ);
                                $maskData = RiverMask::compute($estimatedDist, $targetWidth, $bankNoise);

                                if(!$maskData['isRiver']){
                                        continue;
                                }

                                // Detect original biome from neighbors
                                $originalBiomeId = $this->detectOriginalBiome($chunk, $x, $z, $chunkX, $chunkZ);

                                // Near-bank decoration (where terrain meets water)
                                $isNearBank = $maskData['riverIntensity'] < 0.6;
                                if($isNearBank){
                                        $this->placeBankDecoration($worldX, $worldZ, $originalBiomeId, $maskData, $random);
                                }
                        }
                }
        }

        private function detectOriginalBiome($chunk, $x, $z, $chunkX, $chunkZ) : int{
                $neighbors = [
                        [$x + 1, $z], [$x - 1, $z],
                        [$x, $z + 1], [$x, $z - 1],
                ];

                foreach($neighbors as $n){
                        $nx = $n[0]; $nz = $n[1];

                        if($nx >= 0 && $nx < 16 && $nz >= 0 && $nz < 16){
                                $nBiomeId = $chunk->getBiomeId($nx, $nz);
                        }else{
                                $adjCX = $chunkX + ($nx >= 16 ? 1 : ($nx < 0 ? -1 : 0));
                                $adjCZ = $chunkZ + ($nz >= 16 ? 1 : ($nz < 0 ? -1 : 0));
                                $adjChunk = $this->level->getChunk($adjCX, $adjCZ);
                                if($adjChunk !== null){
                                        $localNx = $nx >= 16 ? 0 : ($nx < 0 ? 15 : $nx);
                                        $localNz = $nz >= 16 ? 0 : ($nz < 0 ? 15 : $nz);
                                        $nBiomeId = $adjChunk->getBiomeId($localNx, $localNz);
                                }else{
                                        $nBiomeId = Biome::PLAINS;
                                }
                        }

                        if($nBiomeId !== Biome::RIVER && $nBiomeId !== Biome::OCEAN){
                                return $nBiomeId;
                        }
                }

                return Biome::PLAINS;
        }

        private function placeBankDecoration(int $worldX, int $worldZ, int $originalBiomeId, array $maskData, Random $random){
                // Sugar cane near water edge
                $sugarcaneAmount = RiverBiomeMixer::getSugarcaneAmount($originalBiomeId);
                if($random->nextBoundedInt(max(1, 20 - $sugarcaneAmount)) === 0){
                        $y = $this->findBankSurface($worldX, $worldZ);
                        if($y > 0 && $this->canPlaceSugarcane($worldX, $y, $worldZ)){
                                $height = $random->nextRange(2, 3);
                                for($dy = 0; $dy < $height; ++$dy){
                                        $by = $y + $dy;
                                        if($by < 127 && $this->level->getBlockIdAt($worldX, $by, $worldZ) === Block::AIR){
                                                $this->level->setBlockIdAt($worldX, $by, $worldZ, Block::SUGARCANE_BLOCK);
                                        }
                                }
                        }
                }

                // Tall grass on grass blocks near banks
                $tallGrassAmount = RiverBiomeMixer::getTallGrassAmount($originalBiomeId);
                if($random->nextBoundedInt(max(1, 15 - $tallGrassAmount)) === 0){
                        $y = $this->findBankSurface($worldX, $worldZ);
                        if($y > 0 && $this->level->getBlockIdAt($worldX, $y - 1, $worldZ) === Block::GRASS
                                && $this->level->getBlockIdAt($worldX, $y, $worldZ) === Block::AIR){
                                $this->level->setBlockIdAt($worldX, $y, $worldZ, Block::TALL_GRASS);
                                $this->level->setBlockDataAt($worldX, $y, $worldZ, 1);
                        }
                }

                // Flowers near banks (plains, forest)
                $flowerAmount = RiverBiomeMixer::getFlowerAmount($originalBiomeId);
                if($flowerAmount > 0 && $random->nextBoundedInt(max(1, 15 - $flowerAmount)) === 0){
                        $y = $this->findBankSurface($worldX, $worldZ);
                        if($y > 0 && $this->level->getBlockIdAt($worldX, $y - 1, $worldZ) === Block::GRASS
                                && $this->level->getBlockIdAt($worldX, $y, $worldZ) === Block::AIR){
                                $flowerId = $random->nextBoundedInt(2) === 0 ? Block::DANDELION : Block::RED_FLOWER;
                                $this->level->setBlockIdAt($worldX, $y, $worldZ, $flowerId);
                        }
                }
        }

        private function findBankSurface(int $x, int $z) : int{
                for($y = 127; $y >= 0; --$y){
                        $b = $this->level->getBlockIdAt($x, $y, $z);
                        if($b !== Block::AIR && $b !== Block::STILL_WATER
                                && $b !== Block::WATER && $b !== Block::TALL_GRASS
                                && $b !== Block::SNOW_LAYER && $b !== Block::LEAVES
                                && $b !== Block::LEAVES2 && $b !== Block::WATER_LILY
                                && $b !== Block::SUGARCANE_BLOCK){
                                return $y + 1;
                        }
                }
                return 0;
        }

        private function canPlaceSugarcane(int $x, int $y, int $z) : bool{
                $b = $this->level->getBlockIdAt($x, $y, $z);
                if($b !== Block::AIR) return false;

                $below = $this->level->getBlockIdAt($x, $y - 1, $z);
                if($below !== Block::SAND && $below !== Block::GRASS
                        && $below !== Block::DIRT && $below !== Block::SUGARCANE_BLOCK) return false;

                $adjacent = [
                        $this->level->getBlockIdAt($x + 1, $y - 1, $z),
                        $this->level->getBlockIdAt($x - 1, $y - 1, $z),
                        $this->level->getBlockIdAt($x, $y - 1, $z + 1),
                        $this->level->getBlockIdAt($x, $y - 1, $z - 1),
                ];

                foreach($adjacent as $adj){
                        if($adj === Block::WATER || $adj === Block::STILL_WATER){
                                return true;
                        }
                }
                return $below === Block::SUGARCANE_BLOCK;
        }
}
