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
use pocketmine\level\generator\vanilla\entity\Entity;
use pocketmine\level\generator\vanilla\entity\object\EnderCrystal;
use pocketmine\level\generator\vanilla\level\ChunkManager;
use pocketmine\level\generator\vanilla\level\format\Chunk;
use pocketmine\level\generator\vanilla\level\generator\feature\configurations\SpikeConfiguration;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\utils\Utils;
use function abs;
use function cos;
use function count;
use function floor;
use function range;
use function sin;
use const M_PI;

class EndSpikeFeature extends Feature {

	public function __construct(
		public SpikeConfiguration $config
	){}

	public function place(FeaturePlaceContext $context) : bool{
		$level = $context->level();
		$origin = $context->origin();
		$config = $this->config;

		$chunkX = $origin->getFloorX() >> Chunk::COORD_BIT_SIZE;
		$chunkZ = $origin->getFloorZ() >> Chunk::COORD_BIT_SIZE;

		$list = $config->spikes;
		if (count($list) === 0) {
			$intArrayList = Utils::shuffledCopy(range(0, 9), $context->random());
			for ($i = 0; $i < 10; $i++) {
				$x = (int) floor(42.0 * cos(2.0 * (-M_PI + (M_PI / 10) * $i)));
				$z = (int) floor(42.0 * sin(2.0 * (-M_PI + (M_PI / 10) * $i)));
				$size = $intArrayList[$i];
				$radius = 2 + $size / 3;
				$height = 76 + $size * 3;
				$guarded = $size == 1 || $size == 2;

				$list[] = new EndSpike($x, $z, (int) $radius, (int) ($height + 10), $guarded);
			}

			$config->spikes = $list;
		}

		foreach ($list as $spike) {
			if($spike->centerX >> Chunk::COORD_BIT_SIZE === $chunkX && $spike->centerZ >> Chunk::COORD_BIT_SIZE === $chunkZ){
				$this->placeSpike($level, $spike->centerX, $spike->centerZ, $spike->radius, $spike->height, $spike->guarded);
			}
		}

		return true;
	}

	public static function placeSpike(ChunkManager $level, int $centerX, int $centerZ, int $radius, int $height, bool $guarded) : void{
		$minX = $centerX - $radius;
		$minY = 0;
		$minZ = $centerZ - $radius;
		$maxX = $centerX + $radius;
		$maxY = $height;
		$maxZ = $centerZ + $radius;

		$obsidian = BlockFactory::get(BlockIds::OBSIDIAN);
		for ($x = $minX; $x <= $maxX; ++$x) {
			for ($y = $minY; $y <= $maxY; ++$y) {
				for ($z = $minZ; $z <= $maxZ; ++$z) {
					$dx = $x - $centerX;
					$dz = $z - $centerZ;
					$distSq = $dx * $dx + $dz * $dz;
					if ($distSq <= $radius * $radius + 1 && $y < $height) {
						$level->setBlockAt($x, $y, $z, $obsidian);
					}
				}
			}
		}

		if ($guarded) {
			$ironBars = BlockFactory::get(BlockIds::IRON_BARS);
			for ($x = -2; $x <= 2; ++$x) {
				for ($z = -2; $z <= 2; ++$z) {
					for ($y = 0; $y <= 3; ++$y) {
						if ((abs($x) === 2) || (abs($z) === 2) || ($y === 3)) {
							$level->setBlockAt($centerX + $x, $height + $y, $centerZ + $z, $ironBars);
						}
					}
				}
			}
		}

		$level->setBlockAt($centerX, $height, $centerZ, BlockFactory::get(BlockIds::BEDROCK));
		$level->setBlockAt($centerX, $height + 1, $centerZ, BlockFactory::get(BlockIds::FIRE));

		$entity = Entity::createEntity("EnderCrystal", $level, Entity::createBaseNBT(new Vector3($centerX + 0.5, $height + 1, $centerZ + 0.5)));
		if ($entity instanceof EnderCrystal) {
			$entity->setShowBase(true);
		}
	}
}
