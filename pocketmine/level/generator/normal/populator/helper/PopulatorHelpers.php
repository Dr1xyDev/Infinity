<?php

/*
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.0
 *               InfinityProject By @Dr1xyDev
 *   YT:         @Dr1xyDev
 *   GitHub:     github.com/Dr1xyDev/Infinity
*/

namespace pocketmine\level\generator\normal\populator\helper;

use pocketmine\block\Block;

/**
 * Helper class for populators.
 * Provides utility methods for checking block properties during
 * world generation, used by tree and structure populators.
 */
class PopulatorHelpers{

        /**
         * Checks if a block ID represents a non-solid block.
         * Equivalent to !Block::$solid[$blockId], used by tree
         * generators to determine valid leaf placement positions.
         *
         * @param int $blockId The block ID to check
         * @return bool True if the block is non-solid (air, leaves, etc.)
         */
        public static function isNonSolid(int $blockId) : bool{
                return !isset(Block::$solid[$blockId]) || !Block::$solid[$blockId];
        }
}
