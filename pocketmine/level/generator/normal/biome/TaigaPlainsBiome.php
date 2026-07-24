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
use pocketmine\block\Block;
use pocketmine\level\generator\populator\TallGrass;
use pocketmine\level\generator\populator\WaterPit;
use pocketmine\level\generator\populator\Sugarcane;
use pocketmine\level\generator\populator\Tree;

/**
 * TaigaPlains sub-biome.
 *
 * A flat, low-relief plain that takes the place of Taiga in the wide
 * band of terrain near rivers and seas. Acts as the natural connector
 * between dense spruce Taiga and water.
 *
 * Per user request:
 *  - Few spruce trees, normal size only (no BigSpruceTree variants).
 *  - No lily pads on puddles.
 *  - 3 blocks above sea/river level (setElevation 62, 65).
 *
 * Note: TaigaPlains does NOT extend SnowyBiome (no snow layer) because
 * it sits at sea level and acts as the transition to open water. The
 * podzol ground cover from Taiga is kept so the soil color matches.
 */
class TaigaPlainsBiome extends GrassyBiome{

        public function __construct(){
                parent::__construct();

                // Few spruce trees, normal SpruceTree only (no BigSpruceTree).
                $trees = new Tree(Sapling::SPRUCE);
                $trees->setNoBigTree(true);
                $trees->setBaseAmount(1);
                $trees->setRandomAmount(1);
                $this->addPopulator($trees);

                $tallGrass = new TallGrass();
                $tallGrass->setBaseAmount(4);
                $tallGrass->setRandomAmount(2);

                $sugarcane = new Sugarcane();
                $sugarcane->setBaseAmount(3);

                // Rare puddles, NO lily pads.
                $waterPit = new WaterPit();
                $waterPit->setBaseAmount(0);
                $waterPit->setRandomAmount(1);

                $this->addPopulator($tallGrass);
                $this->addPopulator($sugarcane);
                $this->addPopulator($waterPit);

                // Override ground cover: podzol surface like real taiga soil.
                $this->setGroundCover([
                        Block::get(Block::PODZOL, 0),
                        Block::get(Block::DIRT, 0),
                        Block::get(Block::DIRT, 0),
                        Block::get(Block::DIRT, 0),
                        Block::get(Block::DIRT, 0),
                ]);

                // 3 blocks above sea level - flat plains at river/sea height.
                $this->setElevation(62, 65);

                $this->temperature = 0.05;
                $this->rainfall = 0.5;
        }

        public function getName() : string{
                return "Taiga Plains";
        }

        public function getColor(){
                return 0x4f7a5e; // Cool taiga green
        }
}
