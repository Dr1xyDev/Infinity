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
use pocketmine\level\generator\populator\Tree;
use pocketmine\level\generator\normal\populator\SavannaCanyon;

class SavannaBiome extends GrassyBiome{

        public function __construct(){
                parent::__construct();

                
                $trees = new Tree(Sapling::ACACIA);
                $trees->setBaseAmount(2);
                $trees->setRandomAmount(1);
                $this->addPopulator($trees);

                
                $tallGrass = new TallGrass();
                $tallGrass->setBaseAmount(10);
                $tallGrass->setRandomAmount(5);
                $this->addPopulator($tallGrass);

                
                
                
                
                

                
                
                
                
                $this->setElevation(62, 66);

                
                $this->temperature = 1.2;
                $this->rainfall = 0.05;
        }

        public function getName() : string{
                return "Savanna";
        }
}
