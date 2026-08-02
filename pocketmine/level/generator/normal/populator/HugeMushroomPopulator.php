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
use pocketmine\level\generator\normal\object\mushroom\BigMushroom;
use pocketmine\math\Vector3;
use pocketmine\utils\Random;

class HugeMushroomPopulator extends Populator{

        
        private ChunkManager $level;

        private int   $randomAmount  = 0;
        private int   $baseAmount    = 0;

        
        private float $spawnChance = 1.0;

        
        private int $mushroomType = -1;

        public function setRandomAmount(int $amount): void{
                $this->randomAmount = $amount;
        }

        public function setBaseAmount(int $amount): void{
                $this->baseAmount = $amount;
        }

        
        public function setSpawnChance(float $chance): void{
                $this->spawnChance = max(0.0, min(1.0, $chance));
        }

        
        public function setMushroomType(int $type): void{
                $this->mushroomType = $type;
        }

        public function populate(ChunkManager $level, $chunkX, $chunkZ, Random $random): void{
                $this->level = $level;

                
                
                if($this->spawnChance < 1.0){
                        if($random->nextBoundedInt(100) >= (int)($this->spawnChance * 100)){
                                return; 
                        }
                }

                $amount = $random->nextRange(0, $this->randomAmount + 1) + $this->baseAmount;
                for($i = 0; $i < $amount; ++$i){
                        $x = $random->nextRange($chunkX * 16 + 3, $chunkX * 16 + 12);
                        $z = $random->nextRange($chunkZ * 16 + 3, $chunkZ * 16 + 12);
                        $y = $this->getHighestWorkableBlock($x, $z);
                        if($y === -1){
                                continue;
                        }

                        
                        $groundBlock = $this->level->getBlockIdAt($x, $y - 1, $z);
                        if($groundBlock !== Block::DIRT && $groundBlock !== Block::GRASS && $groundBlock !== Block::MYCELIUM){
                                continue;
                        }

                        
                        $type = $this->mushroomType >= 0
                                ? $this->mushroomType
                                : ($random->nextBoolean() ? BigMushroom::RED : BigMushroom::BROWN);

                        $mushroom = new BigMushroom($type);
                        $mushroom->generate($this->level, $random, new Vector3($x, $y, $z));
                }
        }

        
        private function getHighestWorkableBlock(int $x, int $z): int{
                for($y = 127; $y >= 0; --$y){
                        $b = $this->level->getBlockIdAt($x, $y, $z);
                        if($b !== Block::AIR && $b !== Block::LEAVES && $b !== Block::LEAVES2
                                && $b !== Block::SNOW_LAYER && $b !== Block::TALL_GRASS
                                && $b !== Block::SAPLING && $b !== Block::VINE
                                && $b !== Block::RED_MUSHROOM && $b !== Block::BROWN_MUSHROOM
                                && $b !== Block::STILL_WATER && $b !== Block::WATER){
                                return ++$y;
                        }
                }
                return -1;
        }
}
