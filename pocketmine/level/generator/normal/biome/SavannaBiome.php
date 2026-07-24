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

class SavannaBiome extends GrassyBiome{

        public function __construct(){
                parent::__construct();

                // Acacia trees (sparse, like vanilla savanna)
                $trees = new Tree(Sapling::ACACIA);
                $trees->setBaseAmount(2);
                $trees->setRandomAmount(1);
                $this->addPopulator($trees);

                // Tall grass for savanna floor (dry, yellowish grass)
                $tallGrass = new TallGrass();
                $tallGrass->setBaseAmount(10);
                $tallGrass->setRandomAmount(5);
                $this->addPopulator($tallGrass);

                // Grand canyons: occasional large canyon trenches in savanna terrain
                // ~3% chance per chunk, creates dramatic ravines with sand/sandstone walls
                // DISABLED: Bug fix - remove this structure to prevent unwanted terrain generation
                // $canyon = new SavannaCanyon();
                // $this->addPopulator($canyon);

                // Soft, low relief: only 4 blocks above water/river level (62),
                // so Savanna terrain is gentle and, as a side-effect, barely
                // touched by river carving at all - there's very little height
                // difference left for a river to smooth away in the first place.
                $this->setElevation(62, 66);

                // Savanna: very hot and dry
                $this->temperature = 1.2;
                $this->rainfall = 0.05;
        }

        public function getName() : string{
                return "Savanna";
        }
}
