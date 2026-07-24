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
use pocketmine\block\Sapling;
use pocketmine\level\ChunkManager;
use pocketmine\level\generator\object\Tree as ObjectTree;
use pocketmine\utils\Random;

class Tree extends Populator{
        /** @var ChunkManager */
        private $level;
        private $randomAmount;
        private $baseAmount;

        private $type;

        /**
         * When true, big/huge tree variants are never spawned by this
         * populator. Used by Plains sub-biomes (OakPlains, JunglePlains,
         * TaigaPlains, etc.) which per user request must only contain
         * the normal (small) tree variants of each species.
         *
         * @var bool
         */
        private $noBigTree = false;

        public function __construct($type = Sapling::OAK){
                $this->type = $type;
        }

        public function setRandomAmount($amount){
                $this->randomAmount = $amount;
        }

        public function setBaseAmount($amount){
                $this->baseAmount = $amount;
        }

        /**
         * Disables big/huge tree variants for this populator instance.
         * Only the normal-size tree of the configured species will spawn.
         */
        public function setNoBigTree(bool $noBigTree = true){
                $this->noBigTree = $noBigTree;
        }

        public function populate(ChunkManager $level, $chunkX, $chunkZ, Random $random){
                $this->level = $level;
                $amount = $random->nextRange(0, $this->randomAmount + 1) + $this->baseAmount;
                for($i = 0; $i < $amount; ++$i){
                        $x = $random->nextRange($chunkX << 4, ($chunkX << 4) + 15);
                        $z = $random->nextRange($chunkZ << 4, ($chunkZ << 4) + 15);
                        $y = $this->getHighestWorkableBlock($x, $z);
                        if($y === -1){
                                continue;
                        }
                        ObjectTree::growTree($this->level, $x, $y, $z, $random, $this->type, $this->noBigTree);
                }
        }

        private function getHighestWorkableBlock($x, $z){
                for($y = 127; $y > 0; --$y){
                        $b = $this->level->getBlockIdAt($x, $y, $z);
                        if($b === Block::DIRT or $b === Block::GRASS or $b === Block::PODZOL){
                                break;
                        }elseif($b !== 0 and $b !== Block::SNOW_LAYER){
                                return -1;
                        }
                }

                return ++$y;
        }
}
