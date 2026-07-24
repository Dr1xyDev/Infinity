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

use pocketmine\level\generator\noise\Simplex;
use pocketmine\utils\Random;

class RiverNoise{

        /** @var Simplex Macro-scale trajectory noise */
        private $macroNoise;
        /** @var Simplex Medium-scale curve noise */
        private $mediumNoise;
        /** @var Simplex Micro-scale variation noise */
        private $microNoise;
        /** @var Simplex Width variation noise */
        private $widthNoise;
        /** @var Simplex Depth variation noise */
        private $depthNoise;
        /** @var Simplex Bank profile noise */
        private $bankNoise;

        /** @var int World seed */
        private $worldSeed;

        // Unique salts for each noise layer - ensures independent permutation tables
        const SALT_MACRO    = 0x5DEECE66D;
        const SALT_MEDIUM   = 0x123456789;
        const SALT_MICRO    = 0x9ABCDEF01;
        const SALT_WIDTH    = 0x2468ACF13;
        const SALT_DEPTH    = 0x369CDEF25;
        const SALT_BANK     = 0x48E012F37;

        // Path noise combination weights
        // Macro dominates trajectory, medium adds curves, micro adds texture
        const WEIGHT_MACRO  = 1.0;
        const WEIGHT_MEDIUM = 0.30;
        const WEIGHT_MICRO  = 0.05;

        /**
         * Minimum gradient magnitude to prevent division by zero
         * in distance estimation. Rivers never become infinitely wide.
         */
        const MIN_GRADIENT = 0.001;

        /**
         * Gradient sampling offset. We evaluate noise at (x +/- offset, z +/- offset)
         * to estimate the local gradient. Small offset = more accurate gradient.
         */
        const GRADIENT_OFFSET = 0.5;

        public function __construct(int $worldSeed){
                $this->worldSeed = $worldSeed;

                // Macro: very low frequency for long river paths across hundreds of blocks.
                // MODIFIED: Increased from 1/768 to 1/1280 for even MORE space between rivers
                // leaving much MUCH wider stretches of uninterrupted land for vast biome terrain
                // 4 octaves for detail, persistence 1/4 for gradual falloff
                $this->macroNoise = $this->createSimplex($worldSeed, self::SALT_MACRO, 4, 1 / 4, 1 / 1280);

                // Medium: medium frequency for natural curves, stretched proportionally
                // MODIFIED: Updated from 1/256 to 1/410 to match the larger river spacing
                // so curves stay visually consistent relative to the now-wider river spacing.
                // 2 octaves, persistence 1/2 for moderate detail
                $this->mediumNoise = $this->createSimplex($worldSeed, self::SALT_MEDIUM, 2, 1 / 2, 1 / 410);

                // Micro: higher frequency for small irregularities, stretched in the
                // same proportion (1/32 -> 1/64).
                // 2 octaves, persistence 1/2
                $this->microNoise = $this->createSimplex($worldSeed, self::SALT_MICRO, 2, 1 / 2, 1 / 64);

                // Width: controls river width variation (1/64 frequency)
                // 3 octaves for smooth width changes along the path
                $this->widthNoise = $this->createSimplex($worldSeed, self::SALT_WIDTH, 3, 1 / 2, 1 / 64);

                // Depth: controls river depth variation (1/64 frequency)
                // 3 octaves, correlates with width but different seed
                $this->depthNoise = $this->createSimplex($worldSeed, self::SALT_DEPTH, 3, 1 / 2, 1 / 64);

                // Bank: controls bank steepness (1/32 frequency)
                // 2 octaves for local bank variations
                $this->bankNoise = $this->createSimplex($worldSeed, self::SALT_BANK, 2, 1 / 2, 1 / 32);
        }

        private function createSimplex(int $worldSeed, int $salt, int $octaves, float $persistence, float $expansion) : Simplex{
                $layerSeed = $worldSeed ^ $salt;
                $random = new Random($layerSeed);
                return new Simplex($random, $octaves, $persistence, $expansion);
        }

        /**
         * Computes the combined river path noise value at a coordinate.
         * This is the value whose zero contour defines the river center line.
         * Where pathValue ~= 0, a river center exists.
         *
         * @param int $x World X
         * @param int $z World Z
         * @return float Combined path noise value
         */
        public function getMainPathValue(int $x, int $z) : float{
                return $this->macroNoise->noise2D($x, $z, true) * self::WEIGHT_MACRO
                     + $this->mediumNoise->noise2D($x, $z, true) * self::WEIGHT_MEDIUM
                     + $this->microNoise->noise2D($x, $z, true) * self::WEIGHT_MICRO;
        }

        /**
         * Computes the estimated DISTANCE from a point to the nearest
         * river center line, in world blocks.
         *
         * This is the KEY innovation of the redesigned system.
         * Instead of abs(noise) < threshold, we compute:
         *   distance = abs(pathValue) / gradientMagnitude
         *
         * The gradient magnitude is estimated numerically by evaluating
         * the path noise at 4 neighboring points.
         *
         * This gives us a proper distance in BLOCKS, allowing us to
         * specify river width directly in blocks instead of through
         * opaque noise thresholds.
         *
         * @param int $x World X
         * @param int $z World Z
         * @return float Estimated distance from river center in blocks
         */
        public function getEstimatedDistance(int $x, int $z) : float{
                $pathValue = $this->getMainPathValue($x, $z);

                // Estimate gradient using numerical differentiation
                // Evaluate path noise at offset points to get gradient direction
                $off = self::GRADIENT_OFFSET;

                $pathXPlus  = $this->getMainPathValueRaw($x + $off, $z);
                $pathXMinus = $this->getMainPathValueRaw($x - $off, $z);
                $pathZPlus  = $this->getMainPathValueRaw($x, $z + $off);
                $pathZMinus = $this->getMainPathValueRaw($x, $z - $off);

                $gradX = ($pathXPlus - $pathXMinus) / (2.0 * $off);
                $gradZ = ($pathZPlus - $pathZMinus) / (2.0 * $off);

                $gradientMag = sqrt($gradX * $gradX + $gradZ * $gradZ);

                // Prevent division by zero / excessively wide rivers at gradient nulls
                $gradientMag = max($gradientMag, self::MIN_GRADIENT);

                // Distance = abs(pathValue) / gradientMagnitude
                // This converts the noise value to a real-world distance estimate
                return abs($pathValue) / $gradientMag;
        }

        /**
         * Raw path value computation for gradient estimation.
         * Uses float coordinates for accurate gradient sampling.
         *
         * @param float $x World X (can be fractional for gradient sampling)
         * @param float $z World Z (can be fractional)
         * @return float Combined path noise value
         */
        private function getMainPathValueRaw(float $x, float $z) : float{
                return $this->macroNoise->noise2D($x, $z, true) * self::WEIGHT_MACRO
                     + $this->mediumNoise->noise2D($x, $z, true) * self::WEIGHT_MEDIUM
                     + $this->microNoise->noise2D($x, $z, true) * self::WEIGHT_MICRO;
        }

        /**
         * Computes all 6 noise values for a coordinate.
         *
         * @param int $x World X
         * @param int $z World Z
         * @return array All noise layer values
         */
        public function compute(int $x, int $z) : array{
                return [
                        'macro'  => $this->macroNoise->noise2D($x, $z, true),
                        'medium' => $this->mediumNoise->noise2D($x, $z, true),
                        'micro'  => $this->microNoise->noise2D($x, $z, true),
                        'width'  => $this->widthNoise->noise2D($x, $z, true),
                        'depth'  => $this->depthNoise->noise2D($x, $z, true),
                        'bank'   => $this->bankNoise->noise2D($x, $z, true),
                ];
        }

        /**
         * Gets width variation noise at a coordinate.
         */
        public function getWidthNoise(int $x, int $z) : float{
                return $this->widthNoise->noise2D($x, $z, true);
        }

        /**
         * Gets depth variation noise at a coordinate.
         */
        public function getDepthNoise(int $x, int $z) : float{
                return $this->depthNoise->noise2D($x, $z, true);
        }

        /**
         * Gets bank profile noise at a coordinate.
         */
        public function getBankNoise(int $x, int $z) : float{
                return $this->bankNoise->noise2D($x, $z, true);
        }

        /**
         * Returns the world seed.
         */
        public function getWorldSeed() : int{
                return $this->worldSeed;
        }
}
