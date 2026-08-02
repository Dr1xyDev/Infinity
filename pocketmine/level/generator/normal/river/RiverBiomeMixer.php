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
use pocketmine\level\generator\biome\Biome;

class RiverBiomeMixer{

        
        public static function getGroundCover(int $originalBiomeId) : array{
                switch($originalBiomeId){
                        case Biome::DESERT:
                        case Biome::DESERT_PLAINS:
                                return [
                                        Block::get(Block::SAND, 0),
                                        Block::get(Block::SAND, 0),
                                        Block::get(Block::SANDSTONE, 0),
                                ];

                        case Biome::TAIGA:
                        case Biome::TAIGA_PLAINS:
                        case Biome::ICE_PLAINS:
                        case Biome::SNOW_PLAINS:
                                return [
                                        Block::get(Block::GRAVEL, 0),
                                        Block::get(Block::STONE, 0),
                                        Block::get(Block::STONE, 0),
                                ];

                        case Biome::SWAMP:
                                return [
                                        Block::get(Block::CLAY_BLOCK, 0),
                                        Block::get(Block::DIRT, 0),
                                        Block::get(Block::DIRT, 0),
                                ];

                        case Biome::JUNGLE:
                        case Biome::JUNGLE_PLAINS:
                                return [
                                        Block::get(Block::DIRT, 0),
                                        Block::get(Block::DIRT, 0),
                                        Block::get(Block::STONE, 0),
                                ];

                        case Biome::MOUNTAINS:
                        case Biome::SMALL_MOUNTAINS:
                                return [
                                        Block::get(Block::GRAVEL, 0),
                                        Block::get(Block::STONE, 0),
                                        Block::get(Block::STONE, 0),
                                ];

                        case Biome::SAVANNA:
                                return [
                                        Block::get(Block::SAND, 0),
                                        Block::get(Block::DIRT, 0),
                                        Block::get(Block::SANDSTONE, 0),
                                ];

                        case Biome::ROOFED_FOREST:
                        case Biome::ROOFED_PLAINS:
                                return [
                                        Block::get(Block::DIRT, 0),
                                        Block::get(Block::DIRT, 0),
                                        Block::get(Block::STONE, 0),
                                ];

                        case Biome::FOREST:
                        case Biome::BIRCH_FOREST:
                        case Biome::BIRCH_PLAINS:
                                return [
                                        Block::get(Block::DIRT, 0),
                                        Block::get(Block::DIRT, 0),
                                        Block::get(Block::DIRT, 0),
                                ];

                        default: 
                                return [
                                        Block::get(Block::SAND, 0),
                                        Block::get(Block::DIRT, 0),
                                        Block::get(Block::DIRT, 0),
                                ];
                }
        }

        
        public static function getBlendedColor(int $originalBiomeId, float $riverIntensity, float $bankIntensity) : int{
                $riverR = 0x25; $riverG = 0x57; $riverB = 0xa6;

                $originalBiome = Biome::getBiome($originalBiomeId);
                $origColor = $originalBiome->getColor();
                $origR = ($origColor >> 16) & 0xff;
                $origG = ($origColor >> 8) & 0xff;
                $origB = $origColor & 0xff;

                $blend = RiverMask::clamp($riverIntensity + $bankIntensity * 0.3, 0.0, 1.0);

                $r = (int)($origR * (1 - $blend) + $riverR * $blend);
                $g = (int)($origG * (1 - $blend) + $riverG * $blend);
                $b = (int)($origB * (1 - $blend) + $riverB * $blend);

                return ($r << 16) | ($g << 8) | $b;
        }

        
        public static function getAdaptiveBankSurface(int $originalBiomeId, float $bankIntensity, float $surfaceNoise) : int{
                
                $biomeSurface = self::getBiomeSurfaceBlock($originalBiomeId);
                $beachBlock = self::getBiomeBeachBlock($originalBiomeId);

                
                
                $gravelThreshold = 0.75; 
                if($surfaceNoise > $gravelThreshold && $bankIntensity > 0.4){
                        return self::getBiomeGravelBlock($originalBiomeId);
                }

                
                if($bankIntensity > 0.65){
                        
                        if($surfaceNoise > -0.4){
                                return $beachBlock;
                        }else{
                                return $biomeSurface;
                        }
                }

                
                if($bankIntensity > 0.35){
                        
                        
                        $sandThreshold = -0.2 + ($bankIntensity - 0.35) * 1.0; 
                        if($surfaceNoise > $sandThreshold){
                                return $beachBlock;
                        }else{
                                return $biomeSurface;
                        }
                }

                
                if($bankIntensity > 0.15){
                        
                        if($surfaceNoise > 0.5){
                                return $beachBlock;
                        }else{
                                return $biomeSurface;
                        }
                }

                
                return $biomeSurface;
        }

        
        public static function getAdaptiveBankSubsurface(int $originalBiomeId, float $bankIntensity, float $surfaceNoise) : int{
                
                
                switch($originalBiomeId){
                        case Biome::DESERT:
                        case Biome::DESERT_PLAINS:
                                return Block::SANDSTONE;

                        case Biome::TAIGA:
                        case Biome::TAIGA_PLAINS:
                        case Biome::ICE_PLAINS:
                        case Biome::SNOW_PLAINS:
                                return Block::STONE;

                        case Biome::SWAMP:
                                if($bankIntensity > 0.5 && $surfaceNoise > 0.2){
                                        return Block::CLAY_BLOCK;
                                }
                                return Block::DIRT;

                        case Biome::MOUNTAINS:
                        case Biome::SMALL_MOUNTAINS:
                                return Block::STONE;

                        case Biome::SAVANNA:
                                if($bankIntensity > 0.4){
                                        return Block::SAND;
                                }
                                return Block::DIRT;

                        default: 
                                
                                if($bankIntensity > 0.5 && $surfaceNoise > 0.1){
                                        return Block::SAND;
                                }
                                return Block::DIRT;
                }
        }

        
        public static function getBiomeSurfaceBlock(int $originalBiomeId) : int{
                switch($originalBiomeId){
                        case Biome::DESERT:
                        case Biome::DESERT_PLAINS:      return Block::SAND;
                        case Biome::TAIGA:
                        case Biome::TAIGA_PLAINS:      return Block::GRASS;
                        case Biome::ICE_PLAINS:
                        case Biome::SNOW_PLAINS:       return Block::GRASS;
                        case Biome::SWAMP:             return Block::GRASS;
                        case Biome::MOUNTAINS:
                        case Biome::SMALL_MOUNTAINS:   return Block::GRASS;
                        case Biome::SAVANNA:           return Block::GRASS;
                        case Biome::JUNGLE:
                        case Biome::JUNGLE_PLAINS:     return Block::GRASS;
                        case Biome::ROOFED_FOREST:
                        case Biome::ROOFED_PLAINS:     return Block::GRASS;
                        case Biome::FOREST:
                        case Biome::OAK_PLAINS:
                        case Biome::BIRCH_FOREST:
                        case Biome::BIRCH_PLAINS:      return Block::GRASS;
                        default:                       return Block::GRASS;
                }
        }

        
        public static function getBiomeBeachBlock(int $originalBiomeId) : int{
                switch($originalBiomeId){
                        case Biome::DESERT:
                        case Biome::DESERT_PLAINS:      return Block::SAND;
                        case Biome::TAIGA:
                        case Biome::TAIGA_PLAINS:       return Block::GRAVEL;
                        case Biome::ICE_PLAINS:
                        case Biome::SNOW_PLAINS:        return Block::GRAVEL;
                        case Biome::SWAMP:              return Block::CLAY_BLOCK;
                        case Biome::MOUNTAINS:
                        case Biome::SMALL_MOUNTAINS:    return Block::GRAVEL;
                        case Biome::SAVANNA:            return Block::SAND;
                        case Biome::JUNGLE:
                        case Biome::JUNGLE_PLAINS:      return Block::SAND;
                        case Biome::ROOFED_FOREST:
                        case Biome::ROOFED_PLAINS:      return Block::SAND;
                        case Biome::FOREST:
                        case Biome::OAK_PLAINS:
                        case Biome::BIRCH_FOREST:
                        case Biome::BIRCH_PLAINS:       return Block::SAND;
                        default:                        return Block::SAND;
                }
        }

        
        public static function getBiomeGravelBlock(int $originalBiomeId) : int{
                switch($originalBiomeId){
                        case Biome::DESERT:
                        case Biome::DESERT_PLAINS:   return Block::GRAVEL; 
                        case Biome::SWAMP:           return Block::CLAY_BLOCK; 
                        case Biome::MOUNTAINS:       return Block::GRAVEL;
                        default:                     return Block::GRAVEL;
                }
        }

        
        public static function getSugarcaneAmount(int $originalBiomeId) : int{
                switch($originalBiomeId){
                        case Biome::SWAMP:                   return 8;
                        case Biome::JUNGLE:
                        case Biome::JUNGLE_PLAINS:           return 7;
                        case Biome::SAVANNA:                 return 4;
                        case Biome::DESERT:
                        case Biome::DESERT_PLAINS:           return 3;
                        case Biome::FOREST:
                        case Biome::BIRCH_FOREST:
                        case Biome::BIRCH_PLAINS:            return 4;
                        default:                             return 5; 
                }
        }

        
        public static function getLilyPadAmount(int $originalBiomeId) : int{
                return $originalBiomeId === Biome::SWAMP ? 4 : 0;
        }

        
        public static function getTallGrassAmount(int $originalBiomeId) : int{
                switch($originalBiomeId){
                        case Biome::JUNGLE:
                        case Biome::JUNGLE_PLAINS:        return 6;
                        case Biome::FOREST:               return 4;
                        case Biome::ROOFED_FOREST:
                        case Biome::ROOFED_PLAINS:        return 3;
                        case Biome::PLAINS:
                        case Biome::OAK_PLAINS:           return 5;
                        case Biome::SWAMP:                return 3;
                        case Biome::SAVANNA:              return 4;
                        default:                          return 2;
                }
        }

        
        public static function getFlowerAmount(int $originalBiomeId) : int{
                switch($originalBiomeId){
                        case Biome::PLAINS:
                        case Biome::OAK_PLAINS:           return 4; 
                        case Biome::FOREST:
                        case Biome::BIRCH_FOREST:
                        case Biome::BIRCH_PLAINS:         return 2;
                        case Biome::ROOFED_FOREST:
                        case Biome::ROOFED_PLAINS:        return 1;
                        case Biome::SWAMP:                return 1;
                        default:                          return 0;
                }
        }
}
