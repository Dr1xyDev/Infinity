<?php

/*
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█
 * ░░█░░█░█▀▀░░█░░█░█░░█░░░█░░░█░
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.0
 *               InfinityProject By @Dr1xyDev
 *   YT:         @Dr1xyDev
 *   GitHub:     github.com/Dr1xyDev/Infinity
*/

namespace pocketmine\level\generator\normal\biome;

use pocketmine\block\Block;
use pocketmine\level\generator\populator\Cactus;
use pocketmine\level\generator\populator\DeadBush;

/**
 * DesertPlains sub-biome.
 *
 * A flat, low-relief sandy plain that takes the place of Desert in the
 * wide band of terrain near rivers and seas. Acts as the natural
 * connector between Desert and water.
 *
 * Per user request:
 *  - No trees (this is a desert plain).
 *  - Few cacti and dead bushes (sparse, plains-like).
 *  - 3 blocks above sea/river level (setElevation 62, 65).
 *
 * The sandy ground cover (inherited from SandyBiome) means the river
 * bank overlay blends smoothly: sand plain -> sand beach -> river/sea,
 * with occasional gravel patches for variety.
 */
class DesertPlainsBiome extends SandyBiome{

        public function __construct(){
                parent::__construct();

                // Reset inherited populators from SandyBiome and add a
                // sparser set appropriate for a flat desert plain.
                $this->clearPopulators();

                $cactus = new Cactus();
                $cactus->setBaseAmount(2);
                $cactus->setRandomAmount(2);

                $deadBush = new DeadBush();
                $deadBush->setBaseAmount(1);
                $deadBush->setRandomAmount(1);

                $this->addPopulator($cactus);
                $this->addPopulator($deadBush);

                // 3 blocks above sea level - flat plains at river/sea height.
                $this->setElevation(62, 65);

                $this->temperature = 2;
                $this->rainfall = 0;
        }

        public function getName() : string{
                return "Desert Plains";
        }

        public function getColor(){
                return 0xe6d49b; // Pale desert sand
        }
}
