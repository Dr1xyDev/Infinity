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

namespace pocketmine\level\generator\vanilla\tile;

use pocketmine\level\generator\vanilla\block\Block;
use pocketmine\level\generator\vanilla\block\Liquid;
use pocketmine\level\generator\vanilla\entity\Entity;
use pocketmine\level\generator\vanilla\entity\Mob;
use pocketmine\level\generator\vanilla\entity\Monster;
use pocketmine\level\generator\vanilla\level\Level;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\nbt\tag\CompoundTag;
use pocketmine\Player;
use pocketmine\Server;

use function constant;
use function defined;
use function intval;
use function rand;
use function str_replace;
use function strtoupper;

class MobSpawner extends Spawnable
{
	// TODO: Use more nbt tags for more customization
	public const TAG_IS_MOVABLE = "isMovable";
	public const TAG_DELAY = "Delay";
	public const TAG_MAX_NEARBY_ENTITIES = "MaxNearbyEntities";
	public const TAG_MAX_SPAWN_DELAY = "MaxSpawnDelay";
	public const TAG_MIN_SPAWN_DELAY = "MinSpawnDelay";
	public const TAG_REQUIRED_PLAYER_RANGE = "RequiredPlayerRange";
	public const TAG_SPAWN_COUNT = "SpawnCount";
	public const TAG_SPAWN_RANGE = "SpawnRange";
	public const TAG_ENTITY_ID = "EntityId";
	public const TAG_DISPLAY_ENTITY_HEIGHT = "DisplayEntityHeight";
	public const TAG_DISPLAY_ENTITY_SCALE = "DisplayEntityScale";
	public const TAG_DISPLAY_ENTITY_WIDTH = "DisplayEntityWidth";
	public const TAG_SPAWN_DATA = "SpawnData"; //TODO
	public const TAG_MIN_SPAWN_COUNT = "MinimumSpawnerCount";
	public const TAG_MAX_SPAWN_COUNT = "MaximumSpawnerCount";

	/** @var int */
	protected $entityId = -1;
	/** @var int */
	protected $spawnRange = 4;
	/** @var int */
	protected $maxNearbyEntities = 16;
	/** @var int */
	protected $requiredPlayerRange = 16;
	/** @var int */
	protected $delay = 0;
	/** @var int */
	protected $minSpawnDelay = 200;
	/** @var int */
	protected $maxSpawnDelay = 5000;
	/** @var int */
	protected $minSpawnCount = 1;
	/** @var int */
	protected $maxSpawnCount = 4;
	/** @var bool */
	protected $isMovable = true;

	public function getEntityId() : int
	{
		return $this->entityId;
	}

	public function setEntityId(int $entityId) : void
	{
		$this->entityId = $entityId;
		$this->delay = 0;
		$this->onChanged();
	}

	public function getSpawnRange() : int
	{
		return $this->spawnRange;
	}

	public function setSpawnRange(int $spawnRange) : void
	{
		$this->spawnRange = $spawnRange;
		$this->onChanged();
	}

	public function getMaxNearbyEntities() : int
	{
		return $this->maxNearbyEntities;
	}

	public function setMaxNearbyEntities(int $maxNearbyEntities) : void
	{
		$this->maxNearbyEntities = $maxNearbyEntities;
		$this->onChanged();
	}

	public function getRequiredPlayerRange() : int
	{
		return $this->requiredPlayerRange;
	}

	public function setRequiredPlayerRange(int $requiredPlayerRange) : void
	{
		$this->requiredPlayerRange = $requiredPlayerRange;
		$this->onChanged();
	}

	public function getDelay() : int
	{
		return $this->delay;
	}

	public function setDelay(int $delay) : void
	{
		$this->delay = $delay;
		$this->onChanged();
	}

	public function getMinSpawnDelay() : int
	{
		return $this->minSpawnDelay;
	}

	public function setMinSpawnDelay(int $minSpawnDelay) : void
	{
		if ($minSpawnDelay > $this->maxSpawnDelay) {
			return;
		}
		$this->minSpawnDelay = $minSpawnDelay;
		$this->onChanged();
	}

	public function getMaxSpawnDelay() : int
	{
		return $this->maxSpawnDelay;
	}

	public function setMaxSpawnDelay(int $maxSpawnDelay) : void
	{
		if ($this->minSpawnDelay > $maxSpawnDelay) {
			return;
		}
		$this->maxSpawnDelay = $maxSpawnDelay;
		$this->onChanged();
	}

	public function setSpawnDelay(int $minDelay, int $maxDelay) : void
	{
		if ($minDelay > $maxDelay) {
			return;
		}
		$this->minSpawnDelay = $minDelay;
		$this->maxSpawnDelay = $maxDelay;
		$this->onChanged();
	}

	public function getMinSpawnCount() : int
	{
		return $this->minSpawnCount;
	}

	public function getMaxSpawnCount() : int
	{
		return $this->maxSpawnCount;
	}

	public function setMinSpawnCount(int $minSpawnCount) : void
	{
		$this->minSpawnCount = $minSpawnCount;
		$this->onChanged();
	}

	public function setMaxSpawnCount(int $maxSpawnCount) : void
	{
		$this->maxSpawnCount = $maxSpawnCount;
		$this->onChanged();
	}

	public function isMovable() : bool
	{
		return $this->isMovable;
	}

	public function setMovable(bool $isMovable) : void
	{
		$this->isMovable = $isMovable;
		$this->onChanged();
	}

	protected function readSaveData(CompoundTag $nbt) : void
	{
		$this->delay = $nbt->getShort(self::TAG_DELAY, 0, true);
		$this->maxNearbyEntities = $nbt->getShort(self::TAG_MAX_NEARBY_ENTITIES, 16, true);
		$this->maxSpawnDelay = $nbt->getShort(self::TAG_MAX_SPAWN_DELAY, 5000, true);
		$this->minSpawnDelay = $nbt->getShort(self::TAG_MIN_SPAWN_DELAY, 200, true);
		$this->requiredPlayerRange = $nbt->getShort(self::TAG_REQUIRED_PLAYER_RANGE, 16, true);
		$this->minSpawnCount = $nbt->getShort(self::TAG_MIN_SPAWN_COUNT, 1, true);
		$this->maxSpawnCount = $nbt->getShort(self::TAG_MAX_SPAWN_COUNT, 4, true);
		$this->spawnRange = $nbt->getShort(self::TAG_SPAWN_RANGE, 4, true);
		$this->entityId = $nbt->getInt(self::TAG_ENTITY_ID, -1, true);

		if ($this->entityId === -1 || $this->entityId === 0) {
			$stringId = $nbt->getString("EntityIdentifier", "");
			if ($stringId !== "") {
				$stringId = strtoupper(str_replace("minecraft:", "", $stringId));
				if (defined(\pocketmine\level\generator\vanilla\entity\EntityIds::class . "::" . $stringId)) {
					$this->entityId = constant(\pocketmine\level\generator\vanilla\entity\EntityIds::class . "::" . $stringId);
				}
			}
		}

		if ($this->level instanceof Level && $this->entityId !== -1) {
			$this->level->scheduleDelayedBlockUpdate($this, 1);
		}
	}

	public function getDefaultName() : string
	{
		return "MobSpawner";
	}

	protected function writeSaveData(CompoundTag $nbt) : void
	{
		$nbt->setByte(self::TAG_IS_MOVABLE, intval($this->isMovable), true);
		$nbt->setShort(self::TAG_DELAY, $this->delay, true);
		$nbt->setShort(self::TAG_MAX_NEARBY_ENTITIES, $this->maxNearbyEntities, true);
		$nbt->setShort(self::TAG_MAX_SPAWN_DELAY, $this->maxSpawnDelay, true);
		$nbt->setShort(self::TAG_MIN_SPAWN_DELAY, $this->minSpawnDelay, true);
		$nbt->setShort(self::TAG_REQUIRED_PLAYER_RANGE, $this->requiredPlayerRange, true);
		$nbt->setShort(self::TAG_MIN_SPAWN_COUNT, $this->minSpawnCount, true);
		$nbt->setShort(self::TAG_MAX_SPAWN_COUNT, $this->maxSpawnCount, true);
		$nbt->setShort(self::TAG_SPAWN_RANGE, $this->spawnRange, true);
		$nbt->setInt(self::TAG_ENTITY_ID, $this->entityId, true);
	}

	public function addAdditionalSpawnData(CompoundTag $nbt, int $protocolVersion) : void
	{
		$nbt->setByte(self::TAG_IS_MOVABLE, intval($this->isMovable));
		$nbt->setInt(self::TAG_ENTITY_ID, $this->entityId);
	}

	public function onUpdate() : bool
	{
		if ($this->entityId === -1) {
			return false;
		}

		if ($this->delay++ >= rand($this->minSpawnDelay, $this->maxSpawnDelay)) {
			$this->delay = 0;

			$nearbyEntities = 0;
			$playerInRange = false;
			$requiredRangeSq = $this->requiredPlayerRange * $this->requiredPlayerRange;

			foreach ($this->level->getEntities() as $entity) {
				if (!$playerInRange && $entity instanceof Player && !$entity->isSpectator()) {
					if ($entity->distanceSquared($this) <= $requiredRangeSq) {
						$playerInRange = true;
					}
				} elseif (!($entity instanceof Player)) {
					if ($entity->distanceSquared($this) <= $requiredRangeSq) {
						$nearbyEntities++;
					}
				}
			}

			$amountToSpawn = $this->minSpawnCount + rand(0, $this->maxSpawnCount - 1);
			for ($i = 0; $i < $amountToSpawn; $i++) {
				if ($playerInRange && $nearbyEntities <= $this->maxNearbyEntities) {
					$spawnPos = new Vector3(
						$this->x + rand(-$this->spawnRange, $this->spawnRange),
						$this->y + 1,
						$this->z + rand(-$this->spawnRange, $this->spawnRange)
					);

					$block = $this->level->getBlock($spawnPos);
					$blockId = $block->getId();
					if ($blockId !== Block::AIR && !($block instanceof Liquid) && $blockId !== Block::SIGN_POST && $blockId !== Block::WALL_SIGN) {
						continue;
					}

					$mob = Entity::createEntity($this->entityId, $this->level, Entity::createBaseNBT($spawnPos->add(0.5, 0, 0.5)));
					if ($mob instanceof Entity) {
						if ($mob instanceof Monster && $this->level->getBlockLightAt((int) $this->x, (int) $this->y, (int) $this->z) > 3) {
							$mob->flagForDespawn();
							continue;
						}

						if ($mob instanceof Mob) {
							if (Server::getInstance()->mobAiEnabled) {
								$mob->setImmobile(false);
							}
						}

						$mob->spawnToAll();
						$nearbyEntities++;
					}
				}
			}
		}

		return true;
	}

	public function isValidSpawnPosition(Vector3 $pos) : bool
	{
		return !$this->level->getBlock($pos)->isSolid() && !$this->level->getBlock($pos->up())->isSolid() && $this->level->getBlock($pos->down())->isSolid();
	}
}
