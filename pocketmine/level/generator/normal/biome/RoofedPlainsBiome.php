<?php

/*
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█
 * ░░█░░█░█▀▀░░█░░█░█░░█░░░█░░░█░
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.0
 *               InfinityProject By @Dr1xyDev
 *   YT:         @Dr1xyDev
 *   GitHub:     github.com/Dr1xyDev/Infinity
*/

namespace pocketmine\level\generator\normal\biome;

use pocketmine\block\Sapling;
use pocketmine\level\generator\populator\TallGrass;
use pocketmine\level\generator\populator\WaterPit;
use pocketmine\level\generator\populator\Sugarcane;
use pocketmine\level\generator\populator\Tree;

/**
 * RoofedPlains sub-biome.
 *
 * A flat, low-relief plain that takes the place of Roofed Forest in
 * the wide band of terrain near rivers and seas. Acts as the natural
 * connector between dense dark-oak Roofed Forest and water.
 *
 * Per user request:
 *  - Few dark oak trees, normal size only (DarkOakTree already has no
 *    big variant, but setNoBigTree(true) is set for forward safety).
 *  - No huge mushrooms (unlike Roofed Forest itself, which has them).
 *  - No lily pads on puddles.
 *  - 3 blocks above sea/river level (setElevation 62, 65).
 */
class RoofedPlainsBiome extends GrassyBiome{

        public function __construct(){
                parent::__construct();

                // Few dark oak trees, normal size only.
                $trees = new Tree(Sapling::DARK_OAK);
                $trees->setNoBigTree(true);
                $trees->setBaseAmount(0);
                $trees->setRandomAmount(2);
                $this->addPopulator($trees);

                $tallGrass = new TallGrass();
                $tallGrass->setBaseAmount(6);
                $tallGrass->setRandomAmount(3);

                $sugarcane = new Sugarcane();
                $sugarcane->setBaseAmount(3);

                // Rare puddles, NO lily pads.
                $waterPit = new WaterPit();
                $waterPit->setBaseAmount(0);
                $waterPit->setRandomAmount(1);

                $this->addPopulator($tallGrass);
                $this->addPopulator($sugarcane);
                $this->addPopulator($waterPit);

                // 3 blocks above sea level - flat plains at river/sea height.
                $this->setElevation(62, 65);

                $this->temperature = 0.7;
                $this->rainfall = 0.7;
        }

        public function getName() : string{
                return "Roofed Plains";
        }

        public function getColor(){
                return 0x4a6b3a; // Darker grass green
        }
}
