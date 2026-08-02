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
use pocketmine\block\Sapling;
use pocketmine\level\generator\populator\TallGrass;
use pocketmine\level\generator\populator\Tree;
use pocketmine\level\generator\normal\populator\HugeMushroomPopulator;
use pocketmine\level\generator\normal\object\mushroom\BigMushroom;

class RoofedForestBiome extends GrassyBiome{

        public function __construct(){
                parent::__construct();

                
                $trees = new Tree(Sapling::DARK_OAK);
                $trees->setBaseAmount(8);
                $trees->setRandomAmount(4);
                $this->addPopulator($trees);

                
                
                $oakTrees = new Tree(Sapling::OAK);
                $oakTrees->setBaseAmount(1);
                $oakTrees->setRandomAmount(3);
                $this->addPopulator($oakTrees);

                
                $redMushrooms = new HugeMushroomPopulator();
                $redMushrooms->setMushroomType(BigMushroom::RED);
                $redMushrooms->setSpawnChance(1.0);
                $redMushrooms->setBaseAmount(2);
                $redMushrooms->setRandomAmount(0);
                $this->addPopulator($redMushrooms);

                
                $brownMushrooms = new HugeMushroomPopulator();
                $brownMushrooms->setMushroomType(BigMushroom::BROWN);
                $brownMushrooms->setSpawnChance(1.0);
                $brownMushrooms->setBaseAmount(2);
                $brownMushrooms->setRandomAmount(0);
                $this->addPopulator($brownMushrooms);

                
                $tallGrass = new TallGrass();
                $tallGrass->setBaseAmount(3);
                $tallGrass->setRandomAmount(2);
                $this->addPopulator($tallGrass);

                
                
                
                $this->setElevation(64, 74);

                
                $this->temperature = 0.7;
                $this->rainfall = 0.8;
        }

        public function getName(): string{
                return "Roofed Forest";
        }

        public function getColor(){
                return 0x283e1e; 
        }
}
