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
use pocketmine\level\ChunkManager;
use pocketmine\math\Vector3;
use pocketmine\utils\Random;

abstract class CustomTree extends TreeObject{

        /**
         * Checks if a block type can be overridden when placing tree parts.
         *
         * @param Block $block The block to check
         * @return bool True if the block can be overridden
         */
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

        /**
         * Sets a block at the given position in the world.
         *
         * @param ChunkManager $level The world/level
         * @param Vector3 $pos The position to set the block at
         * @param Block $block The block to place
         */
        public function setBlockAndNotifyAdequately(ChunkManager $level, Vector3 $pos, Block $block){
                $level->setBlockIdAt($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ(), $block->getId());
                $level->setBlockDataAt($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ(), $block->getDamage());
        }

        /**
         * Generates the tree at the given position.
         *
         * @param ChunkManager $worldIn The world
         * @param Random $rand Random number generator
         * @param Vector3 $position The base position
         * @return bool Whether the tree was successfully generated
         */
        abstract public function generate(ChunkManager $worldIn, Random $rand, Vector3 $position) : bool;
}
