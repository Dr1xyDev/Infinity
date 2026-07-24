<?php

/*
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.0
 *               InfinityProject By @Dr1xyDev
 *   YT:         @Dr1xyDev
 *   GitHub:     github.com/Dr1xyDev/Infinity
*/

namespace pocketmine\level\generator\object;

use pocketmine\block\Block;
use pocketmine\block\Leaves;
use pocketmine\block\Sapling;
use pocketmine\block\Wood;
use pocketmine\level\ChunkManager;
use pocketmine\level\generator\normal\object\AcaciaTree as CustomAcaciaTree;
use pocketmine\level\generator\normal\object\BigSpruceTree;
use pocketmine\level\generator\normal\object\DarkOakTree as CustomDarkOakTree;
use pocketmine\math\Vector3;
use pocketmine\utils\Random;

abstract class Tree{
        public $overridable = [
                Block::AIR => true,
                6 => true,
                17 => true,
                18 => true,
                Block::SNOW_LAYER => true,
                Block::LOG2 => true,
                Block::LEAVES2 => true
        ];

        public $type = 0;
        public $trunkBlock = Block::LOG;
        public $leafBlock = Block::LEAVES;
        public $treeHeight = 7;
        public $leafType = 0;

        /**
         * Grows a tree at the given position.
         *
         * @param ChunkManager $level     The world/level
         * @param int          $x         X coordinate
         * @param int          $y         Y coordinate
         * @param int          $z         Z coordinate
         * @param Random       $random    Random number generator
         * @param int          $type      Sapling type (0=oak, 1=spruce, 2=birch, 3=jungle, 4=acacia, 5=dark_oak)
         * @param bool         $noBigTree Whether to prevent big tree variants.
         *                                 Default changed to false to allow BigJungleTree
         *                                 and BigTree (oak) variants during biome generation.
         */
        public static function growTree(ChunkManager $level, $x, $y, $z, Random $random, $type = 0, bool $noBigTree = false){
                switch($type){
                        case Sapling::SPRUCE:
                                // BigSpruceTree appears with 5% chance when big trees are allowed.
                                // Reduced from 10% to make huge trees rarer (per user request).
                                if(!$noBigTree and $random->nextBoundedInt(20) === 0){
                                        $tree = new BigSpruceTree(0.3, 3);
                                }else{
                                        $tree = new SpruceTree();
                                }
                                break;
                        case Sapling::BIRCH:
                                if($random->nextBoundedInt(39) === 0){
                                        $tree = new BirchTree(true);
                                }else{
                                        $tree = new BirchTree();
                                }
                                break;
                        case Sapling::JUNGLE:
                                // NewJungleTree is the default jungle tree.
                                // BigJungleTree chance raised to 25% (was 5%) per user
                                // request, so huge jungle trees show up a lot more.
                                $pos = new Vector3($x, $y, $z);
                                if(!$noBigTree and $random->nextBoundedInt(4) === 0){
                                        $jungleTree = new BigJungleTree(10, 20, Block::get(Block::LOG, Wood::JUNGLE), Block::get(Block::LEAVES, Leaves::JUNGLE));
                                }else{
                                        $jungleTree = new NewJungleTree(4, 10);
                                }
                                $jungleTree->generate($level, $random, $pos);
                                return;
                        case Sapling::ACACIA:
                                // CustomAcaciaTree extends CustomTree and implements generate(),
                                // which handles all placement checks internally and returns bool.
                                $acaciaTree = new CustomAcaciaTree();
                                $acaciaTree->generate($level, $random, new Vector3($x, $y, $z));
                                return;
                        case Sapling::DARK_OAK:
                                // CustomDarkOakTree extends CustomTree and implements generate(),
                                // which handles all placement checks internally and returns bool.
                                $darkOakTree = new CustomDarkOakTree();
                                $darkOakTree->generate($level, $random, new Vector3($x, $y, $z));
                                return;
                        case Sapling::OAK:
                        default:
                                // BigTree (huge oak) with ~5% chance when big trees are allowed.
                                // Reduced from 10% to make huge trees rarer (per user request).
                                if(!$noBigTree and $random->nextRange(0, 19) === 0){
                                        $tree = new BigTree();
                                }else{
                                        $tree = new OakTree();
                                }
                                break;
                }
                if($tree->canPlaceObject($level, $x, $y, $z, $random)){
                        $tree->placeObject($level, $x, $y, $z, $random);
                }
        }


        public function canPlaceObject(ChunkManager $level, $x, $y, $z, Random $random){
                $radiusToCheck = 0;
                for($yy = 0; $yy < $this->treeHeight + 3; ++$yy){
                        if($yy == 1 or $yy === $this->treeHeight){
                                ++$radiusToCheck;
                        }
                        for($xx = -$radiusToCheck; $xx < ($radiusToCheck + 1); ++$xx){
                                for($zz = -$radiusToCheck; $zz < ($radiusToCheck + 1); ++$zz){
                                        if(!isset($this->overridable[$level->getBlockIdAt($x + $xx, $y + $yy, $z + $zz)])){
                                                return false;
                                        }
                                }
                        }
                }

                return true;
        }

        public function placeObject(ChunkManager $level, $x, $y, $z, Random $random){

                $this->placeTrunk($level, $x, $y, $z, $random, $this->treeHeight - 1);

                for($yy = $y - 3 + $this->treeHeight; $yy <= $y + $this->treeHeight; ++$yy){
                        $yOff = $yy - ($y + $this->treeHeight);
                        $mid = (int) (1 - $yOff / 2);
                        for($xx = $x - $mid; $xx <= $x + $mid; ++$xx){
                                $xOff = abs($xx - $x);
                                for($zz = $z - $mid; $zz <= $z + $mid; ++$zz){
                                        $zOff = abs($zz - $z);
                                        if($xOff === $mid and $zOff === $mid and ($yOff === 0 or $random->nextBoundedInt(2) === 0)){
                                                continue;
                                        }
                                        if(!Block::$solid[$level->getBlockIdAt($xx, $yy, $zz)]){
                                                $level->setBlockIdAt($xx, $yy, $zz, $this->leafBlock);
                                                $level->setBlockDataAt($xx, $yy, $zz, $this->leafType);
                                        }
                                }
                        }
                }
        }

        protected function placeTrunk(ChunkManager $level, $x, $y, $z, Random $random, $trunkHeight){
                // The base dirt block
                $level->setBlockIdAt($x, $y - 1, $z, Block::DIRT);

                for($yy = 0; $yy < $trunkHeight; ++$yy){
                        $blockId = $level->getBlockIdAt($x, $y + $yy, $z);
                        if(isset($this->overridable[$blockId])){
                                $level->setBlockIdAt($x, $y + $yy, $z, $this->trunkBlock);
                                $level->setBlockDataAt($x, $y + $yy, $z, $this->type);
                        }
                }
        }
}
