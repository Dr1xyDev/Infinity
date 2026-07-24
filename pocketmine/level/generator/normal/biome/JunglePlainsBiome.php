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
 * JunglePlains sub-biome.
 *
 * A flat, low-relief plain that takes the place of Jungle in the wide
 * band of terrain near rivers and seas. Acts as the natural connector
 * between dense Jungle and water - the transition goes Jungle ->
 * JunglePlains (flat grassy plain with sparse normal jungle trees)
 * -> sand/gravel beach -> river/sea.
 *
 * Per user request:
 *  - Few jungle trees, normal size only (no BigJungleTree huge variants).
 *  - No lily pads on puddles.
 *  - 3 blocks above sea/river level (setElevation 62, 65).
 */
class JunglePlainsBiome extends GrassyBiome{

        public function __construct(){
                parent::__construct();

                // Few jungle trees, normal NewJungleTree only (no BigJungleTree).
                $trees = new Tree(Sapling::JUNGLE);
                $trees->setNoBigTree(true);
                $trees->setBaseAmount(1);
                $trees->setRandomAmount(1);
                $this->addPopulator($trees);

                $tallGrass = new TallGrass();
                $tallGrass->setBaseAmount(8);
                $tallGrass->setRandomAmount(3);

                $sugarcane = new Sugarcane();
                $sugarcane->setBaseAmount(4);

                // Rare puddles, NO lily pads (per user request).
                $waterPit = new WaterPit();
                $waterPit->setBaseAmount(0);
                $waterPit->setRandomAmount(1);

                $this->addPopulator($tallGrass);
                $this->addPopulator($sugarcane);
                $this->addPopulator($waterPit);

                // 3 blocks above sea level - flat plains at river/sea height.
                $this->setElevation(62, 65);

                $this->temperature = 0.95;
                $this->rainfall = 0.7;
        }

        public function getName() : string{
                return "Jungle Plains";
        }

        public function getColor(){
                return 0x5fa84e; // Lighter jungle green
        }
}
