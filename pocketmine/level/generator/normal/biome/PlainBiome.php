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

use pocketmine\level\generator\populator\TallGrass;
use pocketmine\level\generator\populator\LilyPad;
use pocketmine\level\generator\populator\WaterPit;
use pocketmine\block\Block;
use pocketmine\block\Flower as FlowerBlock;
use pocketmine\level\generator\populator\Flower;
use pocketmine\level\generator\populator\Sugarcane;
use pocketmine\level\generator\populator\Tree;
use pocketmine\block\Sapling;

class PlainBiome extends GrassyBiome{

        public function __construct(){
                parent::__construct();

                // Occasional oak trees in plains (vanilla-like: rare single trees)
                // With 5% chance of BigTree (huge oak) through Tree::growTree()
                // Reduced further per user request: fewer trees overall.
                $trees = new Tree(Sapling::OAK);
                $trees->setBaseAmount(0);
                $trees->setRandomAmount(1);
                $this->addPopulator($trees);

                $sugarcane = new Sugarcane();
                $sugarcane->setBaseAmount(6);
                $tallGrass = new TallGrass();
                $tallGrass->setBaseAmount(25);
                $waterPit = new WaterPit();
                // BUGFIX: this was baseAmount(9999), meaning ~9999-10000 water
                // placement attempts EVERY chunk (256 blocks) - it was flooding
                // almost every grass/dirt surface block with a puddle, which is
                // exactly what made Plains look hollowed out/pockmarked instead
                // of a clean grassy field. A rare, occasional pond is what was
                // actually intended here.
                $waterPit->setBaseAmount(0);
                $waterPit->setRandomAmount(1);
                $lilyPad = new LilyPad();
                $lilyPad->setBaseAmount(8);

                $flower = new Flower();
                $flower->setBaseAmount(2);
                $flower->addType([Block::DANDELION, 0]);
                $flower->addType([Block::RED_FLOWER, FlowerBlock::TYPE_POPPY]);
                $flower->addType([Block::RED_FLOWER, FlowerBlock::TYPE_AZURE_BLUET]);
                $flower->addType([Block::RED_FLOWER, FlowerBlock::TYPE_RED_TULIP]);
                $flower->addType([Block::RED_FLOWER, FlowerBlock::TYPE_ORANGE_TULIP]);
                $flower->addType([Block::RED_FLOWER, FlowerBlock::TYPE_WHITE_TULIP]);
                $flower->addType([Block::RED_FLOWER, FlowerBlock::TYPE_PINK_TULIP]);
                $flower->addType([Block::RED_FLOWER, FlowerBlock::TYPE_OXEYE_DAISY]);

                $this->addPopulator($sugarcane);
                $this->addPopulator($tallGrass);
                $this->addPopulator($flower);
                $this->addPopulator($waterPit);
                $this->addPopulator($lilyPad);

                // Flat plains sitting right at sea level, only 2 blocks above
                // water (waterHeight = 62), per user request.
                $this->setElevation(62, 64);

                $this->temperature = 0.8;
                $this->rainfall = 0.4;
        }

        public function getName() : string{
                return "Plains";
        }
}
