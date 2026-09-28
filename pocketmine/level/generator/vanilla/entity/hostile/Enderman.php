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

namespace pocketmine\level\generator\vanilla\entity\hostile;

use pocketmine\level\generator\vanilla\block\Block;
use pocketmine\level\generator\vanilla\block\BlockFactory;
use pocketmine\level\generator\vanilla\entity\behavior\FloatBehavior;
use pocketmine\level\generator\vanilla\entity\behavior\HurtByTargetBehavior;
use pocketmine\level\generator\vanilla\entity\behavior\LookAtPlayerBehavior;
use pocketmine\level\generator\vanilla\entity\behavior\MeleeAttackBehavior;
use pocketmine\level\generator\vanilla\entity\behavior\RandomLookAroundBehavior;
use pocketmine\level\generator\vanilla\entity\behavior\RandomStrollBehavior;
use pocketmine\level\generator\vanilla\entity\Entity;
use pocketmine\level\generator\vanilla\entity\Monster;
use pocketmine\level\generator\vanilla\event\entity\EntityDamageEvent;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\item\ItemFactory;
use pocketmine\level\generator\vanilla\level\particle\PortalParticle;
use pocketmine\level\generator\vanilla\level\sound\EndermanTeleportSound;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\nbt\tag\IntTag;
use pocketmine\level\generator\vanilla\network\mcpe\convert\ItemTranslator;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ActorEventPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\AddActorPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ProtocolInfo;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\SetActorDataPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\entity\PropertySyncData;
use pocketmine\Player;

use function in_array;
use function is_array;
use function mt_getrandmax;
use function mt_rand;

class Enderman extends Monster
{
	public const NETWORK_ID = self::ENDERMAN;

	public float $width = 0.6;
	public float $height = 2.9;

	public ?float $eyeHeight = 2.55;

	public $shouldTeleport = false;
	public $block = null;

	public const BLOCK_ID_TAG = "BlockId";

	protected function initEntity() : void
	{
		$this->setMaxHealth(40);
		$this->setHealth(40);
		$this->setMovementSpeed(0.3);
		$this->setFollowRange(64);
		$this->setAttackDamage($this->getEndermanDamage());

		if ($this->namedtag->hasTag(self::BLOCK_ID_TAG, IntTag::class)) {
			$this->block = BlockFactory::get($this->namedtag->getInt(self::BLOCK_ID_TAG));

			$this->propertyManager->setShort(self::DATA_ENDERMAN_HELD_ITEM_ID, $this->block->getId());
		}

		parent::initEntity();

		$this->getNavigator()->setAvoidsWater(true);
	}

	public function saveNBT() : void
	{
		parent::saveNBT();
		if ($this->block !== null) {
			$this->namedtag->setInt(self::BLOCK_ID_TAG, $this->block->getId());
		}
	}

	public function getName() : string
	{
		return "Enderman";
	}

	public function getDrops() : array
	{
		$looting = $this->getLootingLevel();
		$drops = [];

		if (mt_rand(0, 1) === 1) {
			$pearls = 1 + ($looting > 0 ? mt_rand(0, $looting) : 0);
			$drops[] = ItemFactory::get(Item::ENDER_PEARL, 0, $pearls);
		}

		return $drops;
	}

	public function randomFloat($min = -0.8, $max = 0.8)
	{
		return $min + mt_rand() / mt_getrandmax() * ($max - $min);
	}

	public function getEndermanDamage() : int
	{
		switch ($this->level->getServer()->getDifficulty()) {
			case 1:
				return 4;
			case 2:
				return 7;
			case 3:
				return 10;
			default:
				return 7;
		}
	}

	public function getXpDropAmount() : int
	{
		return 5;
	}

	public function attack(EntityDamageEvent $source) : void
	{
		if ($source->getCause() === EntityDamageEvent::CAUSE_PROJECTILE) {
			$this->shouldTeleport = true;
			return;
		}

		parent::attack($source);
	}

	public function onAttack(EntityDamageEvent $source) : void
	{
		$this->broadcastEntityEvent(ActorEventPacket::ARM_SWING);
	}

	protected function addBehaviors() : void
	{
		$this->behaviorPool->setBehavior(0, new FloatBehavior($this));
		$this->behaviorPool->setBehavior(1, new MeleeAttackBehavior($this, 2));
		$this->behaviorPool->setBehavior(2, new RandomStrollBehavior($this, 1.0));
		$this->behaviorPool->setBehavior(3, new LookAtPlayerBehavior($this, 8.0));
		$this->behaviorPool->setBehavior(4, new RandomLookAroundBehavior($this));

		$this->targetBehaviorPool->setBehavior(0, new HurtByTargetBehavior($this));
	}

	protected function isPlayerLookingAt(Player $player) : bool
	{
		$helmet = $player->getArmorInventory()->getHelmet();
		if ($helmet !== null && $helmet->getId() === Item::PUMPKIN) {
			return false;
		} else {
			$lookVector = $player->getDirectionVector()->normalize();
			$toEnderman = new Vector3(
				$this->getX() - $player->getX(),
				$this->getEyeHeight() - $player->getEyeHeight(),
				$this->getZ() - $player->getZ()
			);
			$distance = $toEnderman->length();
			$toEnderman = $toEnderman->normalize();
			$dotProduct = $lookVector->dot($toEnderman);

			return $dotProduct > 1.0 - 0.025 / $distance;
		}

		return false;
	}

	protected function findNearestAttackablePlayer() : void
	{
		if ($this->getTargetEntity() !== null) {
			return;
		}

		$list = $this->level->getCollidingEntities($this->getBoundingBox()->expandedCopy(70, 70.0, 70), $this);
		foreach ($list as $player) {
			if ($player instanceof Player) {
				if ($player->isSurvival() && $player->isAlive() && $this->isPlayerLookingAt($player)) {
					$this->setTargetEntity($player);

					$pk = new PlaySoundPacket();
					$pk->soundName = "mob.endermen.stare";
					$pk->x = $this->getX();
					$pk->y = $this->getY();
					$pk->z = $this->getZ();
					$pk->volume = 2.5;
					$pk->pitch = 1.0;
					$this->server->broadcastPacket($this->getViewers(), $pk);

					return;
				}
			}
		}
	}

	public function entityBaseTick(int $diff = 1) : bool
	{
		if (!$this->isImmobile()) {
			if ($this->isInsideOfWater()) {
				$this->attack(new EntityDamageEvent($this, EntityDamageEvent::CAUSE_DROWNING, 1));
				$this->shouldTeleport = true;
			}

			if (mt_rand(0, 200) === 1 || $this->shouldTeleport) {
				$found = false;
				$attempts = 0;
				while (!$found && $attempts < 2) {
					$x = $this->getFloorX() + mt_rand(-8, 8);
					$z = $this->getFloorZ() + mt_rand(-8, 8);
					$y = $this->findSafeY($x, $z);

					if ($y !== null) {
						for ($i = 0; $i < 30; $i++) {
							$this->level->addParticle(new PortalParticle(new Vector3($x + $this->randomFloat(), $y + $this->randomFloat(-0.8, 2.5), $z + $this->randomFloat())));
						}

						for ($i = 0; $i < 30; $i++) {
							$this->level->addParticle(new PortalParticle(new Vector3($x + $this->randomFloat(), $y + $this->randomFloat(-0.8, 2.5), $z + $this->randomFloat())));
						}

						$this->level->addSound(new EndermanTeleportSound($this));
						$this->teleport(new Vector3($x, $y, $z));
						$this->level->addSound(new EndermanTeleportSound($this));
						$this->shouldTeleport = false;
						$found = true;
					} else {
						$attempts++;
					}
				}
			}

			if (mt_rand(0, 1000) === 0 && !$this->hasBlock()) {
				$direction = $this->getDirectionVector()->multiply(1.5);
				$targetBlock = $this->level->getBlock($this->add($direction->x, $direction->y, $direction->z));
				$allowedBlocks = [
					Block::STONE,
					Block::GRASS,
					Block::DIRT,
					Block::COBBLESTONE,
					Block::PLANKS,
					Block::SAND,
					Block::GRAVEL,
					Block::GOLD_ORE,
					Block::IRON_ORE,
					Block::COAL_ORE,
					Block::LOG,
					Block::LEAVES,
					Block::SPONGE,
					Block::GLASS,
					Block::LAPIS_ORE,
					Block::LAPIS_BLOCK,
					Block::SANDSTONE,
					Block::WOOL,
					Block::DANDELION,
					Block::POPPY,
					Block::BROWN_MUSHROOM,
					Block::RED_MUSHROOM,
					Block::GOLD_BLOCK,
					Block::IRON_BLOCK,
					Block::DOUBLE_STONE_SLAB,
					Block::TNT,
					Block::BOOKSHELF,
					Block::MOSSY_COBBLESTONE,
					Block::DIAMOND_ORE,
					Block::DIAMOND_BLOCK,
					Block::CRAFTING_TABLE,
					Block::REDSTONE_ORE,
					Block::GLOWING_REDSTONE_ORE,
					Block::ICE,
					Block::CACTUS,
					Block::CLAY_BLOCK,
					Block::PUMPKIN,
					Block::NETHERRACK,
					Block::SOUL_SAND,
					Block::GLOWSTONE,
					Block::JACK_O_LANTERN,
					Block::STONEBRICK,
					Block::BROWN_MUSHROOM_BLOCK,
					Block::RED_MUSHROOM_BLOCK,
					Block::MELON_BLOCK,
				];

				if (in_array($targetBlock->getId(), $allowedBlocks, true)) {
					$this->propertyManager->setShort(self::DATA_ENDERMAN_HELD_ITEM_ID, $targetBlock->getId());

					$this->block = $targetBlock;
					$this->level->setBlock($targetBlock, BlockFactory::get(0));
				}
			} elseif (mt_rand(0, 1000) === 0 && $this->hasBlock()) {
				$direction = $this->getDirectionVector()->multiply(1.5);
				$targetBlock = $this->level->getBlock($this->add($direction->x, $direction->y, $direction->z));

				if ($targetBlock->getId() === 0) {
					$this->propertyManager->setShort(self::DATA_ENDERMAN_HELD_ITEM_ID, 0);

					$this->level->setBlock($targetBlock, $this->block);
					$this->block = null;
				}
			}

			$this->findNearestAttackablePlayer();
		}

		return parent::entityBaseTick($diff);
	}

	public function findSafeY(int $x, int $z) : ?int
	{
		for ($y = $this->level->getHighestBlockAt($x, $z); $y > 0; $y--) {
			$block = $this->level->getBlockAt($x, $y, $z);
			if (!$this->isUnsafeBlock($block)) {
				return $y + 1;
			}
		}
		return null;
	}

	public function isUnsafeBlock(Block $block) : bool
	{
		$unsafeBlocks = [
			Block::WATER,
			Block::STILL_WATER,
			Block::LEAVES,
			Block::LEAVES2
		];
		return in_array($block->getId(), $unsafeBlocks, true);
	}

	public function sendSpawnPacket(Player $player) : void
	{
		$metadata = $this->propertyManager->getAll();
		if ($player->getProtocolVersion() >= ProtocolInfo::PROTOCOL_419 && isset($metadata[self::DATA_ENDERMAN_HELD_ITEM_ID])) {
			$metadata[self::DATA_ENDERMAN_HELD_ITEM_ID][1] = (ItemTranslator::getInstance($player->getProtocolVersion())->toNetworkIdQuiet($metadata[self::DATA_ENDERMAN_HELD_ITEM_ID][1], 0) ?? [0, 0])[0];
		}

		$player->sendDataPacket(AddActorPacket::create(
			$this->getId(),
			$this->getId(),
			static::NETWORK_ID,
			$this->asVector3(),
			$this->getMotion(),
			$this->pitch,
			$this->yaw,
			$this->yaw,
			$this->yaw,
			$this->attributeMap->getAll(),
			$metadata,
			new PropertySyncData([], []),
			[]
		));
	}

	/**
	 * @param Player[]|Player $player
	 * @param array           $data   Properly formatted entity data, defaults to everything
	 */
	public function sendData($player, ?array $data = null) : void
	{
		if (!is_array($player)) {
			$player = [$player];
		}

		$pk = new SetActorDataPacket();
		$pk->entityRuntimeId = $this->getId();
		$pk->metadata = $data ?? $this->propertyManager->getAll();
		$pk->syncedProperties = new PropertySyncData([], []);

		foreach ($player as $p) {
			if (isset($pk->metadata[self::DATA_ENDERMAN_HELD_ITEM_ID])) {
				if ($p->getProtocolVersion() >= ProtocolInfo::PROTOCOL_419) {
					[$id, ] = ItemTranslator::getInstance($p->getProtocolVersion())->toNetworkIdQuiet($pk->metadata[self::DATA_ENDERMAN_HELD_ITEM_ID][1], 0) ?? [0, 0];

					$pk->metadata[self::DATA_ENDERMAN_HELD_ITEM_ID][1] = $id;
				}
			}

			$p->dataPacket(clone $pk);
		}
	}

	/**
	 * Sets the entity's target entity. Passing null will remove the current target.
	 */
	public function setTargetEntity(?Entity $target) : void
	{
		if ($target === null) {
			$this->setAngry(false);
		} elseif (!$target->closed) {
			$this->setAngry(true);
		}

		parent::setTargetEntity($target);
	}

	public function isAngry() : bool
	{
		return $this->getGenericFlag(self::DATA_FLAG_ANGRY);
	}

	public function setAngry(bool $angry = true) : void
	{
		$this->setGenericFlag(self::DATA_FLAG_ANGRY, $angry);
	}

	public function hasBlock() : bool
	{
		return $this->block !== null;
	}

	public function getBlock() : ?Block
	{
		return $this->block;
	}
}
