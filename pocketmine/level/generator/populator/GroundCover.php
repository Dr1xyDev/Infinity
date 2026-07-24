<?php

/*
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.0
 *               InfinityProject By @Dr1xyDev
 *   YT:         @Dr1xyDev
 *   GitHub:     github.com/Dr1xyDev/Infinity
*/

namespace pocketmine\level\generator\populator;

use pocketmine\block\Block;
use pocketmine\level\ChunkManager;
use pocketmine\level\generator\biome\Biome;
use pocketmine\level\Level;
use pocketmine\level\SimpleChunkManager;
use pocketmine\utils\Random;

class GroundCover extends Populator{

        public function populate(ChunkManager $level, $chunkX, $chunkZ, Random $random){
                $chunk = $level->getChunk($chunkX, $chunkZ);
                if($level instanceof Level or $level instanceof SimpleChunkManager){
                        $waterHeight = $level->getWaterHeight();
                } else $waterHeight = 0;
                for($x = 0; $x < 16; ++$x){
                        for($z = 0; $z < 16; ++$z){
                                $biomeId = $chunk->getBiomeId($x, $z);

                                // Skip RIVER biome columns - they are handled by
                                // RiverChunkProcessor which applies biome-specific
                                // ground cover (desert rivers get sand, taiga rivers
                                // get gravel, etc.) This prevents the default RiverBiome
                                // cover (clay/sand) from overriding the biome-specific cover.
                                if($biomeId === Biome::RIVER){
                                        continue;
                                }

                                $biome = Biome::getBiome($biomeId);
                                $cover = $biome->getGroundCover();
                                if(count($cover) > 0){
                                        $diffY = 0;
                                        if(!$cover[0]->isSolid()){
                                                $diffY = 1;
                                        }

                                        $column = $chunk->getBlockIdColumn($x, $z);
                                        for($y = 127; $y > 0; --$y){
                                                if($column[$y] !== "\x00" and !Block::get(ord($column[$y]))->isTransparent()){
                                                        break;
                                                }
                                        }
                                        $startY = min(127, $y + $diffY);
                                        $endY = $startY - count($cover);
                                        for($y = $startY; $y > $endY and $y >= 0; --$y){
                                                $b = $cover[$startY - $y];
                                                if($column[$y] === "\x00" and $b->isSolid()){
                                                        break;
                                                }
                                                if($y <= $waterHeight and $b->getId() == Block::GRASS and $chunk->getBlockId($x, $y + 1, $z) == Block::STILL_WATER){
                                                        $b = Block::get(Block::DIRT);
                                                }
                                                if($b->getDamage() === 0){
                                                        $chunk->setBlockId($x, $y, $z, $b->getId());
                                                }else{
                                                        $chunk->setBlock($x, $y, $z, $b->getId(), $b->getDamage());
                                                }
                                        }
                                }
                        }
                }
        }
}
