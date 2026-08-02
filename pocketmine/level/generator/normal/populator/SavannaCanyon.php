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

class SavannaCanyon extends Populator{

        
        private $level;

        public function populate(ChunkManager $level, $chunkX, $chunkZ, Random $random){
                $this->level = $level;

                
                if($random->nextBoundedInt(33) !== 0){
                        return;
                }

                
                $cx = $random->nextRange($chunkX * 16 + 4, $chunkX * 16 + 11);
                $cz = $random->nextRange($chunkZ * 16 + 4, $chunkZ * 16 + 11);

                
                $surfaceY = $this->getHighestBlock($cx, $cz);
                if($surfaceY <= 62){
                        return; 
                }

                
                $halfWidth = $random->nextRange(3, 5);  
                $depth = $random->nextRange(8, 14);      
                $length = $random->nextRange(8, 12);     

                
                $isEastWest = $random->nextBoolean();

                
                $branchLength = $random->nextRange(3, 6);
                $branchStart = $random->nextRange(2, $length - 2);

                
                $this->digTrench($cx, $cz, $surfaceY, $halfWidth, $depth, $length, $isEastWest);

                
                $branchCx = $cx + ($isEastWest ? 0 : $branchStart);
                $branchCz = $cz + ($isEastWest ? $branchStart : 0);
                $this->digTrench($branchCx, $branchCz, $surfaceY, $halfWidth - 1, $depth - 2, $branchLength, !$isEastWest);
        }

        
        private function digTrench($cx, $cz, $surfaceY, $halfWidth, $depth, $length, $isEastWest){
                for($i = 0; $i < $length; ++$i){
                        
                        $bx = $cx + ($isEastWest ? $i : 0);
                        $bz = $cz + ($isEastWest ? 0 : $i);

                        
                        $posRatio = $i / max(1, $length - 1);
                        $widthFactor = 1.0 - abs($posRatio - 0.5) * 0.4; 
                        $localHalfWidth = max(2, (int)($halfWidth * $widthFactor));

                        
                        $depthFactor = 1.0 - abs($posRatio - 0.5) * 0.3;
                        $localDepth = max(4, (int)($depth * $depthFactor));

                        
                        for($w = -$localHalfWidth; $w <= $localHalfWidth; ++$w){
                                $wx = $bx + ($isEastWest ? 0 : $w);
                                $wz = $bz + ($isEastWest ? $w : 0);

                                
                                $edgeRatio = abs($w) / max(1, $localHalfWidth);
                                $localDepthAtPos = max(2, (int)($localDepth * (1.0 - $edgeRatio * 0.6)));

                                
                                for($dy = 0; $dy < $localDepthAtPos; ++$dy){
                                        $by = $surfaceY - $dy;
                                        if($by <= 0) continue;

                                        if($dy === 0){
                                                
                                                $this->level->setBlockIdAt($wx, $by, $wz, Block::SAND);
                                                
                                                if($by > 0){
                                                        $this->level->setBlockIdAt($wx, $by - 1, $wz, Block::SANDSTONE);
                                                }
                                        }elseif($edgeRatio > 0.7){
                                                
                                                $this->level->setBlockIdAt($wx, $by, $wz, Block::SANDSTONE);
                                        }else{
                                                
                                                $this->level->setBlockIdAt($wx, $by, $wz, Block::AIR);
                                        }
                                }
                        }
                }
        }

        
        private function getHighestBlock($x, $z){
                for($y = 127; $y >= 0; --$y){
                        $b = $this->level->getBlockIdAt($x, $y, $z);
                        if($b !== Block::AIR && $b !== Block::TALL_GRASS
                                && $b !== Block::SNOW_LAYER && $b !== Block::SAPLING
                                && $b !== Block::STILL_WATER && $b !== Block::WATER){
                                return $y;
                        }
                }
                return 0;
        }
}
