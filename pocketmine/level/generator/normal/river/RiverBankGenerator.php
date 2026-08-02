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

class RiverBankGenerator{

        
        const MAX_BANK_ZONE = 12.0;

        
        const MIN_BANK_ZONE = 4.0;

        
        public static function compute(float $bankNoise, float $width, int $originalBiomeId) : array{
                
                
                
                $bankNoiseFactor = ($bankNoise + 1.0) * 0.5; 
                $baseBankZone = $width * 0.6; 
                $bankZone = $baseBankZone + $bankNoiseFactor * 4.0;
                $bankZone = RiverMask::clamp($bankZone, self::MIN_BANK_ZONE, self::MAX_BANK_ZONE);

                
                switch($originalBiomeId){
                        case 3:  
                                $bankZone *= 0.8; 
                                break;
                        case 20: 
                                $bankZone *= 0.85;
                                break;
                        case 6:  
                                $bankZone *= 1.5; 
                                break;
                        case 2:  
                        case 43: 
                                $bankZone *= 1.2; 
                                break;
                }

                
                
                
                $bankShape = 'beach'; 
                if($bankNoise < -0.6){
                        $bankShape = 'slope'; 
                }elseif($bankNoise > 0.5){
                        $bankShape = 'floodplain'; 
                }

                
                
                
                
                
                $steepnessFactor = 0.5 + $bankNoiseFactor * 0.15; 
                switch($originalBiomeId){
                        case 3:  
                                $steepnessFactor = 0.7; 
                                break;
                        case 20: 
                                $steepnessFactor = 0.65;
                                break;
                        case 6:  
                                $steepnessFactor = 0.4; 
                                break;
                        case 2:  
                        case 43: 
                                $steepnessFactor = 0.5; 
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

                
                $blend = 0.0;

                switch($bankShape){
                        case 'beach':
                                
                                
                                $blend = RiverMask::smootherstep(0, 1, $bankIntensity);
                                break;

                        case 'slope':
                                
                                
                                $blend = pow($bankIntensity, 0.7);
                                break;

                        case 'floodplain':
                                
                                
                                $blend = RiverMask::smoothstep(0, 1, $bankIntensity) * 0.8
                                       + $bankIntensity * 0.2;
                                break;

                        default:
                                $blend = $bankIntensity;
                                break;
                }

                
                $effectiveBlend = $blend * $steepnessFactor;

                
                
                
                
                
                
                
                
                
                
                
                
                
                $elevation = $normalElevation * (1 - $effectiveBlend) + $riverElevation * $effectiveBlend;

                return $elevation;
        }
}
