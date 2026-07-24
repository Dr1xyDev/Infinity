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
 * SnowPlains sub-biome.
 *
 * A flat, low-relief snow-covered plain that takes the place of Ice
 * Plains in the wide band of terrain near rivers and seas. Acts as
 * the natural connector between cold Ice Plains and water.
 *
 * Per user request:
 *  - Few spruce trees (cold-tolerant), normal size only (no BigSpruceTree).
 *  - No lily pads on puddles (they would freeze anyway in cold biomes).
 *  - 3 blocks above sea/river level (setElevation 62, 65).
 */
class SnowPlainsBiome extends SnowyBiome{

        public function __construct(){
                parent::__construct();

                // Few spruce trees (cold-tolerant), normal SpruceTree only.
                $trees = new Tree(Sapling::SPRUCE);
                $trees->setNoBigTree(true);
                $trees->setBaseAmount(0);
                $trees->setRandomAmount(2);
                $this->addPopulator($trees);

                $tallGrass = new TallGrass();
                $tallGrass->setBaseAmount(3);
                $tallGrass->setRandomAmount(2);

                $sugarcane = new Sugarcane();
                $sugarcane->setBaseAmount(2);

                // Rare puddles, NO lily pads (cold biome).
                $waterPit = new WaterPit();
                $waterPit->setBaseAmount(0);
                $waterPit->setRandomAmount(1);

                $this->addPopulator($tallGrass);
                $this->addPopulator($sugarcane);
                $this->addPopulator($waterPit);

                // 3 blocks above sea level - flat plains at river/sea height.
                $this->setElevation(62, 65);

                $this->temperature = 0.0;
                $this->rainfall = 0.5;
        }

        public function getName() : string{
                return "Snow Plains";
        }

        public function getColor(){
                return 0xe5eff7; // Pale snow white-blue
        }
}
