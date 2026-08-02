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

class RiverMask{

        
        const BANK_ZONE_WIDTH = 8.0;

        
        const RIVER_MIN_INTENSITY = 0.08;

        
        public static function compute(float $estimatedDistance, float $targetWidth, float $bankNoise) : array{
                $halfWidth = $targetWidth / 2.0;

                
                
                $bankOuterEdge = $halfWidth + self::BANK_ZONE_WIDTH;

                
                
                
                
                $riverIntensity = 0.0;
                $isRiver = false;

                if($halfWidth > 0.0 && $estimatedDistance < $halfWidth){
                        
                        
                        $normalizedDist = $estimatedDistance / $halfWidth; 
                        $riverIntensity = 1.0 - self::smootherstep(0.0, 1.0, $normalizedDist);
                        $isRiver = $riverIntensity > self::RIVER_MIN_INTENSITY;
                }

                
                
                $bankIntensity = 0.0;
                $isBankZone = false;

                if($estimatedDistance >= $halfWidth && $estimatedDistance < $bankOuterEdge){
                        
                        $bankNormalizedDist = ($estimatedDistance - $halfWidth) / self::BANK_ZONE_WIDTH;
                        $bankIntensity = 1.0 - self::smootherstep(0.0, 1.0, $bankNormalizedDist);
                        $isBankZone = $bankIntensity > 0.01;
                }

                
                
                
                $bankModulation = ($bankNoise + 1.0) * 0.5; 
                $effectiveBankZoneWidth = self::BANK_ZONE_WIDTH * (0.5 + $bankModulation);

                
                $edgeDistance = max(0, $estimatedDistance - $halfWidth);

                
                
                
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

        
        public static function smootherstep(float $edge0, float $edge1, float $x) : float{
                if($edge0 >= $edge1){
                        return $x >= $edge1 ? 1.0 : 0.0;
                }
                $t = self::clamp(($x - $edge0) / ($edge1 - $edge0), 0.0, 1.0);
                return $t * $t * $t * ($t * ($t * 6.0 - 15.0) + 10.0);
        }

        
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
