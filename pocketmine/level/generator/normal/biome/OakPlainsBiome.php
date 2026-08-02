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
use pocketmine\level\generator\populator\WaterPit;
use pocketmine\block\Block;
use pocketmine\block\Flower as FlowerBlock;
use pocketmine\level\generator\populator\Flower;
use pocketmine\level\generator\populator\Sugarcane;
use pocketmine\level\generator\populator\Tree;
use pocketmine\block\Sapling;

class OakPlainsBiome extends GrassyBiome{

        public function __construct(){
                parent::__construct();

                
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

                
                $waterPit = new WaterPit();
                $waterPit->setBaseAmount(0);
                $waterPit->setRandomAmount(1);

                
                
                
                
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

                
                $this->setElevation(62, 65);

                $this->temperature = 0.8;
                $this->rainfall = 0.4;
        }

        public function getName() : string{
                return "Oak Plains";
        }

        public function getColor(){
                return 0x7cbd5a; 
        }
}
