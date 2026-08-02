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
use pocketmine\level\generator\populator\TallGrass;
use pocketmine\level\generator\populator\WaterPit;
use pocketmine\level\generator\populator\Sugarcane;
use pocketmine\level\generator\populator\Tree;

class JunglePlainsBiome extends GrassyBiome{

        public function __construct(){
                parent::__construct();

                
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

                
                $waterPit = new WaterPit();
                $waterPit->setBaseAmount(0);
                $waterPit->setRandomAmount(1);

                $this->addPopulator($tallGrass);
                $this->addPopulator($sugarcane);
                $this->addPopulator($waterPit);

                
                $this->setElevation(62, 65);

                $this->temperature = 0.95;
                $this->rainfall = 0.7;
        }

        public function getName() : string{
                return "Jungle Plains";
        }

        public function getColor(){
                return 0x5fa84e; 
        }
}
