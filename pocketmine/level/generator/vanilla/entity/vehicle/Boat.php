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

namespace pocketmine\level\generator\vanilla\entity\vehicle;

use pocketmine\level\generator\vanilla\block\Water;
use pocketmine\level\generator\vanilla\entity\Entity;
use pocketmine\level\generator\vanilla\entity\Vehicle;
use pocketmine\level\generator\vanilla\event\entity\EntityDamageByEntityEvent;
use pocketmine\level\generator\vanilla\event\entity\EntityDamageEvent;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\item\ItemFactory;
use pocketmine\level\generator\vanilla\level\GameRules;
use pocketmine\level\generator\vanilla\math\AxisAlignedBB;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\SetActorLinkPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\EntityLink;
use pocketmine\Player;

use function spl_object_id;

class Boat extends Vehicle
{
	public const NETWORK_ID = self::BOAT;

	public const TAG_VARIANT = "Variant";

	public float $height = 0.455;
	public float $width = 1.4;

	/** @var Vector3|null */
	protected $boatPos;
	protected $boatYaw = 0.0;
	protected $boatPitch = 0.0;
	/** @var Vector3 */
	protected $velocity;

	protected int $clientMoveTicks = 0;

	protected function initEntity() : void
	{
		$this->setMaxHealth(40);
		$this->setGenericFlag(self::DATA_FLAG_STACKABLE, true);
		$this->setImmobile(false);

		$this->setBoatType($this->namedtag->getInt(self::TAG_VARIANT, 0));
		$this->setHurtDirection(1);
		$this->setHurtTime(0);

		$this->getDataPropertyManager()->setByte(self::DATA_CONTROLLING_RIDER_SEAT_NUMBER, 0);
		$this->getDataPropertyManager()->setByte(self::DATA_IS_BUOYANT, 1);
		$this->getDataPropertyManager()->setString(self::DATA_BUOYANCY_DATA, "{\"apply_gravity\":true,\"base_buoyancy\":1.0,\"big_wave_probability\":0.02999999932944775,\"big_wave_speed\":10.0,\"drag_down_on_buoyancy_removed\":0.0,\"liquid_blocks\":[\"minecraft:water\",\"minecraft:flowing_water\"],\"simulate_waves\":true}");

		$this->velocity = new Vector3(0, 0, 0);

		parent::initEntity();
	}

	public function getRiderSeatPosition(int $seatNumber = 0) : Vector3
	{
		return new Vector3($seatNumber * 0.2, 0, 0);
	}

	public function getMountedYOffset() : float
	{
		return -0.2;
	}

	public function getBoatType() : int
	{
		return $this->propertyManager->getInt(self::DATA_VARIANT);
	}

	public function setBoatType(int $boatType) : void
	{
		$this->propertyManager->setInt(self::DATA_VARIANT, $boatType);
	}

	public function setClientPositionAndRotation(Vector3 $pos, float $yaw, float $pitch, int $clientMoveTicks, bool $immediate) : void
	{
		$riddenByEntity = $this->getRiddenByEntity();

		if ($immediate && $riddenByEntity !== null) {
			$this->setPositionAndRotation($pos, $yaw, $pitch);
			$this->clientMoveTicks = 0;
			$this->resetMotion();

			$this->velocity = new Vector3(0, 0, 0);
		} else {
			if ($riddenByEntity === null) {
				$this->clientMoveTicks = $clientMoveTicks + 5;
			} else {
				if ($this->distanceSquared($pos) > 1) {
					$this->clientMoveTicks = 3;
				} else {
					return;
				}
			}

			$this->boatPos = $pos;
			$this->boatYaw = $yaw;
			$this->boatPitch = $pitch;
			$this->motion = $this->velocity;
		}
	}

	public function onRiderMount(Entity $entity) : void
	{
		$entity->getDataPropertyManager()->setByte(self::DATA_RIDER_ROTATION_LOCKED, 1);
		$entity->getDataPropertyManager()->setFloat(self::DATA_RIDER_MAX_ROTATION, 90.0);
		$entity->getDataPropertyManager()->setFloat(self::DATA_RIDER_MIN_ROTATION, -90.0);
		$entity->getDataPropertyManager()->setFloat(self::DATA_RIDER_SEAT_ROTATION_OFFSET, -90.0);
	}

	public function onRiderLeave(Entity $entity) : void
	{
		$entity->getDataPropertyManager()->setByte(self::DATA_RIDER_ROTATION_LOCKED, 0);
		$entity->getDataPropertyManager()->setFloat(self::DATA_RIDER_MAX_ROTATION, 360.0);
		$entity->getDataPropertyManager()->setFloat(self::DATA_RIDER_MIN_ROTATION, 0.0);
		$entity->getDataPropertyManager()->setFloat(self::DATA_RIDER_SEAT_ROTATION_OFFSET, 0.0);
	}

	public function setClientMotion(Vector3 $motion) : void
	{
		$this->velocity = $motion;
	}

	public function saveNBT() : void
	{
		parent::saveNBT();

		$this->namedtag->setInt(self::TAG_VARIANT, $this->getBoatType());
	}

	public function attack(EntityDamageEvent $source) : void
	{
		$source->setBaseDamage($source->getBaseDamage() * 10);

		parent::attack($source);

		if (!$source->isCancelled() && $source instanceof EntityDamageByEntityEvent) {
			$damager = $source->getDamager();

			$this->setHurtTime(10);
			$this->setHurtDirection(-$this->getHurtDirection());

			$flag = ($damager instanceof Player && $damager->isCreative());
			if ($flag || $this->getHealth() <= 0) {
				$this->kill();

				if (!$flag && $this->level->getGameRules()->getBool(GameRules::RULE_DO_ENTITY_DROPS)) {
					$this->level->dropItem($this, ItemFactory::get(Item::BOAT, $this->getBoatType()));
				}
			}
		}
	}

	public function onUpdate(int $currentTick) : bool
	{
		if ($this->closed) {
			return false;
		}

		$tickDiff = $currentTick - $this->lastUpdate;
		if ($tickDiff <= 0 && !$this->justCreated) {
			return true;
		}

		if ($this->getHurtTime() > 0) {
			$this->setHurtTime($this->getHurtTime() - 1);
		}

		if ($this->getHealth() < 40 && $this->isAlive()) {
			$this->setHealth($this->getHealth() + 1);
		}

		$waterCount = 0;
		$bb = $this->getBoundingBox();

		for ($j = 0; $j < 5; ++$j) {
			$d1 = $bb->minY + ($bb->maxY - $bb->minY) * $j / 5 - 0.125;
			$d3 = $bb->minY + ($bb->maxY - $bb->minY) * ($j + 1) / 5 - 0.125;

			$bb2 = new AxisAlignedBB($bb->minX, $d1, $bb->minZ, $bb->maxX, $d3, $bb->maxZ);

			if ($this->level->isLiquidInBoundingBox($bb2, new Water())) {
				$waterCount += 0.2;
			}
		}

		if ($this->getRiddenByEntity() !== null) {
			if ($this->clientMoveTicks > 0) {
				$newPos = $this->addVector(($this->boatPos->subtractVector($this))->divide($this->clientMoveTicks));
				$newYaw = $this->yaw + ($this->boatYaw - $this->yaw) / $this->clientMoveTicks;
				$newPitch = $this->pitch + ($this->boatPitch - $this->pitch) / $this->clientMoveTicks;

				$this->setPositionAndRotation($newPos, $newYaw, $newPitch);

				$this->clientMoveTicks--;

				//MAKING SURE TO KEEP PLAYER IN POSITION ON OTHER CLIENTS
				//SENDING EVERY 100 TICKS
				if ($this->ticksLived % 100 === 0) {
					$this->broadcastLink($this->getRiddenByEntity());
				}
			} else {
				if ($this->onGround) {
					$this->motion = $this->motion->multiply(0.5);
				}

				$this->motion->x *= 0.99;
				$this->motion->y *= 0.95;
				$this->motion->z *= 0.99;
			}
		}

		if ($waterCount < 1) {
			$this->motion->y += 0.04 * ($waterCount * 2 - 1);
		} else {
			if ($this->motion->y < 0) {
				$this->motion->y /= 2;
			}

			$this->motion->y += 0.007;
		}

		$this->move($this->motion->x, $this->motion->y, $this->motion->z); // Move the entity

		$this->motion->x *= 0.99;
		$this->motion->y *= 0.95;
		$this->motion->z *= 0.99;

		return parent::onUpdate($currentTick);
	}

	protected function onMovementUpdate() : void
	{
		if ($this->onGround && $this->clientMoveTicks === 0) {
			$this->motion = $this->motion->multiply(0.5);
		}

		$this->checkMotion();

		if ($this->motion->x != 0 || $this->motion->y != 0 || $this->motion->z != 0) {
			$this->move($this->motion->x, $this->motion->y, $this->motion->z);
		}

		$this->motion->x *= 0.99;
		$this->motion->y *= 0.95;
		$this->motion->z *= 0.99;
	}

	public function getSeatCount() : int
	{
		return 2;
	}

	public function setPaddleTimeLeft(float $value) : void
	{
		$this->propertyManager->setFloat(self::DATA_PADDLE_TIME_LEFT, $value);
	}

	public function getPaddleTimeLeft() : float
	{
		return $this->propertyManager->getFloat(self::DATA_PADDLE_TIME_LEFT) ?? 0.0;
	}

	public function setPaddleTimeRight(float $value) : void
	{
		$this->propertyManager->setFloat(self::DATA_PADDLE_TIME_RIGHT, $value);
	}

	public function getPaddleTimeRight() : float
	{
		return $this->propertyManager->getFloat(self::DATA_PADDLE_TIME_RIGHT) ?? 0.0;
	}

	protected function broadcastLink(?Player $player = null, int $type = EntityLink::TYPE_RIDER) : void
	{
		$id = spl_object_id($player);
		foreach ($this->getViewers() as $viewer) {
			if (!isset($viewer->getViewers()[$id])) {
				$player->spawnTo($viewer);
			}
			$pk = new SetActorLinkPacket();
			$pk->link = new EntityLink($this->getId(), $player->getId(), $type, false, true);
			$viewer->sendDataPacket($pk);
		}
	}
}
