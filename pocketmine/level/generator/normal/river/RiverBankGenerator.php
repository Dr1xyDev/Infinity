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

class RiverBankGenerator{

        /**
         * Maximum bank zone width in blocks beyond the river edge.
         * Beach-like: wide gentle transition from terrain to water level.
         */
        const MAX_BANK_ZONE = 12.0;

        /**
         * Minimum bank zone width. Even steep mountain rivers need some transition.
         */
        const MIN_BANK_ZONE = 4.0;

        /**
         * Computes bank profile for a river position.
         *
         * Beach-like profile: wide gentle slope from terrain to near-waterHeight.
         *
         * @param float $bankNoise   Bank noise value (determines shape variation)
         * @param float $width       River width in blocks
         * @param int   $originalBiomeId Original biome
         * @return array Bank profile data
         */
        public static function compute(float $bankNoise, float $width, int $originalBiomeId) : array{
                // Bank zone width: how far beyond river edge the transition extends
                // Beach-like: wider rivers have wider bank zones
                // Higher bank noise = more gradual (wider) banks
                $bankNoiseFactor = ($bankNoise + 1.0) * 0.5; // 0 to 1
                $baseBankZone = $width * 0.6; // Proportionally wider than river
                $bankZone = $baseBankZone + $bankNoiseFactor * 4.0;
                $bankZone = RiverMask::clamp($bankZone, self::MIN_BANK_ZONE, self::MAX_BANK_ZONE);

                // Biome-specific bank zone adjustments
                switch($originalBiomeId){
                        case 3:  // MOUNTAINS
                                $bankZone *= 0.8; // Slightly narrower but still Beach-like
                                break;
                        case 20: // SMALL_MOUNTAINS
                                $bankZone *= 0.85;
                                break;
                        case 6:  // SWAMP
                                $bankZone *= 1.5; // Very wide floodplain (like real swamps)
                                break;
                        case 2:  // DESERT
                        case 43: // DESERT_PLAINS (desert-like flat sandy plain)
                                $bankZone *= 1.2; // Desert rivers have sandy gradual banks
                                break;
                }

                // Bank shape: Beach-like shapes
                // Most rivers have smooth S-curve (Beach-like)
                // Some have slightly steeper profiles but never ravine-like
                $bankShape = 'beach'; // Default: Beach-like gentle S-curve
                if($bankNoise < -0.6){
                        $bankShape = 'slope'; // Slightly steeper slope (not ravine!)
                }elseif($bankNoise > 0.5){
                        $bankShape = 'floodplain'; // Very flat, wide transition (swamp-like)
                }

                // Steepness factor: controls how much terrain elevation changes
                // Beach-like: gentle, never steep
                // Range 0.4-0.7 (much gentler than old 0.2-0.8)
                // 0.4 = very gradual (swamp floodplain)
                // 0.7 = moderate Beach slope (mountains)
                $steepnessFactor = 0.5 + $bankNoiseFactor * 0.15; // 0.5 to 0.65 base
                switch($originalBiomeId){
                        case 3:  // MOUNTAINS
                                $steepnessFactor = 0.7; // Moderate Beach slope
                                break;
                        case 20: // SMALL_MOUNTAINS
                                $steepnessFactor = 0.65;
                                break;
                        case 6:  // SWAMP
                                $steepnessFactor = 0.4; // Very gradual floodplain
                                break;
                        case 2:  // DESERT
                        case 43: // DESERT_PLAINS (desert-like flat sandy plain)
                                $steepnessFactor = 0.5; // Standard Beach
                                break;
                }
                $steepnessFactor = RiverMask::clamp($steepnessFactor, 0.35, 0.75);

                return [
                        'bankZone'         => $bankZone,
                        'bankShape'        => $bankShape,
                        'steepnessFactor'  => $steepnessFactor,
                        'bankNoise'        => $bankNoise,
                ];
        }

        /**
         * Computes the terrain elevation modification for a bank zone column.
         *
         * Beach-like transition: FULL gentle descent from normal terrain
         * toward river water level. The target is near waterHeight,
         * creating a Beach-like slope visible above water.
         *
         * Result: terrain gently slopes from y=68 (normal) to y=64 (near river)
         * over 8-12 horizontal blocks, then the remaining 2 blocks descend
         * underwater in the river channel (invisible).
         *
         * @param float $normalElevation  Normal terrain elevation (maxSum)
         * @param float $riverElevation   River channel elevation (waterHeight)
         * @param float $bankIntensity    Bank zone intensity (1=near river, 0=far)
         * @param array $bankData         Bank profile data
         * @return float Modified terrain elevation
         */
        public static function computeBankElevation(
                float $normalElevation,
                float $riverElevation,
                float $bankIntensity,
                array $bankData
        ) : float{
                if($bankIntensity <= 0){
                        return $normalElevation;
                }

                $steepnessFactor = $bankData['steepnessFactor'];
                $bankShape = $bankData['bankShape'];

                // Compute blend factor based on bank shape
                $blend = 0.0;

                switch($bankShape){
                        case 'beach':
                                // Beach-like: quintic S-curve, smoothest possible
                                // This creates the gentle Beach transition
                                $blend = RiverMask::smootherstep(0, 1, $bankIntensity);
                                break;

                        case 'slope':
                                // Slightly steeper Beach slope (pow 0.7 instead of 0.5)
                                // Still Beach-like, just more noticeable descent near river
                                $blend = pow($bankIntensity, 0.7);
                                break;

                        case 'floodplain':
                                // Very flat floodplain: almost linear but with slight curve
                                // Like swamp river banks - very wide, very gradual
                                $blend = RiverMask::smoothstep(0, 1, $bankIntensity) * 0.8
                                       + $bankIntensity * 0.2;
                                break;

                        default:
                                $blend = $bankIntensity;
                                break;
                }

                // Apply steepness factor: controls how much of the full descent happens
                $effectiveBlend = $blend * $steepnessFactor;

                // KEY CHANGE: Target is FULL descent to river level (waterHeight)
                // Not a partial descent like the old 40% system.
                // The steepnessFactor controls how MUCH of this full descent
                // actually happens at the inner bank edge.
                //
                // With steepness=0.6 and blend=1.0 (inner edge):
                //   effectiveBlend = 0.6
                //   elevation = normal * 0.4 + river * 0.6
                //   For Plains (68): 68*0.4 + 62*0.6 = 27.2 + 37.2 = 64.4
                //   Terrain at y=64.4 → 2.4 blocks above water → Beach-like!
                //
                // The remaining descent (64.4 → 62) happens in the river
                // channel underwater, invisible to the player.
                $elevation = $normalElevation * (1 - $effectiveBlend) + $riverElevation * $effectiveBlend;

                return $elevation;
        }
}
