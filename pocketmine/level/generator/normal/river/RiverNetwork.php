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

class RiverNetwork{

        
        const STREAM      = 0; 
        const SMALL       = 1; 
        const NORMAL      = 2; 
        const WIDE        = 3; 
        const EXCEPTIONAL = 4; 

        
        const BASE_WIDTHS = [3.5, 5.5, 8.0, 12.0, 15.0];

        
        const WIDTH_AMPLITUDES = [0.5, 0.5, 1.5, 1.5, 0.5];

        
        const ORIGIN_MULTIPLIERS = [
                Biome::MOUNTAINS        => 1.5,  
                Biome::SMALL_MOUNTAINS  => 1.3,  
                Biome::FOREST           => 0.8,
                Biome::BIRCH_FOREST     => 0.8,
                Biome::ROOFED_FOREST    => 0.7,
                Biome::PLAINS           => 0.6,  
                Biome::DESERT           => 0.3,  
                Biome::TAIGA            => 0.7,
                Biome::ICE_PLAINS       => 0.5,
                Biome::SWAMP            => 0.9,  
                Biome::JUNGLE           => 0.8,
                Biome::SAVANNA          => 0.5,
        ];

        
        public static function getSizeCategory(float $width) : int{
                if($width < 5)   return self::STREAM;
                if($width < 7)   return self::SMALL;
                if($width < 11)  return self::NORMAL;
                if($width < 15)  return self::WIDE;
                return self::EXCEPTIONAL;
        }

        
        public static function compute(array $maskData, int $originalBiomeId, float $width) : array{
                $isRiver = $maskData['isRiver'];
                $isBankZone = $maskData['isBankZone'];

                
                $sizeCategory = self::STREAM;
                if($isRiver){
                        $sizeCategory = self::getSizeCategory($width);
                }

                
                $originProbability = 0.6; 
                if(isset(self::ORIGIN_MULTIPLIERS[$originalBiomeId])){
                        $originProbability = self::ORIGIN_MULTIPLIERS[$originalBiomeId];
                }

                
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

                
                
                $isStreamOrigin = false;
                if($isRiver && $sizeCategory === self::STREAM){
                        $distanceFactor = 1.0 - min(1.0, $maskData['estimatedDistance'] / 2.0);
                        $originChance = $originProbability * $distanceFactor + $elevationBonus;
                        $isStreamOrigin = $originChance > 0.5;
                }

                
                
                
                $isMergePoint = false;
                $mergeWidthFactor = 1.0;
                
                
                

                if($width > self::BASE_WIDTHS[$sizeCategory] * 1.3 && $isRiver){
                        $isMergePoint = true;
                        $mergeWidthFactor = 1.15; 
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

        
        public static function getBaseWidth(int $category) : float{
                return self::BASE_WIDTHS[$category];
        }

        
        public static function getWidthAmplitude(int $category) : float{
                return self::WIDTH_AMPLITUDES[$category];
        }
}
