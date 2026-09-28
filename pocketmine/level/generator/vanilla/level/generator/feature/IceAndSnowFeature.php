<?php

/*
 *
 *   _____       _                          _
 *  / ____|     | |                        (_)
 * | (___  _   _| |__  _ __ ___   __ _ _ __ _ _ __   ___
 *  \___ \| | | | '_ \| '_ ` _ \ / _` | '__| | '_ \ / _ \
 *  ____) | |_| | |_) | | | | | | (_| | |  | | | | |  __/
 * |_____/ \__,_|_.__/|_| |_| |_|\__,_|_|  |_|_| |_|\___|
 *
 * This program is private software. No license required.
 * Publication of this program is forbidden and will be punished.
 *
 * @author SEMENNEJO
 * @link vk.com/vk.snikers && t.me/semennejo
 *
 *
 */

declare(strict_types=1);

namespace pocketmine\level\generator\vanilla\level\generator\feature;

use pocketmine\level\generator\vanilla\block\BlockFactory;
use pocketmine\level\generator\vanilla\block\BlockIds;
use pocketmine\level\generator\vanilla\level\biome\BiomeFactory;
use pocketmine\level\generator\vanilla\level\format\Chunk;
use pocketmine\level\generator\vanilla\level\generator\placement\HeightmantType;
use pocketmine\level\generator\vanilla\math\Vector3;

class IceAndSnowFeature extends Feature {

	public function place(FeaturePlaceContext $context) : bool{
		$level = $context->level();
		$origin = $context->origin();

		$biomeFactory = BiomeFactory::getInstance();
		$chunk = $level->getChunk($origin->getX() >> Chunk::COORD_BIT_SIZE, $origin->getZ() >> Chunk::COORD_BIT_SIZE);

		$ice = BlockFactory::get(BlockIds::ICE);
		$snowLayer = BlockFactory::get(BlockIds::SNOW_LAYER);
		for ($dx = 0; $dx < 16; $dx++) {
			for ($dz = 0; $dz < 16; $dz++) {
				$x = $origin->getX() + $dx;
				$z = $origin->getZ() + $dz;
				$y = HeightmantType::MOTION->getHighestWorkableBlock($level, $x, $z);
				$belowPos = (new Vector3($x, $y, $z))->down();

				$biome = $biomeFactory->get($chunk->getBiomeId($dx, $dz));
				if ($biome->doesWaterFreeze($level, $belowPos, false)) {
					$level->setBlockAt($belowPos->getFloorX(), $belowPos->getFloorY(), $belowPos->getFloorZ(), $ice);
				}

				if ($biome->doesSnowGenerate($level, new Vector3($x, $y, $z))) {
					$level->setBlockAt($x, $y, $z, $snowLayer);
				}
			}
		}

		return true;
	}
}
