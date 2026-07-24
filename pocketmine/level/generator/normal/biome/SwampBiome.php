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
use pocketmine\block\Flower as FlowerBlock;
use pocketmine\level\generator\populator\Flower;
use pocketmine\level\generator\populator\LilyPad;
use pocketmine\level\generator\normal\populator\HugeMushroomPopulator;
use pocketmine\level\generator\normal\populator\SwampTreePopulator;
use pocketmine\level\generator\normal\object\mushroom\BigMushroom;

class SwampBiome extends GrassyBiome{

	public function __construct(){
		parent::__construct();

		// Blue orchids — swamp signature flower
		$flower = new Flower();
		$flower->setBaseAmount(8);
		$flower->addType([Block::RED_FLOWER, FlowerBlock::TYPE_BLUE_ORCHID]);
		$this->addPopulator($flower);

		// Lily pads on water
		$lilypad = new LilyPad();
		$lilypad->setBaseAmount(0);
		$this->addPopulator($lilypad);

		// Swamp oak trees with hanging vines
		$swampTrees = new SwampTreePopulator();
		$swampTrees->setBaseAmount(2);
		$swampTrees->setRandomAmount(3);
		$this->addPopulator($swampTrees);

		// Huge RED mushrooms — 25% per-chunk chance
		$redMushrooms = new HugeMushroomPopulator();
		$redMushrooms->setMushroomType(BigMushroom::RED);
		$redMushrooms->setSpawnChance(0.25);
		$redMushrooms->setBaseAmount(1);
		$redMushrooms->setRandomAmount(0);
		$this->addPopulator($redMushrooms);

		// Huge BROWN mushrooms — 25% per-chunk chance
		$brownMushrooms = new HugeMushroomPopulator();
		$brownMushrooms->setMushroomType(BigMushroom::BROWN);
		$brownMushrooms->setSpawnChance(0.25);
		$brownMushrooms->setBaseAmount(1);
		$brownMushrooms->setRandomAmount(0);
		$this->addPopulator($brownMushrooms);

		$this->setElevation(62, 63);

		// High rainfall + warm temperature → expanded biome range in selector
		$this->temperature = 0.8;
		$this->rainfall = 0.9;
	}

	public function getName(): string{
		return "Swamp";
	}

	public function getColor(){
		return 0x6a7039;
	}
}
