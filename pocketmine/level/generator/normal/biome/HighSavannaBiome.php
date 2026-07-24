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

use pocketmine\block\Sapling;
use pocketmine\level\generator\populator\TallGrass;
use pocketmine\level\generator\populator\Tree;
use pocketmine\level\generator\normal\populator\SavannaCanyon;

/**
 * HighSavanna sub-biome: an elevated version of the Savanna biome.
 * This biome is 30 blocks higher than the normal Savanna and only generates
 * within (attached to) Savanna zones, creating plateau-like elevated
 * savanna terrain that rises dramatically above the surrounding flat
 * savanna. It shares the same flora (acacia trees, tall grass, canyons)
 * and climate (hot, dry) as the normal Savanna, but at a much higher
 * altitude.
 *
 * Which Savanna columns become HighSavanna is decided by precipitation
 * (rainfall) in Normal::pickBiome() - the wetter parts of Savanna
 * territory rise into HighSavanna, so the transition follows the same
 * smooth rainfall gradient that placed Savanna there in the first place,
 * instead of an unrelated noise field.
 */
class HighSavannaBiome extends GrassyBiome{

        public function __construct(){
                parent::__construct();

                // Same flora as Savanna: acacia trees (sparse)
                $trees = new Tree(Sapling::ACACIA);
                $trees->setBaseAmount(2);
                $trees->setRandomAmount(1);
                $this->addPopulator($trees);

                // Tall grass (same as Savanna)
                $tallGrass = new TallGrass();
                $tallGrass->setBaseAmount(10);
                $tallGrass->setRandomAmount(5);
                $this->addPopulator($tallGrass);

                // Canyons (same as Savanna)
                // DISABLED: Bug fix - remove this structure to prevent unwanted terrain generation
                // $canyon = new SavannaCanyon();
                // $this->addPopulator($canyon);

                // Kept as its own elevated plateau range (Savanna is now a
                // soft 62-66, but HighSavanna stays a dramatic plateau tower
                // rather than being re-derived from Savanna's new low relief)
                $this->setElevation(93, 102);

                // Same climate as Savanna: very hot and dry
                $this->temperature = 1.2;
                $this->rainfall = 0.05;
        }

        public function getName() : string{
                return "HighSavanna";
        }
}
