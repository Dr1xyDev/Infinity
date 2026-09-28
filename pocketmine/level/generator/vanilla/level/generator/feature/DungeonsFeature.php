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
use pocketmine\level\generator\vanilla\entity\EntityIds;
use pocketmine\level\generator\vanilla\inventory\random\WeightedRandomChestContent;
use pocketmine\level\generator\vanilla\item\enchantment\EnchantmentHelper;
use pocketmine\level\generator\vanilla\item\ItemFactory;
use pocketmine\level\generator\vanilla\item\ItemIds;
use pocketmine\level\generator\vanilla\math\Facing;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\tile\Chest;
use pocketmine\level\generator\vanilla\tile\MobSpawner;
use pocketmine\level\generator\vanilla\tile\Tile;
use function count;

class DungeonsFeature extends Feature {

	private const array SPAWNER_TYPES = [
		EntityIds::SKELETON,
		EntityIds::ZOMBIE,
		EntityIds::ZOMBIE,
		EntityIds::SPIDER
	];

	/** @var WeightedRandomChestContent[] */
	private array $chestContents;

	public function __construct(){
		$this->chestContents = [
			new WeightedRandomChestContent(ItemFactory::get(ItemIds::SADDLE), 1, 1, 10),
			new WeightedRandomChestContent(ItemFactory::get(ItemIds::IRON_INGOT), 1, 4, 10),
			new WeightedRandomChestContent(ItemFactory::get(ItemIds::BREAD), 1, 1, 10),
			new WeightedRandomChestContent(ItemFactory::get(ItemIds::WHEAT), 1, 4, 10),
			new WeightedRandomChestContent(ItemFactory::get(ItemIds::GUNPOWDER), 1, 4, 10),
			new WeightedRandomChestContent(ItemFactory::get(ItemIds::STRING), 1, 4, 10),
			new WeightedRandomChestContent(ItemFactory::get(ItemIds::BUCKET), 1, 1, 10),
			new WeightedRandomChestContent(ItemFactory::get(ItemIds::GOLDEN_APPLE), 1, 1, 1),
			new WeightedRandomChestContent(ItemFactory::get(ItemIds::REDSTONE), 1, 4, 10),
			new WeightedRandomChestContent(ItemFactory::get(ItemIds::RECORD_13), 1, 1, 4),
			new WeightedRandomChestContent(ItemFactory::get(ItemIds::RECORD_CAT), 1, 1, 4),
			new WeightedRandomChestContent(ItemFactory::get(ItemIds::NAME_TAG), 1, 1, 10),
			new WeightedRandomChestContent(ItemFactory::get(ItemIds::GOLDEN_HORSE_ARMOR), 0, 1, 1, 2),
			new WeightedRandomChestContent(ItemFactory::get(ItemIds::IRON_HORSE_ARMOR), 0, 1, 1, 5),
			new WeightedRandomChestContent(ItemFactory::get(ItemIds::DIAMOND_HORSE_ARMOR), 0, 1, 1, 1)
		];
	}

	public function place(FeaturePlaceContext $context) : bool {
		$level = $context->level();
		$pos = $context->origin();
		$random = $context->random();

		$radiusX = $random->nextRange(2, 3);
		$minX = -$radiusX - 1;
		$maxX = $radiusX + 1;

		$radiusZ = $random->nextRange(2, 3);
		$minZ = -$radiusZ - 1;
		$maxZ = $radiusZ + 1;

		$airCount = 0;

		for($x = $minX; $x <= $maxX; ++$x){
			for($y = -1; $y <= 4; ++$y){
				for($z = $minZ; $z <= $maxZ; ++$z){
					$targetPos = $pos->add($x, $y, $z);
					$material = $level->getBlockAt($targetPos->x, $targetPos->y, $targetPos->z);
					$isSolid = $material->isSolid();

					if($y === -1 && !$isSolid){
						return false;
					}

					if($y === 4 && !$isSolid){
						return false;
					}

					if(($x === $minX || $x === $maxX || $z === $minZ || $z === $maxZ) && $y === 0 && $level->getBlockAt($targetPos->x, $targetPos->y, $targetPos->z)->getId() === BlockIds::AIR && $level->getBlockAt($targetPos->x, $targetPos->y + 1, $targetPos->z)->getId() === BlockIds::AIR){
						$airCount++;
					}
				}
			}
		}

		if($airCount >= 1 && $airCount <= 5){
			for($x = $minX; $x <= $maxX; ++$x){
				for($y = 3; $y >= -1; --$y){
					for($z = $minZ; $z <= $maxZ; ++$z){
						$targetPos = $pos->add($x, $y, $z);
						$block = $level->getBlockAt($targetPos->x, $targetPos->y, $targetPos->z);
						$blockId = $block->getId();

						if($x !== $minX && $y !== -1 && $z !== $minZ && $x !== $maxX && $y !== 4 && $z !== $maxZ){
							if($blockId !== BlockIds::CHEST && $blockId !== BlockIds::MONSTER_SPAWNER){
								$this->setBlock($level, $targetPos, BlockFactory::get(BlockIds::AIR));
							}
						}elseif($targetPos->y >= 0 && !$level->getBlockAt($targetPos->x, $targetPos->y - 1, $targetPos->z)->isSolid()){
							$this->setBlock($level, $targetPos, BlockFactory::get(BlockIds::AIR));
						}elseif($block->isSolid() && $blockId !== BlockIds::CHEST){
							if($y === -1 && $random->nextRange(0, 3) !== 0){
								$this->setBlock($level, $targetPos, BlockFactory::get(BlockIds::MOSSY_COBBLESTONE));
							}else{
								$this->setBlock($level, $targetPos, BlockFactory::get(BlockIds::COBBLESTONE));
							}
						}
					}
				}
			}

			for($i = 0; $i < 2; ++$i){
				for($attempt = 0; $attempt < 3; ++$attempt){
					$chestX = $pos->x + $random->nextRange(0, $radiusX * 2) - $radiusX;
					$chestY = $pos->y;
					$chestZ = $pos->z + $random->nextRange(0, $radiusZ * 2) - $radiusZ;

					if($level->getBlockAt($chestX, $chestY, $chestZ)->getId() === BlockIds::AIR){
						$solidNeighbors = 0;
						if($level->getBlockAt($chestX + 1, $chestY, $chestZ)->isSolid()) $solidNeighbors++;
						if($level->getBlockAt($chestX - 1, $chestY, $chestZ)->isSolid()) $solidNeighbors++;
						if($level->getBlockAt($chestX, $chestY, $chestZ + 1)->isSolid()) $solidNeighbors++;
						if($level->getBlockAt($chestX, $chestY, $chestZ - 1)->isSolid()) $solidNeighbors++;

						if($solidNeighbors === 1){
							$meta = $this->getChestFacingDataId($level, $chestX, $chestY, $chestZ, $chestX - $pos->x, $chestZ - $pos->z);

							$this->setBlock($level, new Vector3($chestX, $chestY, $chestZ), BlockFactory::get(BlockIds::CHEST, $meta));
							$chest = Tile::createTile(Tile::CHEST, $level, Chest::createNBT(new Vector3($chestX, $chestY, $chestZ)));
							if($chest instanceof Chest) {
								$contents = $this->chestContents;
								$contents[] = new WeightedRandomChestContent(EnchantmentHelper::addRandomEnchantment($random, ItemFactory::get(ItemIds::BOOK), 30), 1, 1, 1);
								WeightedRandomChestContent::generateChestContents($random, $contents, $chest->getRealInventory(), 8);
							}

							break;
						}
					}
				}
			}

			$this->setBlock($level, $pos, BlockFactory::get(BlockIds::MONSTER_SPAWNER));
			$spawner = Tile::createTile(Tile::MOB_SPAWNER, $level, MobSpawner::createNBT($pos));
			if($spawner instanceof MobSpawner) {
				$spawner->setEntityId($this->getRandomDungeonMob($random));
			}

			return true;
		}

		return false;
	}

	private function getRandomDungeonMob($random) : int {
		return self::SPAWNER_TYPES[$random->nextRange(0, count(self::SPAWNER_TYPES) - 1)];
	}

	private function getChestFacingDataId($level, int $x, int $y, int $z, int $offsetX, int $offsetZ) : int{
		if($offsetZ > 0 && $level->getBlockAt($x, $y, $z + 1)->getId() !== BlockIds::AIR){
			return Facing::NORTH;
		}
		if($offsetZ < 0 && $level->getBlockAt($x, $y, $z - 1)->getId() !== BlockIds::AIR){
			return Facing::SOUTH;
		}
		if($offsetX > 0 && $level->getBlockAt($x + 1, $y, $z)->getId() !== BlockIds::AIR){
			return Facing::WEST;
		}
		if($offsetX < 0 && $level->getBlockAt($x - 1, $y, $z)->getId() !== BlockIds::AIR){
			return Facing::EAST;
		}
		if($offsetZ > 0){
			return Facing::NORTH;
		}
		if($offsetZ < 0){
			return Facing::SOUTH;
		}
		if($offsetX > 0){
			return Facing::WEST;
		}
		if($offsetX < 0){
			return Facing::EAST;
		}

		return Facing::NORTH;
	}
}
