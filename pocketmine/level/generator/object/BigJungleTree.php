<?php
/*    
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█    
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░    
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.1
 *               InfinityProject By @Dr1xyDev    
 *   YT:         @Dr1xyDev    
 *   GitHub:     github.com/Dr1xyDev/Infinity    
*/

namespace pocketmine\level\generator\object;

use pocketmine\block\Block;
use pocketmine\block\Leaves;
use pocketmine\block\Wood;
use pocketmine\level\ChunkManager;
use pocketmine\level\generator\normal\math\FacingHelper;
use pocketmine\math\Vector3;
use pocketmine\utils\Random;

class BigJungleTree extends HugeTree
{

        public function __construct($baseHeightIn, $extraRandomHeight, Block $woodMetadata, Block $leavesMetadata)
        {
                parent::__construct($baseHeightIn, $extraRandomHeight, $woodMetadata, $leavesMetadata);
        }

        public function generate(ChunkManager $level, Random $rand, Vector3 $position): bool
        {
                $height = $this->getHeight($rand);

                if(!$this->ensureGrowable($level, $rand, $position, $height)){
                        return false;
                }

                for($j = $position->getY() + $height - 2 - $rand->nextBoundedInt(4); $j > $position->getY() + $height / 2; $j -= 2 + $rand->nextBoundedInt(4)){
                        $f = $rand->nextFloat() * ((float)M_PI * 2.0);
                        $k = ($position->getX() + (0.5 + cos($f) * 4.0));
                        $l = ($position->getZ() + (0.5 + sin($f) * 4.0));

                        
                        for($i1 = 0; $i1 < 5; ++$i1){
                                $k = ($position->getX() + (1.5 + cos($f) * (float)$i1));
                                $l = ($position->getZ() + (1.5 + sin($f) * (float)$i1));
                                $this->setBlockAndNotifyAdequately($level, new Vector3($k, $j - 3 + $i1 / 2, $l), $this->woodMetadata);
                        }

                        
                        $j2 = 1 + $rand->nextBoundedInt(2);
                        $j1 = $j;

                        for($k1 = $j - $j2; $k1 <= $j1; ++$k1){
                                $l1 = $k1 - $j1;
                                $this->growLeavesLayer($level, new Vector3($k, $k1, $l), 1 - $l1);
                        }
                }

                
                for($i2 = 0; $i2 < $height; ++$i2){
                        $blockpos = $position->getSide(Vector3::SIDE_UP, $i2);

                        if($this->canOverride(Block::get($level->getBlockIdAt($blockpos->getFloorX(), $blockpos->getFloorY(), $blockpos->getFloorZ())))){
                                $this->setBlockAndNotifyAdequately($level, $blockpos, $this->woodMetadata);

                                if($i2 > 0){
                                        $this->placeVine($level, $rand, $blockpos->getSide(Vector3::SIDE_WEST), 8);
                                        $this->placeVine($level, $rand, $blockpos->getSide(Vector3::SIDE_NORTH), 1);
                                }
                        }

                        if($i2 < $height - 1){
                                $blockpos1 = $blockpos->getSide(Vector3::SIDE_EAST);

                                if($this->canOverride(Block::get($level->getBlockIdAt($blockpos1->getFloorX(), $blockpos1->getFloorY(), $blockpos1->getFloorZ())))){
                                        $this->setBlockAndNotifyAdequately($level, $blockpos1, $this->woodMetadata);

                                        if($i2 > 0){
                                                $this->placeVine($level, $rand, $blockpos1->getSide(Vector3::SIDE_EAST), 2);
                                                $this->placeVine($level, $rand, $blockpos1->getSide(Vector3::SIDE_NORTH), 1);
                                        }
                                }

                                $blockpos2 = $blockpos->getSide(Vector3::SIDE_SOUTH)->getSide(Vector3::SIDE_EAST);

                                if($this->canOverride(Block::get($level->getBlockIdAt($blockpos2->getFloorX(), $blockpos2->getFloorY(), $blockpos2->getFloorZ())))){
                                        $this->setBlockAndNotifyAdequately($level, $blockpos2, $this->woodMetadata);

                                        if($i2 > 0){
                                                $this->placeVine($level, $rand, $blockpos2->getSide(Vector3::SIDE_EAST), 2);
                                                $this->placeVine($level, $rand, $blockpos2->getSide(Vector3::SIDE_SOUTH), 4);
                                        }
                                }

                                $blockpos3 = $blockpos->getSide(Vector3::SIDE_SOUTH);

                                if($this->canOverride(Block::get($level->getBlockIdAt($blockpos3->getFloorX(), $blockpos3->getFloorY(), $blockpos3->getFloorZ())))){
                                        $this->setBlockAndNotifyAdequately($level, $blockpos3, $this->woodMetadata);

                                        if($i2 > 0){
                                                $this->placeVine($level, $rand, $blockpos3->getSide(Vector3::SIDE_WEST), 8);
                                                $this->placeVine($level, $rand, $blockpos3->getSide(Vector3::SIDE_SOUTH), 4);
                                        }
                                }
                        }
                }

                $this->createVanillaCrown($level, $position->getSide(Vector3::SIDE_UP, $height), $rand);

                return true;
        }

        
        protected function createVanillaCrown(ChunkManager $level, Vector3 $pos, Random $rand)
        {
                
                $bottomRadius = 5;
                for($dx = -$bottomRadius; $dx <= $bottomRadius; ++$dx){
                        for($dz = -$bottomRadius; $dz <= $bottomRadius; ++$dz){
                                
                                $absDx = abs($dx);
                                $absDz = abs($dz);
                                if($absDx === $bottomRadius && $absDz === $bottomRadius){
                                        continue;
                                }
                                
                                if($absDx >= $bottomRadius - 1 && $absDz >= $bottomRadius - 1 && ($absDx + $absDz) >= $bottomRadius + 2){
                                        if($rand->nextBoundedInt(3) === 0){
                                                continue;
                                        }
                                }
                                $leafPos = new Vector3($pos->getX() + $dx, $pos->getY() - 2, $pos->getZ() + $dz);
                                $id = $level->getBlockIdAt($leafPos->getFloorX(), $leafPos->getFloorY(), $leafPos->getFloorZ());
                                if($id === Block::AIR || $id === Block::LEAVES){
                                        $this->setBlockAndNotifyAdequately($level, $leafPos, $this->leavesMetadata);
                                }
                        }
                }

                
                $middleRadius = 4;
                for($dx = -$middleRadius; $dx <= $middleRadius; ++$dx){
                        for($dz = -$middleRadius; $dz <= $middleRadius; ++$dz){
                                $absDx = abs($dx);
                                $absDz = abs($dz);
                                if($absDx === $middleRadius && $absDz === $middleRadius){
                                        continue;
                                }
                                if($absDx >= $middleRadius - 1 && $absDz >= $middleRadius - 1 && ($absDx + $absDz) >= $middleRadius + 2){
                                        if($rand->nextBoundedInt(2) === 0){
                                                continue;
                                        }
                                }
                                $leafPos = new Vector3($pos->getX() + $dx, $pos->getY() - 1, $pos->getZ() + $dz);
                                $id = $level->getBlockIdAt($leafPos->getFloorX(), $leafPos->getFloorY(), $leafPos->getFloorZ());
                                if($id === Block::AIR || $id === Block::LEAVES){
                                        $this->setBlockAndNotifyAdequately($level, $leafPos, $this->leavesMetadata);
                                }
                        }
                }

                
                
                $topRadius = 3;
                $topY = $pos->getY();
                for($dx = -$topRadius; $dx <= $topRadius; ++$dx){
                        for($dz = -$topRadius; $dz <= $topRadius; ++$dz){
                                $absDx = abs($dx);
                                $absDz = abs($dz);
                                if($absDx === $topRadius && $absDz === $topRadius){
                                        continue;
                                }
                                if($absDx >= $topRadius - 1 && $absDz >= $topRadius - 1 && ($absDx + $absDz) >= $topRadius + 2){
                                        if($rand->nextBoundedInt(2) === 0){
                                                continue;
                                        }
                                }
                                $leafPos = new Vector3($pos->getX() + $dx, $topY, $pos->getZ() + $dz);
                                $id = $level->getBlockIdAt($leafPos->getFloorX(), $leafPos->getFloorY(), $leafPos->getFloorZ());
                                if($id === Block::AIR || $id === Block::LEAVES){
                                        $this->setBlockAndNotifyAdequately($level, $leafPos, $this->leavesMetadata);
                                }
                        }
                }

                
                $topPos = new Vector3($pos->getX(), $topY, $pos->getZ());
                $this->placeLeafAt($level, $topPos->getSide(Vector3::SIDE_EAST, 3));
                $this->placeLeafAt($level, $topPos->getSide(Vector3::SIDE_WEST, 3));
                $this->placeLeafAt($level, $topPos->getSide(Vector3::SIDE_SOUTH, 3));
                $this->placeLeafAt($level, $topPos->getSide(Vector3::SIDE_NORTH, 3));

                
                foreach(FacingHelper::HORIZONTAL as $face){
                        if($rand->nextBoundedInt(3) === 0){
                                $branchLen = 1 + $rand->nextBoundedInt(2); 
                                $bx = $pos->getX();
                                $bz = $pos->getZ();
                                $by = $pos->getY() - 1; 

                                for($i = 0; $i < $branchLen; ++$i){
                                        $bx += FacingHelper::xOffset($face);
                                        $bz += FacingHelper::zOffset($face);
                                        $this->setBlockAndNotifyAdequately($level, new Vector3($bx, $by, $bz), $this->woodMetadata);
                                }
                        }
                }
        }

        
        private function placeLeafAt(ChunkManager $level, Vector3 $pos)
        {
                $id = $level->getBlockIdAt($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ());
                if($id === Block::AIR || $id === Block::LEAVES){
                        $this->setBlockAndNotifyAdequately($level, $pos, $this->leavesMetadata);
                }
        }

        private function placeVine(ChunkManager $level, Random $random, Vector3 $pos, $meta)
        {
                if($random->nextBoundedInt(3) > 0 && $level->getBlockIdAt($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ()) === 0){
                        $this->setBlockAndNotifyAdequately($level, $pos, Block::get(Block::VINE, $meta));
                }
        }

}
