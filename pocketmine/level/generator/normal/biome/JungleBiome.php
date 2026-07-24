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

use pocketmine\block\Sapling;
use pocketmine\level\generator\populator\TallGrass;
use pocketmine\level\generator\populator\LilyPad;
use pocketmine\level\generator\populator\Tree;
use pocketmine\level\generator\normal\populator\JunglePit;

class JungleBiome extends GrassyBiome{

        public function __construct(){
                parent::__construct();

                // Jungle trees (uses NewJungleTree through Tree::growTree())
                // Higher base amount for wider/denser jungle canopy
                $trees = new Tree(Sapling::JUNGLE);
                $trees->setBaseAmount(12);
                $trees->setRandomAmount(6);
                $this->addPopulator($trees);

                // Tall grass for jungle floor
                $tallGrass = new TallGrass();
                $tallGrass->setBaseAmount(5);
                $tallGrass->setRandomAmount(3);
                $this->addPopulator($tallGrass);

                // Water puddles in natural terrain depressions (low spots), rare
                // Only places puddles where surrounding terrain is higher
                $waterPit = new JunglePit();
                $waterPit->setBaseAmount(1);
                $waterPit->setRandomAmount(2);
                $this->addPopulator($waterPit);

                // Lily pads on jungle water puddles (vanilla-like, rare)
                $lilyPad = new LilyPad();
                $lilyPad->setBaseAmount(2);
                $lilyPad->setRandomAmount(1);
                $this->addPopulator($lilyPad);

                $this->setElevation(63, 81);

                // Jungle biome has high temperature and high rainfall
                $this->temperature = 0.95;
                $this->rainfall = 0.90;
        }

        public function getName() : string{
                return "Jungle";
        }
}
