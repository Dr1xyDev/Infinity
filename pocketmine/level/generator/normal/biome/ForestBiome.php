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
use pocketmine\level\generator\populator\Tree;

class ForestBiome extends GrassyBiome{

	const TYPE_NORMAL = 0;
	const TYPE_BIRCH = 1;

	public $type;

	public function __construct($type = self::TYPE_NORMAL){
		parent::__construct();

		$this->type = $type;

		$trees = new Tree($type === self::TYPE_BIRCH ? Sapling::BIRCH : Sapling::OAK);
		if($type === self::TYPE_BIRCH){
			$trees->setBaseAmount(5);
		}else{
			// Fewer oak trees than before (was a flat 5) - a more open,
			// gently spaced oak Forest instead of a dense treeline.
			$trees->setBaseAmount(2);
			$trees->setRandomAmount(1);
		}
		$this->addPopulator($trees);

		$tallGrass = new TallGrass();
		$tallGrass->setBaseAmount(3);

		$this->addPopulator($tallGrass);

		if($type === self::TYPE_BIRCH){
			$this->setElevation(63, 81);
			$this->temperature = 0.5;
			$this->rainfall = 0.5;
		}else{
			// Soft terrain like Savanna: only 3 blocks above river/water
			// level (62), so it connects seamlessly with rivers and stays
			// gentle - any taller relief now comes from the Hills
			// sub-biome instead of Forest's own terrain noise.
			$this->setElevation(62, 65);
			$this->temperature = 0.7;
			$this->rainfall = 0.8;
		}
	}

	public function getName() : string{
		return $this->type === self::TYPE_BIRCH ? "Birch Forest" : "Forest";
	}
}