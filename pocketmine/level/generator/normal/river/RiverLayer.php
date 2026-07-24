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

class RiverLayer{

        /**
         * Processes a biome column, applying river override where appropriate.
         *
         * @param Biome $baseBiome       Biome selected by BiomeSelector
         * @param array $maskData        Mask data from RiverMask::compute()
         * @return array Layer data including final biome and original biome
         */
        public static function compute(Biome $baseBiome, array $maskData) : array{
                $originalBiomeId = $baseBiome->getId();
                $isRiver = $maskData['isRiver'];
                $isBankZone = $maskData['isBankZone'];

                // Rivers NEVER override Ocean
                // Oceans are terminal points for rivers (rivers flow INTO oceans)
                if($originalBiomeId === Biome::OCEAN){
                        return [
                                'isRiver'          => false,
                                'isBankZone'       => false,
                                'finalBiomeId'     => Biome::OCEAN,
                                'originalBiomeId'  => Biome::OCEAN,
                                'originalBiome'    => $baseBiome,
                                'maskValue'        => 0.0,
                                'riverIntensity'   => 0.0,
                                'bankIntensity'    => 0.0,
                        ];
                }

                $finalBiomeId = $originalBiomeId;
                $maskValue = 0.0;

                if($isRiver){
                        $finalBiomeId = Biome::RIVER;
                        $maskValue = $maskData['riverIntensity'];
                }elseif($isBankZone){
                        // Bank zone: biome stays as original, but terrain is modified
                        $maskValue = $maskData['bankIntensity'] * 0.3; // Reduced influence
                }

                return [
                        'isRiver'          => $isRiver,
                        'isBankZone'       => $isBankZone,
                        'finalBiomeId'     => $finalBiomeId,
                        'originalBiomeId'  => $originalBiomeId,
                        'originalBiome'    => $baseBiome,
                        'maskValue'        => $maskValue,
                        'riverIntensity'   => $maskData['riverIntensity'],
                        'bankIntensity'    => $maskData['bankIntensity'],
                ];
        }
}
