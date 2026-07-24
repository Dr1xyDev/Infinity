<?php

/*
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.0
 *               InfinityProject By @Dr1xyDev
 *   YT:         @Dr1xyDev
 *   GitHub:     github.com/Dr1xyDev/Infinity
*/

namespace pocketmine\level\generator\normal\populator;

use pocketmine\block\Block;
use pocketmine\level\ChunkManager;
use pocketmine\level\generator\populator\Populator;
use pocketmine\utils\Random;

class JunglePit extends Populator{

        /** @var ChunkManager */
        private $level;
        private $randomAmount;
        private $baseAmount;

        public function setRandomAmount($amount){
                $this->randomAmount = $amount;
        }

        public function setBaseAmount($amount){
                $this->baseAmount = $amount;
        }

        public function populate(ChunkManager $level, $chunkX, $chunkZ, Random $random){
                $this->level = $level;
                $amount = $random->nextRange(0, $this->randomAmount + 1) + $this->baseAmount;
                for($i = 0; $i < $amount; ++$i){
                        $x = $random->nextRange($chunkX * 16 + 2, $chunkX * 16 + 13);
                        $z = $random->nextRange($chunkZ * 16 + 2, $chunkZ * 16 + 13);

                        // Find the surface height at this position
                        $surfaceY = $this->getHighestWorkableBlock($x, $z);
                        if($surfaceY === -1){
                                continue;
                        }

                        // Check if this is a natural depression: surrounding blocks must be higher
                        if(!$this->isDepression($x, $surfaceY, $z)){
                                continue;
                        }

                        // Place a small puddle filling the depression area
                        $this->placePuddle($x, $surfaceY, $z);
                }
        }

        /**
         * Checks if the position (x, y, z) is in a natural terrain depression.
         * A depression means that at least 3 of the 4 neighboring blocks
         * (north, south, east, west) are at a higher elevation (y+1 or more).
         */
        private function isDepression($x, $y, $z){
                $higherNeighbors = 0;
                // Check all 4 cardinal neighbors
                $neighbors = [
                        [$x + 1, $z],
                        [$x - 1, $z],
                        [$x, $z + 1],
                        [$x, $z - 1]
                ];

                foreach($neighbors as $n){
                        $ny = $this->getHighestWorkableBlock($n[0], $n[1]);
                        if($ny !== -1 && $ny > $y){
                                ++$higherNeighbors;
                        }
                }

                // Require at least 3 higher neighbors to qualify as a depression
                // This ensures puddles only appear in genuine low spots
                return $higherNeighbors >= 3;
        }

        /**
         * Places a small water puddle at and around the depression point.
         * Fills the center block with water, and also fills any adjacent
         * blocks that are at the same height or lower (within the depression).
         * This creates puddles that look like natural water collecting in valleys.
         */
        private function placePuddle($x, $y, $z){
                // Place water at the center depression point
                $this->setWaterAt($x, $y, $z);

                // Also fill adjacent same-height blocks that form the depression floor
                // This creates puddles of 2-5 blocks instead of just 1
                $adjacent = [
                        [$x + 1, $z],
                        [$x - 1, $z],
                        [$x, $z + 1],
                        [$x, $z - 1]
                ];

                foreach($adjacent as $n){
                        $ny = $this->getHighestWorkableBlock($n[0], $n[1]);
                        if($ny !== -1 && $ny <= $y){
                                $block = $this->level->getBlockIdAt($n[0], $ny, $n[1]);
                                if($block === Block::GRASS || $block === Block::DIRT || $block === Block::AIR){
                                        $this->setWaterAt($n[0], $ny, $n[1]);
                                }
                        }
                }
        }

        /**
         * Sets a water block at the given position and ensures dirt underneath.
         */
        private function setWaterAt($x, $y, $z){
                $block = $this->level->getBlockIdAt($x, $y, $z);
                // Only replace grass, dirt, or air (not stone, not already water)
                if($block === Block::GRASS || $block === Block::DIRT || $block === Block::AIR){
                        // Ensure block below is solid (dirt) so water sits properly
                        $below = $this->level->getBlockIdAt($x, $y - 1, $z);
                        if($below === Block::GRASS){
                                $this->level->setBlockIdAt($x, $y - 1, $z, Block::DIRT);
                        }
                        $this->level->setBlockIdAt($x, $y, $z, Block::STILL_WATER);
                        $this->level->setBlockDataAt($x, $y, $z, 0);
                }
        }

        /**
         * Finds the surface height at (x, z) by scanning downward from y=127.
         * Returns the y coordinate of the surface block (grass, dirt, stone, etc.)
         * Returns -1 if no valid surface found.
         */
        private function getHighestWorkableBlock($x, $z){
                for($y = 127; $y >= 0; --$y){
                        $b = $this->level->getBlockIdAt($x, $y, $z);
                        if($b !== Block::AIR && $b !== Block::LEAVES && $b !== Block::LEAVES2
                                && $b !== Block::SNOW_LAYER && $b !== Block::SAPLING
                                && $b !== Block::VINE && $b !== Block::TALL_GRASS
                                && $b !== Block::STILL_WATER && $b !== Block::WATER){
                                return $y;
                        }
                }
                return -1;
        }
}
