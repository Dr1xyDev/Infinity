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
use pocketmine\utils\Random;

class SavannaCanyon extends Populator{

        /** @var ChunkManager */
        private $level;

        public function populate(ChunkManager $level, $chunkX, $chunkZ, Random $random){
                $this->level = $level;

                // Very rare: only ~3% of savanna chunks get a canyon
                if($random->nextBoundedInt(33) !== 0){
                        return;
                }

                // Canyon center position (slightly inward to avoid chunk-edge issues)
                $cx = $random->nextRange($chunkX * 16 + 4, $chunkX * 16 + 11);
                $cz = $random->nextRange($chunkZ * 16 + 4, $chunkZ * 16 + 11);

                // Find surface height at canyon center
                $surfaceY = $this->getHighestBlock($cx, $cz);
                if($surfaceY <= 62){
                        return; // Don't carve canyons below water level
                }

                // Canyon dimensions
                $halfWidth = $random->nextRange(3, 5);  // 3-5 half-width → 7-11 total width
                $depth = $random->nextRange(8, 14);      // 8-14 blocks deep
                $length = $random->nextRange(8, 12);     // 8-12 blocks long

                // Direction: N-S or E-W (random)
                $isEastWest = $random->nextBoolean();

                // Second perpendicular short extension (creates L or T shape)
                $branchLength = $random->nextRange(3, 6);
                $branchStart = $random->nextRange(2, $length - 2);

                // Dig main canyon trench
                $this->digTrench($cx, $cz, $surfaceY, $halfWidth, $depth, $length, $isEastWest);

                // Dig branch (perpendicular direction, shorter)
                $branchCx = $cx + ($isEastWest ? 0 : $branchStart);
                $branchCz = $cz + ($isEastWest ? $branchStart : 0);
                $this->digTrench($branchCx, $branchCz, $surfaceY, $halfWidth - 1, $depth - 2, $branchLength, !$isEastWest);
        }

        /**
         * Digs a trench in the terrain for the canyon.
         * The trench has a V-profile: deeper at center, shallower at edges.
         * Walls are sandstone, bottom is sand, interior is air.
         *
         * @param int $cx Center X
         * @param int $cz Center Z
         * @param int $surfaceY Surface height
         * @param int $halfWidth Half-width of the canyon
         * @param int $depth Depth of the canyon
         * @param int $length Length of the canyon
         * @param bool $isEastWest Direction: true = E-W, false = N-S
         */
        private function digTrench($cx, $cz, $surfaceY, $halfWidth, $depth, $length, $isEastWest){
                for($i = 0; $i < $length; ++$i){
                        // Progress along the trench direction
                        $bx = $cx + ($isEastWest ? $i : 0);
                        $bz = $cz + ($isEastWest ? 0 : $i);

                        // Width slightly varies along length (wider at center, narrower at ends)
                        $posRatio = $i / max(1, $length - 1);
                        $widthFactor = 1.0 - abs($posRatio - 0.5) * 0.4; // 0.8-1.0 range
                        $localHalfWidth = max(2, (int)($halfWidth * $widthFactor));

                        // Depth also varies: deepest at center, shallower at ends
                        $depthFactor = 1.0 - abs($posRatio - 0.5) * 0.3;
                        $localDepth = max(4, (int)($depth * $depthFactor));

                        // Dig across the width (perpendicular to trench direction)
                        for($w = -$localHalfWidth; $w <= $localHalfWidth; ++$w){
                                $wx = $bx + ($isEastWest ? 0 : $w);
                                $wz = $bz + ($isEastWest ? $w : 0);

                                // V-profile: deeper at center, shallower at edges
                                $edgeRatio = abs($w) / max(1, $localHalfWidth);
                                $localDepthAtPos = max(2, (int)($localDepth * (1.0 - $edgeRatio * 0.6)));

                                // Carve terrain from surface down to depth
                                for($dy = 0; $dy < $localDepthAtPos; ++$dy){
                                        $by = $surfaceY - $dy;
                                        if($by <= 0) continue;

                                        if($dy === 0){
                                                // Bottom layer: sand (canyon floor)
                                                $this->level->setBlockIdAt($wx, $by, $wz, Block::SAND);
                                                // Also set sand below for thick floor
                                                if($by > 0){
                                                        $this->level->setBlockIdAt($wx, $by - 1, $wz, Block::SANDSTONE);
                                                }
                                        }elseif($edgeRatio > 0.7){
                                                // Edge walls: sandstone (steep canyon walls)
                                                $this->level->setBlockIdAt($wx, $by, $wz, Block::SANDSTONE);
                                        }else{
                                                // Interior: air (open canyon space)
                                                $this->level->setBlockIdAt($wx, $by, $wz, Block::AIR);
                                        }
                                }
                        }
                }
        }

        /**
         * Finds the highest solid block at (x, z).
         * Returns the y coordinate of the surface.
         */
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
