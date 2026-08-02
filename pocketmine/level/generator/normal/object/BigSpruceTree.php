<?php
/*    
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█    
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░    
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.1
 *               InfinityProject By @Dr1xyDev    
 *   YT:         @Dr1xyDev    
 *   GitHub:     github.com/Dr1xyDev/Infinity    
*/

namespace pocketmine\level\generator\normal\object;

use pocketmine\level\generator\normal\populator\helper\PopulatorHelpers;
use pocketmine\block\Block;
use pocketmine\level\ChunkManager;
use pocketmine\level\generator\object\SpruceTree;
use pocketmine\utils\Random;

class BigSpruceTree extends SpruceTree
{
                
                private $leafStartHeightMultiplier;
                private $baseLeafRadius;

                public function __construct($leafStartHeightMultiplier, $baseLeafRadius)
                {
                                parent::__construct();

                                $this->leafStartHeightMultiplier = $leafStartHeightMultiplier;
                                $this->baseLeafRadius = $baseLeafRadius;
                }

                public function placeObject(ChunkManager $level, $x, $y, $z, Random $random)
                {
                                $this->treeHeight = $random->nextBoundedInt(15) + 20;

                                
                                $this->placeTrunk($level, $x, $y, $z, $random, $this->treeHeight);

                                
                                
                                $topY = $y + $this->treeHeight;

                                
                                $this->placeLeafRing($level, $x, $z, $topY, 1);

                                
                                $this->placeLeafRing($level, $x, $z, $topY - 2, 2);

                                
                                $this->placeLeafRing($level, $x, $z, $topY - 4, 2);
                }

                protected function placeTrunk(ChunkManager $level, $x, $y, $z, Random $random, $trunkHeight)
                {
                                
                                $level->setBlockIdAt($x, $y - 1, $z, Block::DIRT);
                                $level->setBlockIdAt($x + 1, $y - 1, $z, Block::DIRT);
                                $level->setBlockIdAt($x, $y - 1, $z + 1, Block::DIRT);
                                $level->setBlockIdAt($x + 1, $y - 1, $z + 1, Block::DIRT);

                                $trunkWidth = 2;

                                for ($yy = 0; $yy < $trunkHeight; ++$yy) {
                                                for ($xx = 0; $xx < $trunkWidth; $xx++) {
                                                                for ($zz = 0; $zz < $trunkWidth; $zz++) {
                                                                                $block = $level->getBlockIdAt($x + $xx, $y + $yy, $z + $zz);
                                                                                if ($this->canOverride(Block::get($block))) {
                                                                                                $level->setBlockIdAt($x + $xx, $y + $yy, $z + $zz, $this->trunkBlock);
                                                                                                $level->setBlockDataAt($x + $xx, $y + $yy, $z + $zz, $this->type);
                                                                                }
                                                                }
                                                }
                                }
                }

                public function canOverride(Block $block): bool
                {
                                return isset($this->overridable[$block->getId()]);
                }

                
                private function placeLeafRing(ChunkManager $level, $x, $z, $ringY, $radius)
                {
                                for ($xx = $x - $radius; $xx <= $x + 1 + $radius; ++$xx) {
                                                
                                                if ($xx < $x) {
                                                                $dx = $x - $xx;
                                                } elseif ($xx > $x + 1) {
                                                                $dx = $xx - ($x + 1);
                                                } else {
                                                                $dx = 0;
                                                }

                                                for ($zz = $z - $radius; $zz <= $z + 1 + $radius; ++$zz) {
                                                                
                                                                if ($zz < $z) {
                                                                                $dz = $z - $zz;
                                                                } elseif ($zz > $z + 1) {
                                                                                $dz = $zz - ($z + 1);
                                                                } else {
                                                                                $dz = 0;
                                                                }

                                                                
                                                                if ($dx >= $radius && $dz >= $radius && $radius > 0) {
                                                                                continue;
                                                                }

                                                                if (PopulatorHelpers::isNonSolid($level->getBlockIdAt($xx, $ringY, $zz))) {
                                                                                $level->setBlockIdAt($xx, $ringY, $zz, $this->leafBlock);
                                                                                $level->setBlockDataAt($xx, $ringY, $zz, $this->type);
                                                                }
                                                }
                                }
                }
}
