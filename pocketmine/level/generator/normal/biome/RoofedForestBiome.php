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

use pocketmine\block\Block;
use pocketmine\block\Sapling;
use pocketmine\level\generator\populator\TallGrass;
use pocketmine\level\generator\populator\Tree;
use pocketmine\level\generator\normal\populator\HugeMushroomPopulator;
use pocketmine\level\generator\normal\object\mushroom\BigMushroom;

class RoofedForestBiome extends GrassyBiome{

        public function __construct(){
                parent::__construct();

                // Dense dark oak trees (roofed forest is characterized by many dark oaks)
                $trees = new Tree(Sapling::DARK_OAK);
                $trees->setBaseAmount(8);
                $trees->setRandomAmount(4);
                $this->addPopulator($trees);

                // Some regular oak trees mixed in (vanilla roofed forest has occasional oaks)
                // Amount raised ~30% per user request (was base 1 + random 2, avg 2.5).
                $oakTrees = new Tree(Sapling::OAK);
                $oakTrees->setBaseAmount(1);
                $oakTrees->setRandomAmount(3);
                $this->addPopulator($oakTrees);

                // Huge RED mushrooms — guaranteed 2 per chunk, per user request
                $redMushrooms = new HugeMushroomPopulator();
                $redMushrooms->setMushroomType(BigMushroom::RED);
                $redMushrooms->setSpawnChance(1.0);
                $redMushrooms->setBaseAmount(2);
                $redMushrooms->setRandomAmount(0);
                $this->addPopulator($redMushrooms);

                // Huge BROWN mushrooms — guaranteed 2 per chunk, per user request
                $brownMushrooms = new HugeMushroomPopulator();
                $brownMushrooms->setMushroomType(BigMushroom::BROWN);
                $brownMushrooms->setSpawnChance(1.0);
                $brownMushrooms->setBaseAmount(2);
                $brownMushrooms->setRandomAmount(0);
                $this->addPopulator($brownMushrooms);

                // Tall grass on forest floor
                $tallGrass = new TallGrass();
                $tallGrass->setBaseAmount(3);
                $tallGrass->setRandomAmount(2);
                $this->addPopulator($tallGrass);

                // Soft, plains-like terrain: a narrow elevation range keeps the
                // noise-driven terrain gentle and rolling instead of the rugged
                // hills the old wide range (64-85, 21 blocks) produced.
                $this->setElevation(64, 74);

                // Roofed forest: moderate-cool temperature, high rainfall
                $this->temperature = 0.7;
                $this->rainfall = 0.8;
        }

        public function getName(): string{
                return "Roofed Forest";
        }

        public function getColor(){
                return 0x283e1e; // Dark green for roofed forest
        }
}
