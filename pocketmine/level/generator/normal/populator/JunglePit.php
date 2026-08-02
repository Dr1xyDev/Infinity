<?php
/*    
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█    
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░    
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.1
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

                        
                        $surfaceY = $this->getHighestWorkableBlock($x, $z);
                        if($surfaceY === -1){
                                continue;
                        }

                        
                        if(!$this->isDepression($x, $surfaceY, $z)){
                                continue;
                        }

                        
                        $this->placePuddle($x, $surfaceY, $z);
                }
        }

        
        private function isDepression($x, $y, $z){
                $higherNeighbors = 0;
                
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

                
                
                return $higherNeighbors >= 3;
        }

        
        private function placePuddle($x, $y, $z){
                
                $this->setWaterAt($x, $y, $z);

                
                
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

        
        private function setWaterAt($x, $y, $z){
                $block = $this->level->getBlockIdAt($x, $y, $z);
                
                if($block === Block::GRASS || $block === Block::DIRT || $block === Block::AIR){
                        
                        $below = $this->level->getBlockIdAt($x, $y - 1, $z);
                        if($below === Block::GRASS){
                                $this->level->setBlockIdAt($x, $y - 1, $z, Block::DIRT);
                        }
                        $this->level->setBlockIdAt($x, $y, $z, Block::STILL_WATER);
                        $this->level->setBlockDataAt($x, $y, $z, 0);
                }
        }

        
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
