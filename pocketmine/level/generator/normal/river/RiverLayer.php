<?php
/*    
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█    
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░    
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.1
 *               InfinityProject By @Dr1xyDev    
 *   YT:         @Dr1xyDev    
 *   GitHub:     github.com/Dr1xyDev/Infinity    
*/

namespace pocketmine\level\generator\normal\river;

use pocketmine\level\generator\biome\Biome;

class RiverLayer{

        
        public static function compute(Biome $baseBiome, array $maskData) : array{
                $originalBiomeId = $baseBiome->getId();
                $isRiver = $maskData['isRiver'];
                $isBankZone = $maskData['isBankZone'];

                
                
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
                        
                        $maskValue = $maskData['bankIntensity'] * 0.3; 
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
