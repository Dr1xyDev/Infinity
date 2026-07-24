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

class RiverWidthGenerator{

        const MIN_WIDTH = 3;
        const MAX_WIDTH = 16;

        /**
         * Computes river width at a position.
         *
         * Width is computed from:
         * 1. Path intensity: how strong the river signal is at this point
         *    (determines size category: stream, small, normal, wide)
         * 2. Width noise: slow variation along the path
         * 3. Merge factor: wider at confluences
         * 4. Origin biome: mountain streams start narrow
         *
         * @param RiverNoise $riverNoise The noise system
         * @param int        $x          World X
         * @param int        $z          World Z
         * @param float      $pathValue  The combined path noise value
         * @param int        $originalBiomeId Original biome
         * @return float River width in blocks (3-16)
         */
        public static function compute(RiverNoise $riverNoise, int $x, int $z, float $pathValue, int $originalBiomeId) : float{
                // Rivers only belong on low, relatively flat terrain (like plains,
                // forest, taiga, swamp...) - the same way oceans only ever appear
                // in their own elevation band, rivers should never carve through
                // Mountains/SmallMountains terrain, nor through the elevated
                // HighSavanna plateau (it sits 30 blocks above normal Savanna,
                // same problem as Mountains). Returning 0 here means the
                // distance-field mask (RiverMask) will never classify a column in
                // this biome as river/bank, so no carving happens there at all -
                // this is what actually stops the terrain from generating
                // dirt/stone "walls" where a river would otherwise try to cut
                // into a much higher elevation envelope.
                if($originalBiomeId === 3 || $originalBiomeId === 20 || $originalBiomeId === 36){ // MOUNTAINS / SMALL_MOUNTAINS / HIGH_SAVANNA
                        return 0.0;
                }

                // Base width: determined by the primary noise structure
                // Lower abs(pathValue) = closer to river center = potentially wider
                // But the actual width is determined by the noise structure at larger scale

                // The width noise modulates the base width
                $widthNoise = $riverNoise->getWidthNoise($x, $z);

                // Compute base width from the macro noise structure
                // The macro noise determines the overall river size
                // We use a separate evaluation at a slightly offset position
                // to determine if this is a "wide river zone" or "narrow river zone"
                $macroStructure = $riverNoise->getWidthNoise(
                        (int)($x / 64) * 64,
                        (int)($z / 64) * 64
                );

                // Base width selection based on macro structure
                // Normal rivers are the most common
                $baseWidth = 8.0; // Default: normal river (7-10 blocks)

                if($macroStructure > 0.5){
                        // Wide river zone
                        $baseWidth = 12.0;
                }elseif($macroStructure < -0.3){
                        // Stream zone
                        $baseWidth = 4.0;
                }elseif($macroStructure < -0.1){
                        // Small river zone
                        $baseWidth = 6.0;
                }

                // Never generate an enormous river starting in a desert flatland
                // (Mountains/SmallMountains are handled above - they get no river)
                switch($originalBiomeId){
                        case 2:  // DESERT
                                // Rivers in desert are typically normal width
                                // but rarely originate new wide rivers
                                $baseWidth = min($baseWidth, 10.0);
                                break;
                }

                // Width noise variation: slow changes along the path
                // This creates natural width variation (narrowing, widening)
                $widthVariation = $widthNoise * 2.0; // +/- 2 blocks

                // Final width
                $width = $baseWidth + $widthVariation;

                // Clamp to valid range
                $width = RiverMask::clamp($width, self::MIN_WIDTH, self::MAX_WIDTH);

                return $width;
        }
}
