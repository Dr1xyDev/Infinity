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
use pocketmine\level\generator\populator\WaterPit;
use pocketmine\block\Block;
use pocketmine\block\Flower as FlowerBlock;
use pocketmine\level\generator\populator\Flower;
use pocketmine\level\generator\populator\Sugarcane;
use pocketmine\level\generator\populator\Tree;
use pocketmine\block\Sapling;

/**
 * OakPlains sub-biome.
 *
 * This is the most common plains sub-biome and the primary "connector"
 * between land biomes and rivers/seas. It replaces the original biome
 * in a wide band near water so that the boundary between land and water
 * is always a smooth, flat grassy plain that transitions through a
 * sand/gravel beach into the river or ocean.
 *
 * Per user request:
 *  - Few oak trees, but ONLY the normal OakTree variant (no BigTree).
 *  - More flowers than vanilla Plains (8 base + 6 random, full flower set).
 *  - Rare puddles (WaterPit) but those puddles must NOT carry lily pads,
 *    so no LilyPad populator is added here.
 *  - 3 blocks above sea/river level (setElevation 62, 65).
 */
class OakPlainsBiome extends GrassyBiome{

        public function __construct(){
                parent::__construct();

                // Few oak trees, normal size only (no BigTree huge variants).
                $trees = new Tree(Sapling::OAK);
                $trees->setNoBigTree(true);
                $trees->setBaseAmount(0);
                $trees->setRandomAmount(2);
                $this->addPopulator($trees);

                $sugarcane = new Sugarcane();
                $sugarcane->setBaseAmount(3);
                $tallGrass = new TallGrass();
                $tallGrass->setBaseAmount(20);
                $tallGrass->setRandomAmount(8);

                // Rare puddles only - and NO lily pads on them.
                $waterPit = new WaterPit();
                $waterPit->setBaseAmount(0);
                $waterPit->setRandomAmount(1);

                // Lots of flowers per user request: full vanilla Plains
                // flower set, with higher density than the regular Plains
                // biome (PlainBiome uses base 2). OakPlains uses base 8 +
                // random 6, which gives 8-14 flowers per chunk.
                $flower = new Flower();
                $flower->setBaseAmount(8);
                $flower->setRandomAmount(6);
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

                // 3 blocks above sea level - flat plains at river/sea height.
                $this->setElevation(62, 65);

                $this->temperature = 0.8;
                $this->rainfall = 0.4;
        }

        public function getName() : string{
                return "Oak Plains";
        }

        public function getColor(){
                return 0x7cbd5a; // Slightly lighter green than regular Plains
        }
}
