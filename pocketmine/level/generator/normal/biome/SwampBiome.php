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
use pocketmine\block\Flower as FlowerBlock;
use pocketmine\level\generator\populator\Flower;
use pocketmine\level\generator\populator\LilyPad;
use pocketmine\level\generator\normal\populator\HugeMushroomPopulator;
use pocketmine\level\generator\normal\populator\SwampTreePopulator;
use pocketmine\level\generator\normal\object\mushroom\BigMushroom;

class SwampBiome extends GrassyBiome{

	public function __construct(){
		parent::__construct();

		
		$flower = new Flower();
		$flower->setBaseAmount(8);
		$flower->addType([Block::RED_FLOWER, FlowerBlock::TYPE_BLUE_ORCHID]);
		$this->addPopulator($flower);

		
		$lilypad = new LilyPad();
		$lilypad->setBaseAmount(0);
		$this->addPopulator($lilypad);

		
		$swampTrees = new SwampTreePopulator();
		$swampTrees->setBaseAmount(2);
		$swampTrees->setRandomAmount(3);
		$this->addPopulator($swampTrees);

		
		$redMushrooms = new HugeMushroomPopulator();
		$redMushrooms->setMushroomType(BigMushroom::RED);
		$redMushrooms->setSpawnChance(0.25);
		$redMushrooms->setBaseAmount(1);
		$redMushrooms->setRandomAmount(0);
		$this->addPopulator($redMushrooms);

		
		$brownMushrooms = new HugeMushroomPopulator();
		$brownMushrooms->setMushroomType(BigMushroom::BROWN);
		$brownMushrooms->setSpawnChance(0.25);
		$brownMushrooms->setBaseAmount(1);
		$brownMushrooms->setRandomAmount(0);
		$this->addPopulator($brownMushrooms);

		$this->setElevation(62, 63);

		
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
