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
 * BirchPlains sub-biome.
 *
 * A flat, low-relief plain that takes the place of Birch Forest in
 * the wide band of terrain near rivers and seas. Acts as the natural
 * connector between Birch Forest and water.
 *
 * Per user request:
 *  - Few birch trees, normal size only (the tall birch variant in
 *    Tree::growTree is a 1/40 chance; we set noBigTree=true for
 *    clarity/forward safety, though birch has no truly "big" variant).
 *  - No lily pads on puddles.
 *  - 3 blocks above sea/river level (setElevation 62, 65).
 */
class BirchPlainsBiome extends GrassyBiome{

        public function __construct(){
                parent::__construct();

                // Few birch trees, normal BirchTree only.
                $trees = new Tree(Sapling::BIRCH);
                $trees->setNoBigTree(true);
                $trees->setBaseAmount(0);
                $trees->setRandomAmount(2);
                $this->addPopulator($trees);

                $tallGrass = new TallGrass();
                $tallGrass->setBaseAmount(10);
                $tallGrass->setRandomAmount(4);

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

                $this->temperature = 0.5;
                $this->rainfall = 0.5;
        }

        public function getName() : string{
                return "Birch Plains";
        }

        public function getColor(){
                return 0x6fae5e; // Light birch-forest green
        }
}
