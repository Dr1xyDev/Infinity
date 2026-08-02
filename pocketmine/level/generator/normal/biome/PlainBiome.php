<?php
/*    
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█    
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░    
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.1
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

                
                
                
                /* Oak ocasional */
                $trees = new Tree(Sapling::OAK);
                $trees->setBaseAmount(0);
                $trees->setRandomAmount(1);
                $this->addPopulator($trees);

                /* Birch salpicados en la llanura */
                $birchTrees = new Tree(Sapling::BIRCH);
                $birchTrees->setBaseAmount(1);
                $birchTrees->setRandomAmount(2);
                $this->addPopulator($birchTrees);

                $sugarcane = new Sugarcane();
                $sugarcane->setBaseAmount(6);
                $tallGrass = new TallGrass();
                $tallGrass->setBaseAmount(25);
                $waterPit = new WaterPit();
                
                
                
                
                
                
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

                
                
                $this->setElevation(62, 64);

                $this->temperature = 0.8;
                $this->rainfall = 0.4;
        }

        public function getName() : string{
                return "Plains";
        }
}
