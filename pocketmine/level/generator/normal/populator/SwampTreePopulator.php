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
use pocketmine\level\generator\normal\object\SwampTree;
use pocketmine\math\Vector3;
use pocketmine\utils\Random;

class SwampTreePopulator extends Populator{

        private ChunkManager $level;
        private int $randomAmount = 0;
        private int $baseAmount   = 0;

        public function setRandomAmount(int $amount): void{
                $this->randomAmount = $amount;
        }

        public function setBaseAmount(int $amount): void{
                $this->baseAmount = $amount;
        }

        public function populate(ChunkManager $level, $chunkX, $chunkZ, Random $random): void{
                $this->level = $level;
                $amount = $random->nextRange(0, $this->randomAmount + 1) + $this->baseAmount;

                for($i = 0; $i < $amount; ++$i){
                        $x = $random->nextRange($chunkX * 16 + 1, $chunkX * 16 + 14);
                        $z = $random->nextRange($chunkZ * 16 + 1, $chunkZ * 16 + 14);
                        $y = $this->getHighestWorkableBlock($x, $z);
                        if($y === -1){
                                continue;
                        }

                        $tree = new SwampTree();
                        $tree->generate($this->level, $random, new Vector3($x, $y, $z));
                }
        }

        private function getHighestWorkableBlock(int $x, int $z): int{
                for($y = 127; $y > 0; --$y){
                        $b = $this->level->getBlockIdAt($x, $y, $z);
                        if($b === Block::DIRT || $b === Block::GRASS){
                                break;
                        }elseif($b !== Block::AIR && $b !== Block::SNOW_LAYER
                                && $b !== Block::WATER && $b !== Block::STILL_WATER){
                                return -1;
                        }
                }
                return ++$y;
        }
}
