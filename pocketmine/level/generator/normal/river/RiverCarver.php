<?php

/*
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.0
 *               InfinityProject By @Dr1xyDev
 *   YT:         @Dr1xyDev
 *   GitHub:     github.com/Dr1xyDev/Infinity
*/

namespace pocketmine\level\generator\normal\river;

use pocketmine\block\Block;

class RiverCarver{

        /**
         * Minimum river floor depth below terrain surface.
         * Ensures rivers always have some water depth (not just flat surface).
         */
        const MIN_FLOOR_DEPTH = 1;

        /**
         * Maximum allowed slope of the bank transition, in vertical blocks
         * per horizontal block. 0.28 means roughly 1 block of descent for
         * every ~3.5 blocks travelled - a gentle, walkable hillside instead
         * of a cliff. Lower = gentler/wider transition.
         */
        const MAX_SLOPE = 0.28;

        /**
         * Hard cap on how far the adaptive transition is allowed to reach,
         * even next to very tall terrain. Prevents rivers from having an
         * enormous zone of influence in extreme cases.
         */
        const MAX_TRANSITION_WIDTH = 40.0;

        /**
         * Computes carved terrain elevation for a river column.
         *
         * BUGFIX HISTORY:
         * v1: river zone and bank zone had two DIFFERENT formulas that
         *     disagreed at their shared boundary -> a hard vertical step
         *     (the original "muralla" bug).
         * v2 (this version): even after unifying into one continuous
         *     formula, the transition distance was a FIXED 8 blocks
         *     (RiverMask::BANK_ZONE_WIDTH). That's fine for flat plains,
         *     but next to hills/forest edges where normal terrain can sit
         *     15-25 blocks above water, dropping that much in only 8
         *     blocks is still a near-vertical slope - it LOOKS like a
         *     wall even though there's technically no seam anymore.
         *
         * FIX: the transition width is now ADAPTIVE - it's derived from
         * how much elevation actually needs to be shed at this column
         * (|normalMaxSum - waterHeight|) divided by a maximum allowed
         * slope. Taller surrounding terrain automatically gets a wider,
         * gentler descent; low flat terrain keeps the tight beach-like
         * transition. The result is always continuous (still one formula,
         * clamped to exactly 0 beyond its own radius), so no seam can
         * reappear - it's just now shaped correctly for the terrain
         * around it.
         *
         * @param float $normalMaxSum      Normal terrain maxSum (from biome blend, ALREADY includes neighbor smoothing)
         * @param float $normalMinSum      Normal terrain minSum (from biome blend)
         * @param float $depth             River depth in blocks below waterHeight (1-3)
         * @param array $bankData          Bank profile from RiverBankGenerator
         * @param int   $waterHeight       World water height
         * @param float $estimatedDistance Distance (blocks) from the river center line
         * @param float $halfWidth         Half the river width at this position (blocks)
         * @return array [maxSum, minSum] Modified elevation values
         */
        public static function compute(
                float $normalMaxSum,
                float $normalMinSum,
                float $depth,
                array $bankData,
                int $waterHeight,
                float $estimatedDistance,
                float $halfWidth
        ) : array{
                // River channel target elevation
                // BEACH-LIKE: terrain surface AT waterHeight (not deeply carved below)
                // This means water naturally fills the shallow channel on top
                // The riverbed (minSum) is only depth blocks below the surface
                $riverMaxSum = (float) $waterHeight; // Terrain surface at sea level
                $riverMinSum = $waterHeight - $depth - self::MIN_FLOOR_DEPTH;
                $riverMinSum = max(5.0, $riverMinSum); // Don't go too deep

                // ------------------------------------------------------------
                // ADAPTIVE transition width: the higher the surrounding
                // terrain sits above the river, the wider (gentler) the
                // slope needs to be to avoid looking like a wall.
                // MAX_SLOPE caps how many vertical blocks are allowed per
                // horizontal block of transition (0.28 ~= 1 block up for
                // every ~3.5 blocks out - a gentle hillside, not a cliff).
                // ------------------------------------------------------------
                $elevationHeadroom = abs($normalMaxSum - $riverMaxSum);
                $adaptiveWidth = $elevationHeadroom / self::MAX_SLOPE;
                $bankZoneWidth = RiverMask::clamp($adaptiveWidth, RiverMask::BANK_ZONE_WIDTH, self::MAX_TRANSITION_WIDTH);

                $totalRadius = $halfWidth + $bankZoneWidth;
                $normalizedPos = $totalRadius > 0.0
                        ? RiverMask::clamp($estimatedDistance / $totalRadius, 0.0, 1.0)
                        : 0.0;

                // Fully outside the (adaptive) influence radius: untouched terrain.
                // This is an EXACT match with the blend formula's own 0 at
                // normalizedPos=1, so returning early here changes nothing -
                // it's just a fast path, not a second formula.
                if($normalizedPos >= 1.0){
                        return ['maxSum' => $normalMaxSum, 'minSum' => $normalMinSum];
                }

                $blend = 1.0 - RiverMask::smootherstep(0.0, 1.0, $normalizedPos);

                // Biome-specific descent shape (mountains dip faster near the
                // river, swamps stay gentle longer). This only reshapes the
                // curve BETWEEN 0 and 1 - the endpoints never move, so
                // continuity with untouched terrain and with the river
                // center is always guaranteed.
                $steepnessFactor = $bankData['steepnessFactor'] ?? 0.55;
                $shapeExponent = RiverMask::clamp(1.6 - $steepnessFactor, 0.6, 1.4);
                $blend = pow($blend, $shapeExponent);

                $carvedMaxSum = $normalMaxSum * (1 - $blend) + $riverMaxSum * $blend;
                $carvedMinSum = $normalMinSum * (1 - $blend) + $riverMinSum * $blend;

                return ['maxSum' => $carvedMaxSum, 'minSum' => $carvedMinSum];
        }


        /**
         * Computes riverbed material based on biome and depth.
         * Riverbeds mix gravel, dirt, sand, clay, and stone naturally.
         *
         * @param int $y              Current Y level
         * @param int $waterHeight    Water surface level
         * @param int $originalBiomeId Original biome
         * @return int Block ID for riverbed at this position
         */
        public static function getRiverbedBlock(int $y, int $waterHeight, int $originalBiomeId) : int{
                $AIR = 0; $STONE = 1; $GRASS = 2; $DIRT = 3;
                $SAND = 12; $GRAVEL = 13; $SANDSTONE = 24; $CLAY = 82;

                $depthBelowWater = $waterHeight - $y;

                switch($originalBiomeId){
                        case 2: // DESERT
                        case 43: // DESERT_PLAINS
                                return $depthBelowWater <= 1 ? $SAND : $SANDSTONE;

                        case 5: // TAIGA
                        case 42: // TAIGA_PLAINS
                                return $depthBelowWater <= 1 ? $GRAVEL : $STONE;

                        case 6: // SWAMP
                                if($depthBelowWater <= 1) return $CLAY;
                                if($depthBelowWater <= 2) return $DIRT;
                                return $STONE;

                        case 21: // JUNGLE
                        case 41: // JUNGLE_PLAINS
                                if($depthBelowWater <= 1) return $DIRT;
                                if($depthBelowWater <= 2) return $CLAY;
                                return $STONE;

                        case 3: case 20: // MOUNTAINS / SMALL_MOUNTAINS
                                return $depthBelowWater <= 1 ? $GRAVEL : $STONE;

                        case 35: // SAVANNA
                                if($depthBelowWater <= 1) return $SAND;
                                if($depthBelowWater <= 2) return $DIRT;
                                return $SANDSTONE;

                        case 29: // ROOFED_FOREST
                        case 44: // ROOFED_PLAINS
                                if($depthBelowWater <= 1) return $DIRT;
                                if($depthBelowWater <= 2) return $GRAVEL;
                                return $STONE;

                        case 4: case 27: // FOREST / BIRCH_FOREST
                        case 46: // BIRCH_PLAINS
                                if($depthBelowWater <= 1) return $DIRT;
                                if($depthBelowWater <= 2) return $GRAVEL;
                                return $DIRT;

                        case 12: // ICE_PLAINS
                        case 45: // SNOW_PLAINS
                                return $depthBelowWater <= 1 ? $GRAVEL : $STONE;

                        case 40: // OAK_PLAINS
                                if($depthBelowWater <= 1) return $GRAVEL;
                                if($depthBelowWater <= 2) return $DIRT;
                                return $SAND;

                        default: // PLAINS and others
                                if($depthBelowWater <= 1) return $GRAVEL;
                                if($depthBelowWater <= 2) return $DIRT;
                                return $SAND;
                }
        }

        /**
         * Computes bank zone surface block for Beach-like transitions.
         * Bank zones get sand/gravel on the surface near the river,
         * creating a Beach-like transition like how ocean meets land.
         *
         * @param int $y              Current Y level
         * @param int $waterHeight    Water surface level
         * @param int $originalBiomeId Original biome
         * @param float $bankIntensity How close to river (1=near, 0=far)
         * @return int Block ID for bank surface at this position
         */
        public static function getBankBlock(int $y, int $waterHeight, int $originalBiomeId, float $bankIntensity) : int{
                $STONE = 1; $DIRT = 3; $SAND = 12; $GRAVEL = 13; $SANDSTONE = 24;

                $distToWater = abs($y - $waterHeight);

                // Near water surface AND near river: Beach-like sand/gravel
                if($distToWater <= 3 && $bankIntensity > 0.3){
                        switch($originalBiomeId){
                                case 2: case 35: // DESERT / SAVANNA
                                case 43: // DESERT_PLAINS
                                        return $distToWater <= 1 ? $SAND : $SANDSTONE;
                                case 5: // TAIGA
                                case 42: // TAIGA_PLAINS
                                case 12: // ICE_PLAINS
                                case 45: // SNOW_PLAINS
                                        return $GRAVEL;
                                case 3: case 20: // MOUNTAINS
                                        return $GRAVEL;
                                default:
                                        return $distToWater <= 1 ? $SAND : $DIRT;
                        }
                }

                // Farther from water or river: normal stone (GroundCover will overlay)
                return $STONE;
        }
}
