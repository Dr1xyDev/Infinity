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

class RiverDepthGenerator{

        const MIN_DEPTH = 1;
        const MAX_DEPTH = 3;

        /**
         * Width-to-depth correlation scale.
         * Beach-like: shallower correlation (wider ≠ much deeper).
         */
        const WIDTH_DEPTH_SCALE = 0.25;

        /**
         * Computes river depth at a position.
         *
         * @param RiverNoise $riverNoise  Noise system
         * @param int        $x           World X
         * @param int        $z           World Z
         * @param float      $width       River width in blocks
         * @param float      $riverIntensity Mask intensity (1=center, 0=edge)
         * @param int        $sizeCategory River size category
         * @return float River depth in blocks below waterHeight (1-6)
         */
        public static function compute(RiverNoise $riverNoise, int $x, int $z, float $width, float $riverIntensity, int $sizeCategory) : float{
                // Base depth from width correlation
                // Stream (3-4 wide) => 1-2 deep
                // Normal (7-10 wide) => 2-4 deep
                // Wide (11-14 wide) => 4-6 deep
                $baseDepth = $width * self::WIDTH_DEPTH_SCALE;
                $baseDepth = max(1.0, $baseDepth); // Minimum 1 block depth

                // Depth variation from noise (slow changes along path)
                $depthNoise = $riverNoise->getDepthNoise($x, $z);
                $depthVariation = $depthNoise * 0.8; // +/- 0.8 blocks

                // Combine base + variation
                $depth = $baseDepth + $depthVariation;

                // At river edges (low intensity), depth should be shallower
                // This creates natural bank slopes
                if($riverIntensity < 0.5){
                        $edgeFactor = $riverIntensity * 1.5 + 0.25;
                        $depth *= $edgeFactor;
                }

                // Clamp to valid range
                $depth = RiverMask::clamp($depth, self::MIN_DEPTH, self::MAX_DEPTH);

                return $depth;
        }

        /**
         * Computes riverbed irregularity at a position.
         * Small depth variations to prevent flat riverbeds.
         *
         * @param array $noiseData Noise values from RiverNoise
         * @return float Irregularity in blocks (-1.5 to 1.5)
         */
        public static function getBedIrregularity(array $noiseData) : float{
                $irregularity = $noiseData['micro'] * 0.8;
                return RiverMask::clamp($irregularity, -1.5, 1.5);
        }
}
