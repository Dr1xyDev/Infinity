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
use pocketmine\level\ChunkManager;
use pocketmine\math\Vector3;
use pocketmine\utils\Random;

abstract class CustomTree extends TreeObject{

        
        public function canOverride(Block $block) : bool{
                $overridable = [
                        Block::AIR => true,
                        Block::LEAVES => true,
                        Block::SAPLING => true,
                        Block::VINE => true,
                        Block::SNOW_LAYER => true,
                        Block::GRASS => true,
                ];
                return isset($overridable[$block->getId()]);
        }

        
        public function setBlockAndNotifyAdequately(ChunkManager $level, Vector3 $pos, Block $block){
                $level->setBlockIdAt($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ(), $block->getId());
                $level->setBlockDataAt($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ(), $block->getDamage());
        }

        
        abstract public function generate(ChunkManager $worldIn, Random $rand, Vector3 $position) : bool;
}
