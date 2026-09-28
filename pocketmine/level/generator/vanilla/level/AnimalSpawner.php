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

namespace pocketmine\level\generator\vanilla\level;

use Exception;
use pocketmine\level\generator\vanilla\block\Block;
use pocketmine\level\generator\vanilla\block\Leaves;
use pocketmine\level\generator\vanilla\block\Liquid;
use pocketmine\level\generator\vanilla\block\Water;
use pocketmine\level\generator\vanilla\entity\Animal;
use pocketmine\level\generator\vanilla\entity\Creature;
use pocketmine\level\generator\vanilla\entity\CreatureType;
use pocketmine\level\generator\vanilla\entity\Entity;
use pocketmine\level\generator\vanilla\entity\Living;
use pocketmine\level\generator\vanilla\entity\Mob;
use pocketmine\level\generator\vanilla\entity\Monster;
use pocketmine\level\generator\vanilla\entity\SpawnPlacementTypes;
use pocketmine\level\generator\vanilla\entity\WaterAnimal;
use pocketmine\level\generator\vanilla\level\biome\Biome;
use pocketmine\level\generator\vanilla\level\biome\SpawnListEntry;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\Player;
use pocketmine\level\generator\vanilla\utils\Random;
use pocketmine\level\generator\vanilla\utils\WeightedRandomItem;
use function array_filter;
use function count;
use function is_a;

class AnimalSpawner
{
	public const MAX_MOBS = 289;

	/** @var CreatureType[] */
	public static $creatureTypes = [];

	public function __construct()
	{
		self::$creatureTypes[Monster::class] = new CreatureType(Monster::class, 70, Block::AIR, false);
		self::$creatureTypes[Animal::class] = new CreatureType(Animal::class, 10, Block::AIR, true);
		self::$creatureTypes[Creature::class] = new CreatureType(Creature::class, 15, Block::AIR, false);
		self::$creatureTypes[WaterAnimal::class] = new CreatureType(WaterAnimal::class, 5, Block::STILL_WATER, false);
	}

	public function findChunksForSpawning(Level $level, bool $spawnHostileMobs, bool $spawnPeacefulMobs, array $eligibleChunks) : void
	{
		if ($spawnHostileMobs || $spawnPeacefulMobs) {
			$spawn = $level->getSpawnLocation();

			foreach (self::$creatureTypes as $creatureType) {
				if ((!$creatureType->isPeacefulCreature() || $spawnPeacefulMobs) && ($creatureType->isPeacefulCreature() || $spawnHostileMobs) && ($creatureType->getCreatureClass() !== Animal::class || $level->isDayTime())) {
					$a = $creatureType->getCreatureClass();
					$j4 = count(array_filter($level->getEntities(), function (Entity $entity) use ($a) {
						return is_a($entity, $a);
					}));
					$k4 = $creatureType->getMaxSpawn() * count($eligibleChunks) / self::MAX_MOBS;

					if ($j4 <= $k4) {
						foreach ($eligibleChunks as $chunkHash) {
							Level::getXZ($chunkHash, $cx, $cz);
							if ($level->getChunk($cx, $cz, false) === null) {
								continue;
							}
							if (count($level->getChunkEntities($cx, $cz)) === 0) {

								$pos = $level->getSafeSpawn(self::getRandomChunkPosition($level, $cx, $cz));
								$block = $level->getBlock($pos);

								if (!$block->isSolid() && $level->getNearestEntity($pos, 24, Player::class) === null) {
									$j2 = 0;

									for ($k2 = 0; $k2 < 3; ++$k2) {
										$l2 = $pos->x;
										$i3 = $pos->y;
										$j3 = $pos->z;
										$entry = null;
										$s1 = $level->random->nextBoundedInt(4);

										for ($l3 = 0; $l3 < $s1; ++$l3) {
											$l2 += $level->random->nextBoundedInt(6) - $level->random->nextBoundedInt(6);
											$i3 += $level->random->nextBoundedInt(1) - $level->random->nextBoundedInt(1);
											$j3 += $level->random->nextBoundedInt(6) - $level->random->nextBoundedInt(6);
											$pos1 = new Vector3($l2, $i3, $j3);

											if ($pos1->distanceSquared($spawn) >= 576) {
												if ($entry === null) {
													$entry = $level->getSpawnListEntryForTypeAt($creatureType, $pos1);

													if ($entry === null) {
														break;
													}
												}

												if ($level->canCreatureTypeSpawnHere($creatureType, $entry, $pos1) && self::canCreatureTypeSpawnAtLocation($entry->entityClass::SPAWN_PLACEMENT_TYPE, $level, $pos1)) {
													$entity = null;
													try {
														$class = $entry->entityClass;
														/** @var Living $entity */
														$entity = new $class($level, Entity::createBaseNBT($pos1->add(0.5, 0, 0.5)));
													} catch (Exception $e) {
														return;
													}

													if ($entity instanceof Mob) {
														$entity->setImmobile(false);
													}

													$entity->setRotation($level->random->nextFloat() * 360, 0);

													if ($entity->canSpawnHere() && count($level->getCollidingEntities($entity->getBoundingBox(), $entity)) === 0) {
														// TODO: implement mob initial spawn
														++$j2;
														$entity->spawnToAll();

														if ($j2 >= $entity->getMaxSpawnedInChunk()) {
															continue 4;
														}
													} else {
														$entity->flagForDespawn();
													}
												}
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}
	}

	/**
	 * @return Vector3
	 */
	public static function getRandomChunkPosition(Level $level, int $x, int $z)
	{
		$i = $x * 16 + $level->random->nextBoundedInt(16);
		$j = $z * 16 + $level->random->nextBoundedInt(16);
		$k = $level->getHeightMap($i, $j) + 1;
		if ($k % 16 !== 0) {
			$k = $k + 16 - ($k % 16);
		}
		$l = $level->random->nextBoundedInt($k > 0 ? $k : 256);
		return new Vector3($i, $l, $j);
	}

	/**
	 * @return bool
	 */
	public static function canCreatureTypeSpawnAtLocation(int $spawnPlacementType, Level $level, Vector3 $pos)
	{
		$block = $level->getBlock($pos);

		if ($spawnPlacementType === SpawnPlacementTypes::PLACEMENT_TYPE_IN_WATER) {
			return $block instanceof Water && $level->getBlock($pos->down()) instanceof Water && !$level->getBlock($pos->up())->isSolid();
		} else {
			$block1 = $level->getBlock($pos->down());

			if (!$block1->isSolid()) {
				return false;
			} else {
				$flag = $block1->getId() !== Block::BEDROCK;
				return $flag && !$block->isSolid() && !($block instanceof Liquid) && !$level->getBlock($pos->up())->isSolid();
			}
		}
	}

	/**
	 * Called during chunk generation to spawn initial creatures.
	 */
	public static function performChunkGeneratorSpawning(Level $level, Biome $biome, int $sourceX, int $sourceZ, int $xRange, int $zRange, Random $random)
	{
		$list = $biome->getSpawnableList(self::$creatureTypes[Animal::class]);

		if (!empty($list)) {
			while ($random->nextFloat() < $biome->getSpawningChance()) {
				/** @var SpawnListEntry $entry */
				$entry = WeightedRandomItem::getRandomItem($random, $list, WeightedRandomItem::getTotalWeight($list) + 2);
				if ($entry === null) {
					continue;
				}

				$i = $random->nextBoundedInt($entry->minGroupCount + $random->nextBoundedInt(1 + $entry->maxGroupCount - $entry->minGroupCount));
				$j = $sourceX + $random->nextBoundedInt($xRange);
				$k = $sourceZ + $random->nextBoundedInt($zRange);
				$l = $j;
				$i1 = $k;

				for ($j1 = 0; $j1 < $i; ++$j1) {
					$flag = false;

					for ($k1 = 0; !$flag && $k1 < 4; ++$k1) {
						$pos = new Vector3($j, $level->getHeightMap($j, $k) + 1, $k);

						for (; $pos->y > 0; $pos = $pos->down()) {
							$down = $level->getBlock($pos->down());

							if (!($down instanceof Leaves) && $down->isSolid()) {
								break;
							}
						}

						if (self::canCreatureTypeSpawnAtLocation(SpawnPlacementTypes::PLACEMENT_TYPE_ON_GROUND, $level, $pos)) {
							$entity = null;

							try {
								$class = $entry->entityClass;
								/** @var Entity $entity */
								$entity = new $class($level, Entity::createBaseNBT($pos->add(0.5, 0, 0.5)));
							} catch (Exception $e) {
								continue;
							}

							if ($entity instanceof Mob) {
								$entity->setImmobile(false);
							}

							$entity->setRotation($random->nextFloat() * 360, 0);
							$entity->spawnToAll();
							// TODO: entity initial spawn
							$flag = true;
						}

						$j += $random->nextBoundedInt(5) - $random->nextBoundedInt(5);

						for ($k += $random->nextBoundedInt(5) - $random->nextBoundedInt(5); $j < $sourceX || $j >= $sourceX + $xRange || $k < $sourceZ || $k >= $sourceZ + $xRange; $k = $i1 + $random->nextBoundedInt(5) - $random->nextBoundedInt(5)) {
							$j = $l + $random->nextBoundedInt(5) - $random->nextBoundedInt(5);
						}
					}
				}
			}
		}
	}
}
