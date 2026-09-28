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

namespace pocketmine\level\generator\vanilla\entity\object;

use pocketmine\level\generator\vanilla\block\Block;
use pocketmine\level\generator\vanilla\block\BlockFactory;
use pocketmine\level\generator\vanilla\block\Fallable;
use pocketmine\level\generator\vanilla\entity\Entity;
use pocketmine\level\generator\vanilla\entity\Living;
use pocketmine\level\generator\vanilla\event\entity\EntityBlockChangeEvent;
use pocketmine\level\generator\vanilla\event\entity\EntityDamageByEntityEvent;
use pocketmine\level\generator\vanilla\event\entity\EntityDamageEvent;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\item\ItemFactory;
use pocketmine\level\generator\vanilla\level\Position;
use pocketmine\level\generator\vanilla\level\sound\BlockBreakSound;
use pocketmine\level\generator\vanilla\nbt\tag\ByteTag;
use pocketmine\level\generator\vanilla\nbt\tag\IntTag;
use pocketmine\level\generator\vanilla\network\mcpe\convert\block\BlockProtocolConvertor;
use pocketmine\level\generator\vanilla\network\mcpe\convert\block\RuntimeBlockMapping;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\AddActorPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ProtocolInfo;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\SetActorDataPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\entity\PropertySyncData;
use pocketmine\Player;
use UnexpectedValueException;
use function abs;
use function get_class;
use function is_array;
use function min;
use function round;

class FallingBlock extends Entity
{
	public const NETWORK_ID = self::FALLING_BLOCK;

	public float $width = 0.98;
	public float $height = 0.98;

	protected float $baseOffset = 0.49;

	protected $gravity = 0.04;
	protected $drag = 0.02;

	/** @var Block */
	protected $block;

	public bool $canCollide = false;

	protected function initEntity() : void
	{
		parent::initEntity();

		$blockId = 0;

		//TODO: 1.8+ save format
		if ($this->namedtag->hasTag("TileID", IntTag::class)) {
			$blockId = $this->namedtag->getInt("TileID");
		} elseif ($this->namedtag->hasTag("Tile", ByteTag::class)) {
			$blockId = $this->namedtag->getByte("Tile");
			$this->namedtag->removeTag("Tile");
		}

		if ($blockId === 0) {
			throw new UnexpectedValueException("Invalid " . get_class($this) . " entity: block ID is 0 or missing");
		}

		$damage = $this->namedtag->getByte("Data", 0);

		$this->block = BlockFactory::get($blockId, $damage);

		$this->propertyManager->setInt(self::DATA_VARIANT, $this->getBlock() | ($this->getDamage() << 8));
	}

	public function canCollideWith(Entity $entity) : bool
	{
		return false;
	}

	public function canBeMovedByCurrents() : bool
	{
		return false;
	}

	public function attack(EntityDamageEvent $source) : void
	{
		if ($source->getCause() === EntityDamageEvent::CAUSE_VOID) {
			parent::attack($source);
		}
	}

	public function entityBaseTick(int $tickDiff = 1) : bool
	{
		if ($this->closed) {
			return false;
		}

		$hasUpdate = parent::entityBaseTick($tickDiff);

		if (!$this->isFlaggedForDespawn()) {
			$level = $this->getLevel();

			$pos = Position::fromObject($this->add(-$this->width / 2, $this->height, -$this->width / 2)->floor(), $this->getLevel());

			$this->block->position($pos);

			$blockTarget = null;
			if ($this->block instanceof Fallable) {
				$blockTarget = $this->block->tickFalling();
			}

			if ($this->onGround || $blockTarget !== null) {
				$this->flagForDespawn();

				$blockResult = $blockTarget ?? $this->block;
				$block = $level->getBlock($pos);
				if(!$block->canBeReplaced() || !$level->isInWorld($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ()) || ($this->onGround && abs($this->y - $this->getFloorY()) > 0.001)){
					$level->dropItem($this, $this->block->asItem());
					$level->addSound(new BlockBreakSound($pos->add(0.5, 0.5, 0.5), $blockResult));
				}else{
					$ev = new EntityBlockChangeEvent($this, $block, $blockResult);
					$ev->call();
					if(!$ev->isCancelled()){
						$b = $ev->getTo();
						$level->setBlock($pos, $b);
						if($this->onGround && $b instanceof Fallable && ($sound = $b->getLandSound($pos->add(0.5, 0.5, 0.5))) !== null){
							$level->addSound($sound);
						}
					}
				}

				$hasUpdate = true;
			}
		}

		return $hasUpdate;
	}

	public function fall(float $fallDistance) : void{
		if($this->block instanceof Fallable){
			$damagePerBlock = $this->block->getFallDamagePerBlock();
			if($damagePerBlock > 0 && ($fallenBlocks = round($this->fallDistance) - 1) > 0){
				$damage = min($fallenBlocks * $damagePerBlock, $this->block->getMaxFallDamage());
				foreach($this->level->getCollidingEntities($this->getBoundingBox()) as $entity){
					if($entity instanceof Living){
						$ev = new EntityDamageByEntityEvent($this, $entity, EntityDamageEvent::CAUSE_FALLING_BLOCK, $damage);
						$entity->attack($ev);
					}
				}
			}
			if(!$this->block->onHitGround($this, $fallDistance)){
				$this->flagForDespawn();
			}
		}
	}

	public function getBlock() : int
	{
		return $this->block->getId();
	}

	public function getDamage() : int
	{
		return $this->block->getDamage();
	}

	public function saveNBT() : void
	{
		parent::saveNBT();
		$this->namedtag->setInt("TileID", $this->block->getId(), true);
		$this->namedtag->setByte("Data", $this->block->getDamage());
	}

	public function getPickedItem() : ?Item
	{
		return ItemFactory::get($this->getBlock(), $this->getDamage());
	}

	public function sendSpawnPacket(Player $player) : void
	{
		$metadata = $this->propertyManager->getAll();

		if (isset($metadata[self::DATA_VARIANT])) {
			$block = BlockProtocolConvertor::getInstance()->get($this->block, $player->getProtocolVersion()) ?? $this->block;
			if ($player->getProtocolVersion() >= ProtocolInfo::PROTOCOL_407) {
				$variant = RuntimeBlockMapping::getInstance($player->getProtocolVersion())->toRuntimeId($block->getFullId());
			} else {
				$variant = $block->getId() | ($block->getDamage() << 8);
			}

			$metadata[self::DATA_VARIANT][1] = $variant;
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

		/** @var Player[][] $protocolPlayers */
		$protocolPlayers = [];
		foreach ($player as $target) {
			$protocolPlayers[$target->getProtocolVersion()][] = $target;
		}

		foreach ($protocolPlayers as $protocolVersion => $targets) {
			if (isset($pk->metadata[self::DATA_VARIANT])) {
				$block = BlockProtocolConvertor::getInstance()->get($this->block, $protocolVersion) ?? $this->block;
				if ($protocolVersion >= ProtocolInfo::PROTOCOL_407) {
					$variant = RuntimeBlockMapping::getInstance($protocolVersion)->toRuntimeId($block->getFullId());
				} else {
					$variant = $block->getId() | ($block->getDamage() << 8);
				}

				$pk->metadata[self::DATA_VARIANT][1] = $variant;
			}

			foreach ($targets as $target) {
				$target->sendDataPacket(clone $pk);
			}
		}
	}
}
