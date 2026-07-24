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

/**
 * Hills ("Colina") sub-biome: a long transitional foothill strip that
 * connects the (now soft, low-relief) oak Forest biome with the taller
 * terrain around it - Mountains, RoofedForest and Jungle. It rises
 * gradually from Forest's height up toward whichever of those it's
 * bordering, similar to vanilla's Extreme Hills foothills.
 *
 * Which Forest columns become Hills is decided in Normal::pickBiome():
 * a Forest column only turns into Hills when it's both (a) reasonably
 * far from any river - so the river's already-smooth connection to
 * Forest is never interrupted by a hill starting right at the bank -
 * and (b) within a wide detection radius of Mountains/SmallMountains/
 * RoofedForest/Jungle territory, making the transition band deliberately
 * long instead of a thin seam.
 */
class HillsBiome extends GrassyBiome{

        public function __construct(){
                parent::__construct();

                // Sparse oak trees, thinning out as the land climbs
                $trees = new Tree(Sapling::OAK);
                $trees->setBaseAmount(1);
                $trees->setRandomAmount(2);
                $this->addPopulator($trees);

                $tallGrass = new TallGrass();
                $tallGrass->setBaseAmount(4);
                $tallGrass->setRandomAmount(2);
                $this->addPopulator($tallGrass);

                // Rises well above Forest's soft low relief, climbing toward
                // Mountains/RoofedForest/Jungle - a long, gradual ramp rather
                // than a sudden step.
                $this->setElevation(66, 100);

                // Same general temperate climate as Forest
                $this->temperature = 0.65;
                $this->rainfall = 0.7;
        }

        public function getName() : string{
                return "Hills";
        }

        public function getColor(){
                return 0x6b8f4f;
        }
}
