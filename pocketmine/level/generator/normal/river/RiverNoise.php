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

use pocketmine\level\generator\noise\Simplex;
use pocketmine\utils\Random;

class RiverNoise{

        
        private $macroNoise;
        
        private $mediumNoise;
        
        private $microNoise;
        
        private $widthNoise;
        
        private $depthNoise;
        
        private $bankNoise;

        
        private $worldSeed;

        
        const SALT_MACRO    = 0x5DEECE66D;
        const SALT_MEDIUM   = 0x123456789;
        const SALT_MICRO    = 0x9ABCDEF01;
        const SALT_WIDTH    = 0x2468ACF13;
        const SALT_DEPTH    = 0x369CDEF25;
        const SALT_BANK     = 0x48E012F37;

        
        
        const WEIGHT_MACRO  = 1.0;
        const WEIGHT_MEDIUM = 0.30;
        const WEIGHT_MICRO  = 0.05;

        
        const MIN_GRADIENT = 0.001;

        
        const GRADIENT_OFFSET = 0.5;

        public function __construct(int $worldSeed){
                $this->worldSeed = $worldSeed;

                
                
                
                
                $this->macroNoise = $this->createSimplex($worldSeed, self::SALT_MACRO, 4, 1 / 4, 1 / 1280);

                
                
                
                
                $this->mediumNoise = $this->createSimplex($worldSeed, self::SALT_MEDIUM, 2, 1 / 2, 1 / 410);

                
                
                
                $this->microNoise = $this->createSimplex($worldSeed, self::SALT_MICRO, 2, 1 / 2, 1 / 64);

                
                
                $this->widthNoise = $this->createSimplex($worldSeed, self::SALT_WIDTH, 3, 1 / 2, 1 / 64);

                
                
                $this->depthNoise = $this->createSimplex($worldSeed, self::SALT_DEPTH, 3, 1 / 2, 1 / 64);

                
                
                $this->bankNoise = $this->createSimplex($worldSeed, self::SALT_BANK, 2, 1 / 2, 1 / 32);
        }

        private function createSimplex(int $worldSeed, int $salt, int $octaves, float $persistence, float $expansion) : Simplex{
                $layerSeed = $worldSeed ^ $salt;
                $random = new Random($layerSeed);
                return new Simplex($random, $octaves, $persistence, $expansion);
        }

        
        public function getMainPathValue(int $x, int $z) : float{
                return $this->macroNoise->noise2D($x, $z, true) * self::WEIGHT_MACRO
                     + $this->mediumNoise->noise2D($x, $z, true) * self::WEIGHT_MEDIUM
                     + $this->microNoise->noise2D($x, $z, true) * self::WEIGHT_MICRO;
        }

        
        public function getEstimatedDistance(int $x, int $z) : float{
                $pathValue = $this->getMainPathValue($x, $z);

                
                
                $off = self::GRADIENT_OFFSET;

                $pathXPlus  = $this->getMainPathValueRaw($x + $off, $z);
                $pathXMinus = $this->getMainPathValueRaw($x - $off, $z);
                $pathZPlus  = $this->getMainPathValueRaw($x, $z + $off);
                $pathZMinus = $this->getMainPathValueRaw($x, $z - $off);

                $gradX = ($pathXPlus - $pathXMinus) / (2.0 * $off);
                $gradZ = ($pathZPlus - $pathZMinus) / (2.0 * $off);

                $gradientMag = sqrt($gradX * $gradX + $gradZ * $gradZ);

                
                $gradientMag = max($gradientMag, self::MIN_GRADIENT);

                
                
                return abs($pathValue) / $gradientMag;
        }

        
        private function getMainPathValueRaw(float $x, float $z) : float{
                return $this->macroNoise->noise2D($x, $z, true) * self::WEIGHT_MACRO
                     + $this->mediumNoise->noise2D($x, $z, true) * self::WEIGHT_MEDIUM
                     + $this->microNoise->noise2D($x, $z, true) * self::WEIGHT_MICRO;
        }

        
        public function compute(int $x, int $z) : array{
                return [
                        'macro'  => $this->macroNoise->noise2D($x, $z, true),
                        'medium' => $this->mediumNoise->noise2D($x, $z, true),
                        'micro'  => $this->microNoise->noise2D($x, $z, true),
                        'width'  => $this->widthNoise->noise2D($x, $z, true),
                        'depth'  => $this->depthNoise->noise2D($x, $z, true),
                        'bank'   => $this->bankNoise->noise2D($x, $z, true),
                ];
        }

        
        public function getWidthNoise(int $x, int $z) : float{
                return $this->widthNoise->noise2D($x, $z, true);
        }

        
        public function getDepthNoise(int $x, int $z) : float{
                return $this->depthNoise->noise2D($x, $z, true);
        }

        
        public function getBankNoise(int $x, int $z) : float{
                return $this->bankNoise->noise2D($x, $z, true);
        }

        
        public function getWorldSeed() : int{
                return $this->worldSeed;
        }
}
