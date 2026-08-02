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

use pocketmine\block\Block;
use pocketmine\level\generator\populator\Cactus;
use pocketmine\level\generator\populator\DeadBush;

class DesertPlainsBiome extends SandyBiome{

        public function __construct(){
                parent::__construct();

                
                
                $this->clearPopulators();

                $cactus = new Cactus();
                $cactus->setBaseAmount(2);
                $cactus->setRandomAmount(2);

                $deadBush = new DeadBush();
                $deadBush->setBaseAmount(1);
                $deadBush->setRandomAmount(1);

                $this->addPopulator($cactus);
                $this->addPopulator($deadBush);

                
                $this->setElevation(62, 65);

                $this->temperature = 2;
                $this->rainfall = 0;
        }

        public function getName() : string{
                return "Desert Plains";
        }

        public function getColor(){
                return 0xe6d49b; 
        }
}
