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

class RiverWidthGenerator{

        const MIN_WIDTH = 3;
        const MAX_WIDTH = 16;

        
        public static function compute(RiverNoise $riverNoise, int $x, int $z, float $pathValue, int $originalBiomeId) : float{
                
                
                
                
                
                
                
                
                
                
                
                if($originalBiomeId === 3 || $originalBiomeId === 20 || $originalBiomeId === 36){ 
                        return 0.0;
                }

                
                
                

                
                $widthNoise = $riverNoise->getWidthNoise($x, $z);

                
                
                
                
                $macroStructure = $riverNoise->getWidthNoise(
                        (int)($x / 64) * 64,
                        (int)($z / 64) * 64
                );

                
                
                $baseWidth = 8.0; 

                if($macroStructure > 0.5){
                        
                        $baseWidth = 12.0;
                }elseif($macroStructure < -0.3){
                        
                        $baseWidth = 4.0;
                }elseif($macroStructure < -0.1){
                        
                        $baseWidth = 6.0;
                }

                
                
                switch($originalBiomeId){
                        case 2:  
                                
                                
                                $baseWidth = min($baseWidth, 10.0);
                                break;
                }

                
                
                $widthVariation = $widthNoise * 2.0; 

                
                $width = $baseWidth + $widthVariation;

                
                $width = RiverMask::clamp($width, self::MIN_WIDTH, self::MAX_WIDTH);

                return $width;
        }
}
