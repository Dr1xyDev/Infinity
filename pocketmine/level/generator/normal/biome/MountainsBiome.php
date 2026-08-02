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
use pocketmine\level\generator\populator\Tree;

class MountainsBiome extends GrassyBiome{

    public function __construct(){
        parent::__construct();

        $this->setGroundCover([]);

        $trees = new Tree(Sapling::SPRUCE);
        $trees->setBaseAmount(1);
        $trees->setRandomAmount(2);
        $this->addPopulator($trees);

        $this->setElevation(63, 145);

        $this->temperature = 0.15;
        $this->rainfall    = 0.90;
    }

    public function getName() : string{
        return "Mountains";
    }
}
