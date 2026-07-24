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

use pocketmine\level\generator\biome\Biome;

class RiverNetwork{

        /** River size categories with width ranges */
        const STREAM      = 0; // 3-4 blocks
        const SMALL       = 1; // 5-6 blocks
        const NORMAL      = 2; // 7-10 blocks
        const WIDE        = 3; // 11-14 blocks
        const EXCEPTIONAL = 4; // 15-16 blocks

        /** Base width for each category */
        const BASE_WIDTHS = [3.5, 5.5, 8.0, 12.0, 15.0];

        /** Width variation amplitude for each category */
        const WIDTH_AMPLITUDES = [0.5, 0.5, 1.5, 1.5, 0.5];

        /** Origin probability multipliers per biome */
        const ORIGIN_MULTIPLIERS = [
                Biome::MOUNTAINS        => 1.5,  // Mountains: many streams originate
                Biome::SMALL_MOUNTAINS  => 1.3,  // Small mountains: moderate streams
                Biome::FOREST           => 0.8,
                Biome::BIRCH_FOREST     => 0.8,
                Biome::ROOFED_FOREST    => 0.7,
                Biome::PLAINS           => 0.6,  // Plains: few new streams, receive from mountains
                Biome::DESERT           => 0.3,  // Desert: rarely originates streams
                Biome::TAIGA            => 0.7,
                Biome::ICE_PLAINS       => 0.5,
                Biome::SWAMP            => 0.9,  // Swamp: many small waterways
                Biome::JUNGLE           => 0.8,
                Biome::SAVANNA          => 0.5,
        ];

        /**
         * Determines river size category from width value.
         *
         * @param float $width River width in blocks
         * @return int Size category (STREAM..EXCEPTIONAL)
         */
        public static function getSizeCategory(float $width) : int{
                if($width < 5)   return self::STREAM;
                if($width < 7)   return self::SMALL;
                if($width < 11)  return self::NORMAL;
                if($width < 15)  return self::WIDE;
                return self::EXCEPTIONAL;
        }

        /**
         * Computes network classification and properties for a position.
         *
         * @param array $maskData        Mask data from RiverMask
         * @param int   $originalBiomeId Original biome before river override
         * @param float $width           Computed river width in blocks
         * @return array Network classification data
         */
        public static function compute(array $maskData, int $originalBiomeId, float $width) : array{
                $isRiver = $maskData['isRiver'];
                $isBankZone = $maskData['isBankZone'];

                // Determine size category
                $sizeCategory = self::STREAM;
                if($isRiver){
                        $sizeCategory = self::getSizeCategory($width);
                }

                // Origin probability for this biome
                $originProbability = 0.6; // Default
                if(isset(self::ORIGIN_MULTIPLIERS[$originalBiomeId])){
                        $originProbability = self::ORIGIN_MULTIPLIERS[$originalBiomeId];
                }

                // Elevation bonus: mountains/hills increase stream probability
                $elevationBonus = 0.0;
                switch($originalBiomeId){
                        case Biome::MOUNTAINS:
                                $elevationBonus = 0.3;
                                break;
                        case Biome::SMALL_MOUNTAINS:
                                $elevationBonus = 0.15;
                                break;
                        case Biome::DESERT:
                                $elevationBonus = -0.2;
                                break;
                }

                // Stream origin: higher elevation + lower distance = more likely stream origin
                // Streams start at river centers in elevated terrain
                $isStreamOrigin = false;
                if($isRiver && $sizeCategory === self::STREAM){
                        $distanceFactor = 1.0 - min(1.0, $maskData['estimatedDistance'] / 2.0);
                        $originChance = $originProbability * $distanceFactor + $elevationBonus;
                        $isStreamOrigin = $originChance > 0.5;
                }

                // Merge detection: where two river paths cross
                // This is detected by checking if the distance field has local minimum
                // that is significantly wider than expected
                $isMergePoint = false;
                $mergeWidthFactor = 1.0;
                // Merge points are detected during width computation
                // If width is significantly above the base for this category,
                // it's likely a merge point

                if($width > self::BASE_WIDTHS[$sizeCategory] * 1.3 && $isRiver){
                        $isMergePoint = true;
                        $mergeWidthFactor = 1.15; // 15% wider at merge
                }

                return [
                        'isRiver'           => $isRiver,
                        'isBankZone'        => $isBankZone,
                        'sizeCategory'      => $sizeCategory,
                        'originProbability' => $originProbability,
                        'elevationBonus'    => $elevationBonus,
                        'isStreamOrigin'    => $isStreamOrigin,
                        'isMergePoint'      => $isMergePoint,
                        'mergeWidthFactor'  => $mergeWidthFactor,
                        'width'             => $width,
                ];
        }

        /**
         * Gets the base width for a size category.
         */
        public static function getBaseWidth(int $category) : float{
                return self::BASE_WIDTHS[$category];
        }

        /**
         * Gets the width variation amplitude for a size category.
         */
        public static function getWidthAmplitude(int $category) : float{
                return self::WIDTH_AMPLITUDES[$category];
        }
}
