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

use pocketmine\level\generator\vanilla\block\Fence;
use pocketmine\level\generator\vanilla\entity\Entity;
use pocketmine\level\generator\vanilla\entity\Living;
use pocketmine\level\generator\vanilla\event\entity\EntityDamageEvent;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\level\ChunkManager;
use pocketmine\level\generator\vanilla\level\Level;
use pocketmine\level\generator\vanilla\level\Position;
use pocketmine\level\generator\vanilla\math\AxisAlignedBB;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\nbt\tag\CompoundTag;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\Player;

class LeashKnot extends Entity
{
	public const NETWORK_ID = self::LEASH_KNOT;

	/** @var float */
	protected $gravity = 0.0;
	/** @var float */
	protected $drag = 0.0;
	public float $height = 0.33;
	public float $width = 0.1875;
	/** @var int */
	public $dropCounter = 0;

	/**
	 * LeashKnot constructor.
	 */
	public function __construct(ChunkManager $level, CompoundTag $nbt)
	{
		parent::__construct($level, $nbt);

		$this->setPosition($this->getHangingPosition()->add(0.5, 0.25, 0.5));
		$this->boundingBox = new AxisAlignedBB($this->x - 0.1875, $this->y - 0.25 + 0.125, $this->z - 0.1875, $this->x + 0.1875, $this->y + 0.25 + 0.125, $this->z + 0.1875);
	}

	public function initEntity() : void
	{
		$this->setMaxHealth(1);
		parent::initEntity();
	}

	public function isSurfaceValid() : bool
	{
		return $this->level->getBlock($this) instanceof Fence;
	}

	public function hasMovementUpdate() : bool
	{
		return false;
	}

	protected function updateMovement(bool $teleport = false) : void
	{

	}

	public function canBeCollidedWith() : bool
	{
		return false;
	}

	public function entityBaseTick(int $tickDiff = 1) : bool
	{
		if ($this->dropCounter++ >= 100 && $this->isValid()) {
			$this->dropCounter = 0;
			if (!$this->isSurfaceValid() && !$this->isFlaggedForDespawn()) {
				$this->kill();
			}
		}
		return parent::entityBaseTick($tickDiff);
	}

	public function onFirstInteract(Player $player, Vector3 $clickPos) : bool
	{
		$flag = false;
		$item = $player->getInventory()->getItemInHand();

		if ($item->getId() === Item::LEAD) {
			$f = 7.0;

			foreach ($player->level->getCollidingEntities(new AxisAlignedBB($this->x - $f, $this->y - $f, $this->z - $f, $this->x + $f, $this->y + $f, $this->z + $f)) as $entity) {
				if ($entity instanceof Living) {
					if ($entity->isLeashed() && $entity->getLeashedToEntity() === $player) {
						$entity->setLeashedToEntity($this, true);
						$flag = true;
					}
				}
			}
		}

		if (!$flag) {
			$this->kill();

			if ($player->isCreative()) {
				$f = 7.0;

				foreach ($player->level->getCollidingEntities(new AxisAlignedBB($this->x - $f, $this->y - $f, $this->z - $f, $this->x + $f, $this->y + $f, $this->z + $f)) as $entity) {
					if ($entity instanceof Living) {
						if ($entity->isLeashed() && $entity->getLeashedToEntity() === $this) {
							$entity->clearLeashed(true, false);
						}
					}
				}
			}
		}

		return true;
	}

	public function attack(EntityDamageEvent $source) : void
	{
		parent::attack($source);

		if (!$source->isCancelled()) {
			$this->kill();
		}
	}

	public function kill() : void
	{
		parent::kill();

		$this->level->broadcastLevelSoundEvent($this, LevelSoundEventPacket::SOUND_LEASHKNOT_BREAK);
	}

	public function getHangingPosition() : Position
	{
		return new Position($this->getFloorX(), $this->getFloorY(), $this->getFloorZ(), $this->level);
	}

	public static function getKnotFromPosition(Level $level, Vector3 $pos) : ?LeashKnot
	{
		foreach ($level->getCollidingEntities(new AxisAlignedBB($pos->x - 1, $pos->y - 1, $pos->z - 1, $pos->x + 1, $pos->y + 1, $pos->z + 1)) as $entity) {
			if ($entity instanceof LeashKnot) {
				if ($entity->getHangingPosition()->equals($pos)) {
					return $entity;
				}
			}
		}

		return null;
	}
}
