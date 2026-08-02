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

use pocketmine\block\Sapling;
use pocketmine\block\Block;
use pocketmine\level\generator\populator\TallGrass;
use pocketmine\level\generator\populator\WaterPit;
use pocketmine\level\generator\populator\Sugarcane;
use pocketmine\level\generator\populator\Tree;

class TaigaPlainsBiome extends GrassyBiome{

        public function __construct(){
                parent::__construct();

                
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

                
                $waterPit = new WaterPit();
                $waterPit->setBaseAmount(0);
                $waterPit->setRandomAmount(1);

                $this->addPopulator($tallGrass);
                $this->addPopulator($sugarcane);
                $this->addPopulator($waterPit);

                
                $this->setGroundCover([
                        Block::get(Block::PODZOL, 0),
                        Block::get(Block::DIRT, 0),
                        Block::get(Block::DIRT, 0),
                        Block::get(Block::DIRT, 0),
                        Block::get(Block::DIRT, 0),
                ]);

                
                $this->setElevation(62, 65);

                $this->temperature = 0.05;
                $this->rainfall = 0.5;
        }

        public function getName() : string{
                return "Taiga Plains";
        }

        public function getColor(){
                return 0x4f7a5e; 
        }
}
