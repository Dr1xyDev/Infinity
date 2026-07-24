<?php

/*
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.0
 *               InfinityProject By @Dr1xyDev
 *   YT:         @Dr1xyDev
 *   GitHub:     github.com/Dr1xyDev/Infinity
*/

namespace pocketmine\level\generator\normal\biome;

use pocketmine\block\Block;
use pocketmine\level\generator\populator\Sugarcane;
use pocketmine\level\generator\populator\TallGrass;

class RiverBiome extends WateryBiome{

        public function __construct(){
                parent::__construct();

                // Default ground cover: clay/sand bottom (vanilla-like)
                // Underwater rivers should NOT have grass on the bottom
                // This is the FALLBACK cover - RiverBiomeMixer overrides this
                // with biome-specific cover (desert=sand, taiga=gravel, etc.)
                $this->setGroundCover([
                        Block::get(Block::CLAY_BLOCK, 0),
                        Block::get(Block::SAND, 0),
                        Block::get(Block::SAND, 0),
                        Block::get(Block::DIRT, 0),
                        Block::get(Block::DIRT, 0),
                ]);

                // Default populators - these are FALLBACK populators
                // RiverDecorator (from the new river system) handles
                // biome-specific decoration when the river system is active
                $sugarcane = new Sugarcane();
                $sugarcane->setBaseAmount(5);
                $tallGrass = new TallGrass();
                $tallGrass->setBaseAmount(3);

                $this->addPopulator($sugarcane);
                $this->addPopulator($tallGrass);

                // River elevation: Beach-like shallow depression
                // Terrain surface at waterHeight (62), riverbed only 1-3 blocks below.
                // The bank zone creates a gentle Beach-like slope from terrain to water.
                // RiverCarver modifies elevation dynamically, this is the fallback reference.
                $this->setElevation(59, 62);

                $this->temperature = 0.5;
                $this->rainfall = 0.7;
        }

        public function getName() : string{
                return "River";
        }

        public function getColor(){
                return 0x2557a6; // Blue-ish color for river biome (water tint)
        }
}
