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

class RiverDepthGenerator{

        const MIN_DEPTH = 1;
        const MAX_DEPTH = 3;

        
        const WIDTH_DEPTH_SCALE = 0.25;

        
        public static function compute(RiverNoise $riverNoise, int $x, int $z, float $width, float $riverIntensity, int $sizeCategory) : float{
                
                
                
                
                $baseDepth = $width * self::WIDTH_DEPTH_SCALE;
                $baseDepth = max(1.0, $baseDepth); 

                
                $depthNoise = $riverNoise->getDepthNoise($x, $z);
                $depthVariation = $depthNoise * 0.8; 

                
                $depth = $baseDepth + $depthVariation;

                
                
                if($riverIntensity < 0.5){
                        $edgeFactor = $riverIntensity * 1.5 + 0.25;
                        $depth *= $edgeFactor;
                }

                
                $depth = RiverMask::clamp($depth, self::MIN_DEPTH, self::MAX_DEPTH);

                return $depth;
        }

        
        public static function getBedIrregularity(array $noiseData) : float{
                $irregularity = $noiseData['micro'] * 0.8;
                return RiverMask::clamp($irregularity, -1.5, 1.5);
        }
}
