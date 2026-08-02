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

use pocketmine\block\Block;

class RiverCarver{

        
        const MIN_FLOOR_DEPTH = 1;

        
        const MAX_SLOPE = 0.28;

        
        const MAX_TRANSITION_WIDTH = 40.0;

        
        public static function compute(
                float $normalMaxSum,
                float $normalMinSum,
                float $depth,
                array $bankData,
                int $waterHeight,
                float $estimatedDistance,
                float $halfWidth
        ) : array{
                
                
                
                
                $riverMaxSum = (float) $waterHeight; 
                $riverMinSum = $waterHeight - $depth - self::MIN_FLOOR_DEPTH;
                $riverMinSum = max(5.0, $riverMinSum); 

                
                
                
                
                
                
                
                
                $elevationHeadroom = abs($normalMaxSum - $riverMaxSum);
                $adaptiveWidth = $elevationHeadroom / self::MAX_SLOPE;
                $bankZoneWidth = RiverMask::clamp($adaptiveWidth, RiverMask::BANK_ZONE_WIDTH, self::MAX_TRANSITION_WIDTH);

                $totalRadius = $halfWidth + $bankZoneWidth;
                $normalizedPos = $totalRadius > 0.0
                        ? RiverMask::clamp($estimatedDistance / $totalRadius, 0.0, 1.0)
                        : 0.0;

                
                
                
                
                if($normalizedPos >= 1.0){
                        return ['maxSum' => $normalMaxSum, 'minSum' => $normalMinSum];
                }

                $blend = 1.0 - RiverMask::smootherstep(0.0, 1.0, $normalizedPos);

                
                
                
                
                
                $steepnessFactor = $bankData['steepnessFactor'] ?? 0.55;
                $shapeExponent = RiverMask::clamp(1.6 - $steepnessFactor, 0.6, 1.4);
                $blend = pow($blend, $shapeExponent);

                $carvedMaxSum = $normalMaxSum * (1 - $blend) + $riverMaxSum * $blend;
                $carvedMinSum = $normalMinSum * (1 - $blend) + $riverMinSum * $blend;

                return ['maxSum' => $carvedMaxSum, 'minSum' => $carvedMinSum];
        }

        
        public static function getRiverbedBlock(int $y, int $waterHeight, int $originalBiomeId) : int{
                $AIR = 0; $STONE = 1; $GRASS = 2; $DIRT = 3;
                $SAND = 12; $GRAVEL = 13; $SANDSTONE = 24; $CLAY = 82;

                $depthBelowWater = $waterHeight - $y;

                switch($originalBiomeId){
                        case 2: 
                        case 43: 
                                return $depthBelowWater <= 1 ? $SAND : $SANDSTONE;

                        case 5: 
                        case 42: 
                                return $depthBelowWater <= 1 ? $GRAVEL : $STONE;

                        case 6: 
                                if($depthBelowWater <= 1) return $CLAY;
                                if($depthBelowWater <= 2) return $DIRT;
                                return $STONE;

                        case 21: 
                        case 41: 
                                if($depthBelowWater <= 1) return $DIRT;
                                if($depthBelowWater <= 2) return $CLAY;
                                return $STONE;

                        case 3: case 20: 
                                return $depthBelowWater <= 1 ? $GRAVEL : $STONE;

                        case 35: 
                                if($depthBelowWater <= 1) return $SAND;
                                if($depthBelowWater <= 2) return $DIRT;
                                return $SANDSTONE;

                        case 29: 
                        case 44: 
                                if($depthBelowWater <= 1) return $DIRT;
                                if($depthBelowWater <= 2) return $GRAVEL;
                                return $STONE;

                        case 4: case 27: 
                        case 46: 
                                if($depthBelowWater <= 1) return $DIRT;
                                if($depthBelowWater <= 2) return $GRAVEL;
                                return $DIRT;

                        case 12: 
                        case 45: 
                                return $depthBelowWater <= 1 ? $GRAVEL : $STONE;

                        case 40: 
                                if($depthBelowWater <= 1) return $GRAVEL;
                                if($depthBelowWater <= 2) return $DIRT;
                                return $SAND;

                        default: 
                                if($depthBelowWater <= 1) return $GRAVEL;
                                if($depthBelowWater <= 2) return $DIRT;
                                return $SAND;
                }
        }

        
        public static function getBankBlock(int $y, int $waterHeight, int $originalBiomeId, float $bankIntensity) : int{
                $STONE = 1; $DIRT = 3; $SAND = 12; $GRAVEL = 13; $SANDSTONE = 24;

                $distToWater = abs($y - $waterHeight);

                
                if($distToWater <= 3 && $bankIntensity > 0.3){
                        switch($originalBiomeId){
                                case 2: case 35: 
                                case 43: 
                                        return $distToWater <= 1 ? $SAND : $SANDSTONE;
                                case 5: 
                                case 42: 
                                case 12: 
                                case 45: 
                                        return $GRAVEL;
                                case 3: case 20: 
                                        return $GRAVEL;
                                default:
                                        return $distToWater <= 1 ? $SAND : $DIRT;
                        }
                }

                
                return $STONE;
        }
}
