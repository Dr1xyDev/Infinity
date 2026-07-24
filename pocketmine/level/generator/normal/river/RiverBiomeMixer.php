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

use pocketmine\block\Block;
use pocketmine\level\generator\biome\Biome;

class RiverBiomeMixer{

        /**
         * Biome-specific ground cover for river columns.
         * REDUCED to 3 blocks (shallow, no uniform clay/dirt columns).
         * Only the riverbed surface material + 2 subsurface layers.
         *
         * @param int $originalBiomeId Original biome before river override
         * @return Block[] Ground cover blocks (top to bottom, max 3)
         */
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

                        default: // PLAINS, OAK_PLAINS and others
                                return [
                                        Block::get(Block::SAND, 0),
                                        Block::get(Block::DIRT, 0),
                                        Block::get(Block::DIRT, 0),
                                ];
                }
        }

        /**
         * Blended biome color at river edges.
         */
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

        /**
         * Determines the ADAPTIVE surface block for a bank zone column.
         *
         * NO fixed columns. Each column gets a different surface material
         * based on: bankIntensity (distance from river) + noise (variation)
         * + original biome (adapts to neighboring terrain).
         *
         * Result per column (varies by noise):
         * - Near river (bankIntensity > 0.6): mostly sand, rare gravel
         * - Mid zone (0.3-0.6): mix of biome surface + sand (noise picks)
         * - Far from river (< 0.3): biome surface (grass, sand, etc.)
         * - Gravel appears ~15% of columns (only when noise is in narrow range)
         *
         * @param int   $originalBiomeId Original biome
         * @param float $bankIntensity   Bank zone intensity (1=near river, 0=far)
         * @param float $surfaceNoise    Noise value for this column (-1 to 1)
         * @return int Block ID for surface block
         */
        public static function getAdaptiveBankSurface(int $originalBiomeId, float $bankIntensity, float $surfaceNoise) : int{
                // Biome's natural surface block
                $biomeSurface = self::getBiomeSurfaceBlock($originalBiomeId);
                $beachBlock = self::getBiomeBeachBlock($originalBiomeId);

                // Gravel: rare (~15% chance), only when noise is in narrow range
                // Never forms columns - scattered randomly by noise
                $gravelThreshold = 0.75; // Only when noise > 0.75 (about 12.5% of columns)
                if($surfaceNoise > $gravelThreshold && $bankIntensity > 0.4){
                        return self::getBiomeGravelBlock($originalBiomeId);
                }

                // Near river: mostly beach material (sand, clay, gravel depending on biome)
                if($bankIntensity > 0.65){
                        // 80% beach block, 20% biome surface (noise determines)
                        if($surfaceNoise > -0.4){
                                return $beachBlock;
                        }else{
                                return $biomeSurface;
                        }
                }

                // Mid zone: mix of biome surface + beach
                if($bankIntensity > 0.35){
                        // noise determines the mix:
                        // positive noise → more beach, negative → more biome surface
                        $sandThreshold = -0.2 + ($bankIntensity - 0.35) * 1.0; // -0.2 to +0.65
                        if($surfaceNoise > $sandThreshold){
                                return $beachBlock;
                        }else{
                                return $biomeSurface;
                        }
                }

                // Far from river: mostly biome surface, occasional beach
                if($bankIntensity > 0.15){
                        // Only ~30% beach blocks at this distance
                        if($surfaceNoise > 0.5){
                                return $beachBlock;
                        }else{
                                return $biomeSurface;
                        }
                }

                // Very far: pure biome surface
                return $biomeSurface;
        }

        /**
         * Determines the ADAPTIVE subsurface block (1 block below surface)
         * for a bank zone column. Usually matches biome's subsurface
         * (dirt for Plains, sandstone for Desert) with occasional variation.
         *
         * @param int   $originalBiomeId Original biome
         * @param float $bankIntensity   Bank zone intensity
         * @param float $surfaceNoise    Noise for variation
         * @return int Block ID for subsurface block
         */
        public static function getAdaptiveBankSubsurface(int $originalBiomeId, float $bankIntensity, float $surfaceNoise) : int{
                // Subsurface usually matches the biome's natural subsurface
                // Near river: sometimes sand below surface, sometimes biome subsurface
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

                        default: // Plains, Forest, Oak Plains, Jungle Plains, etc.
                                // Near river: sometimes sand as subsurface
                                if($bankIntensity > 0.5 && $surfaceNoise > 0.1){
                                        return Block::SAND;
                                }
                                return Block::DIRT;
                }
        }

        /**
         * Gets the biome's natural surface block (what GroundCover places on top).
         */
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

        /**
         * Gets the biome's Beach block (what appears near water).
         * Sand for most biomes, clay for Swamp, gravel for Mountains/Taiga.
         */
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

        /**
         * Gets the biome's rare gravel block (appears ~15% of columns).
         */
        public static function getBiomeGravelBlock(int $originalBiomeId) : int{
                switch($originalBiomeId){
                        case Biome::DESERT:
                        case Biome::DESERT_PLAINS:   return Block::GRAVEL; // Rare gravel in desert
                        case Biome::SWAMP:           return Block::CLAY_BLOCK; // Clay instead of gravel
                        case Biome::MOUNTAINS:       return Block::GRAVEL;
                        default:                     return Block::GRAVEL;
                }
        }

        /** Sugar cane amount per biome near river banks */
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
                        default:                             return 5; // Plains, Oak Plains, etc.
                }
        }

        /**
         * Lily pad amount (swamp rivers ONLY).
         * Per user request: plains sub-biomes never carry lily pads,
         * so all plains variants return 0 here in addition to the
         * base rule that only Swamp rivers get lily pads.
         */
        public static function getLilyPadAmount(int $originalBiomeId) : int{
                return $originalBiomeId === Biome::SWAMP ? 4 : 0;
        }

        /** Tall grass amount per biome near river banks */
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

        /** Flower amount per biome near river banks */
        public static function getFlowerAmount(int $originalBiomeId) : int{
                switch($originalBiomeId){
                        case Biome::PLAINS:
                        case Biome::OAK_PLAINS:           return 4; // Oak Plains has extra flowers
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
