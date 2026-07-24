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

class RiverMask{

        /**
         * Bank transition zone width in blocks beyond the river edge.
         * Beach-like transition: wide gentle slope from terrain to water level.
         * Similar to how ocean transitions work in the biome blend system.
         */
        const BANK_ZONE_WIDTH = 8.0;

        /**
         * Minimum mask intensity to be considered "in river".
         * Very low values at the outermost smooth edge are not
         * counted as river to prevent thin, barely-visible water.
         */
        const RIVER_MIN_INTENSITY = 0.08;

        /**
         * Computes the river mask for a position using distance-field approach.
         *
         * @param float $estimatedDistance Distance from river center in blocks (from RiverNoise)
         * @param float $targetWidth       Target river width in blocks (from RiverWidthGenerator)
         * @param float $bankNoise         Bank noise value for edge smoothing
         * @return array Mask data with isRiver, intensity, bank info
         */
        public static function compute(float $estimatedDistance, float $targetWidth, float $bankNoise) : array{
                $halfWidth = $targetWidth / 2.0;

                // River zone: distance < halfWidth
                // Bank zone: distance < halfWidth + BANK_ZONE_WIDTH
                $bankOuterEdge = $halfWidth + self::BANK_ZONE_WIDTH;

                // Compute mask intensity within the river
                // At center (distance=0): intensity=1.0
                // At edge (distance=halfWidth): intensity=0.0
                // Beyond edge: intensity=0
                $riverIntensity = 0.0;
                $isRiver = false;

                if($halfWidth > 0.0 && $estimatedDistance < $halfWidth){
                        // Inside the river channel
                        // Use quintic smoothstep for the smoothest possible transition
                        $normalizedDist = $estimatedDistance / $halfWidth; // 0 at center, 1 at edge
                        $riverIntensity = 1.0 - self::smootherstep(0.0, 1.0, $normalizedDist);
                        $isRiver = $riverIntensity > self::RIVER_MIN_INTENSITY;
                }

                // Bank zone intensity (terrain modification outside the river)
                // This creates the gradual descent from surrounding terrain to river level
                $bankIntensity = 0.0;
                $isBankZone = false;

                if($estimatedDistance >= $halfWidth && $estimatedDistance < $bankOuterEdge){
                        // In the bank transition zone
                        $bankNormalizedDist = ($estimatedDistance - $halfWidth) / self::BANK_ZONE_WIDTH;
                        $bankIntensity = 1.0 - self::smootherstep(0.0, 1.0, $bankNormalizedDist);
                        $isBankZone = $bankIntensity > 0.01;
                }

                // Bank noise modulates the bank zone width
                // Positive bank noise = wider, more gradual banks
                // Negative bank noise = narrower, steeper banks
                $bankModulation = ($bankNoise + 1.0) * 0.5; // 0 to 1
                $effectiveBankZoneWidth = self::BANK_ZONE_WIDTH * (0.5 + $bankModulation);

                // Edge distance from river center (how far past the half-width)
                $edgeDistance = max(0, $estimatedDistance - $halfWidth);

                // Cross-section position: 0 = center, 1 = edge, >1 = bank/outside
                // (halfWidth can be 0 for biomes where rivers are suppressed
                // entirely, e.g. Mountains - guard against div-by-zero)
                $crossSectionPos = $halfWidth > 0.0 ? $estimatedDistance / $halfWidth : ($estimatedDistance > 0.0 ? INF : 0.0);

                return [
                        'isRiver'              => $isRiver,
                        'riverIntensity'       => $riverIntensity,
                        'isBankZone'           => $isBankZone,
                        'bankIntensity'        => $bankIntensity,
                        'estimatedDistance'     => $estimatedDistance,
                        'targetWidth'          => $targetWidth,
                        'halfWidth'            => $halfWidth,
                        'edgeDistance'          => $edgeDistance,
                        'crossSectionPos'      => $crossSectionPos,
                        'effectiveBankZoneWidth' => $effectiveBankZoneWidth,
                        'bankNoise'            => $bankNoise,
                ];
        }

        /**
         * Quintic smoothstep (smootherstep) for the smoothest possible transition.
         * Has zero 1st and 2nd derivatives at both edges.
         */
        public static function smootherstep(float $edge0, float $edge1, float $x) : float{
                if($edge0 >= $edge1){
                        return $x >= $edge1 ? 1.0 : 0.0;
                }
                $t = self::clamp(($x - $edge0) / ($edge1 - $edge0), 0.0, 1.0);
                return $t * $t * $t * ($t * ($t * 6.0 - 15.0) + 10.0);
        }

        /**
         * Cubic smoothstep for moderate smoothness.
         */
        public static function smoothstep(float $edge0, float $edge1, float $x) : float{
                if($edge0 >= $edge1){
                        return $x >= $edge1 ? 1.0 : 0.0;
                }
                $t = self::clamp(($x - $edge0) / ($edge1 - $edge0), 0.0, 1.0);
                return $t * $t * (3.0 - 2.0 * $t);
        }

        public static function clamp(float $value, float $min, float $max) : float{
                return max($min, min($max, $value));
        }
}
