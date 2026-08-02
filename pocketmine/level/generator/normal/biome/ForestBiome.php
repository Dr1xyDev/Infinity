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

class ForestBiome extends GrassyBiome{

    const TYPE_NORMAL = 0;
    const TYPE_BIRCH  = 1;

    public $type;

    public function __construct($type = self::TYPE_NORMAL){
        parent::__construct();

        $this->type = $type;

        if($type === self::TYPE_BIRCH){
            /* Birch Forest puro */
            $birch = new Tree(Sapling::BIRCH);
            $birch->setBaseAmount(6);
            $birch->setRandomAmount(3);
            $this->addPopulator($birch);

            $this->setElevation(63, 85);
            $this->temperature = 0.5;
            $this->rainfall    = 0.5;
        }else{
            /*
             * Forest normal: mezcla Oak + Birch + HugeOak.
             *
             * Oak normal (mayoría) — noBigTree=false permite que
             * growTree() genere BigTree con probabilidad 1/20.
             */
            $oak = new Tree(Sapling::OAK);
            $oak->setBaseAmount(5);
            $oak->setRandomAmount(3);
            $this->addPopulator($oak);

            /* Birch salpicados dentro del Forest */
            $birch = new Tree(Sapling::BIRCH);
            $birch->setBaseAmount(2);
            $birch->setRandomAmount(2);
            $this->addPopulator($birch);

            $tallGrass = new TallGrass();
            $tallGrass->setBaseAmount(4);
            $this->addPopulator($tallGrass);

            /*
             * Elevación más amplia para que el bioma se genere
             * en zonas de colinas suaves y sea dominante/amplio.
             */
            $this->setElevation(63, 90);
            $this->temperature = 0.70;
            $this->rainfall    = 0.80;
        }
    }

    public function getName() : string{
        return $this->type === self::TYPE_BIRCH ? "Birch Forest" : "Forest";
    }
}
