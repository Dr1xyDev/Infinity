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

use InvalidArgumentException;
use pocketmine\level\generator\vanilla\entity\Entity;
use pocketmine\level\generator\vanilla\entity\Human;
use pocketmine\level\generator\vanilla\nbt\tag\IntTag;
use pocketmine\level\generator\vanilla\nbt\tag\ShortTag;
use pocketmine\Player;

use function sqrt;

class ExperienceOrb extends Entity
{
	public const NETWORK_ID = self::XP_ORB;

	public const TAG_VALUE_PC = "Value"; //short
	public const TAG_VALUE_PE = "experience value"; //int (WTF?)

	/**
	 * Max distance an orb will follow a player across.
	 */
	public const MAX_TARGET_DISTANCE = 8.0;

	/**
	 * Split sizes used for dropping experience orbs.
	 */
	public const ORB_SPLIT_SIZES = [2477, 1237, 617, 307, 149, 73, 37, 17, 7, 3, 1]; //This is indexed biggest to smallest so that we can return as soon as we found the biggest value.

	/**
	 * Returns the largest size of normal XP orb that will be spawned for the specified amount of XP. Used to split XP
	 * up into multiple orbs when an amount of XP is dropped.
	 */
	public static function getMaxOrbSize(int $amount) : int
	{
		foreach (self::ORB_SPLIT_SIZES as $split) {
			if ($amount >= $split) {
				return $split;
			}
		}

		return 1;
	}

	/**
	 * Splits the specified amount of XP into an array of acceptable XP orb sizes.
	 *
	 * @return int[]
	 */
	public static function splitIntoOrbSizes(int $amount) : array
	{
		$result = [];

		while ($amount > 0) {
			$size = self::getMaxOrbSize($amount);
			$result[] = $size;
			$amount -= $size;
		}

		return $result;
	}

	public float $height = 0.25;
	public float $width = 0.25;

	public $gravity = 0.04;
	public $drag = 0.02;

	/** @var int */
	protected $age = 0;

	/**
	 * @var int
	 * Ticker used for determining interval in which to look for new target players.
	 */
	protected $lookForTargetTime = 0;

	/**
	 * @var int|null
	 * Runtime entity ID of the player this XP orb is targeting.
	 */
	protected $targetPlayerRuntimeId = null;

	protected function initEntity() : void
	{
		parent::initEntity();

		$this->age = $this->namedtag->getShort("Age", 0);

		$value = 0;
		if ($this->namedtag->hasTag(self::TAG_VALUE_PC, ShortTag::class)) { //PC
			$value = $this->namedtag->getShort(self::TAG_VALUE_PC);
		} elseif ($this->namedtag->hasTag(self::TAG_VALUE_PE, IntTag::class)) { //PE save format
			$value = $this->namedtag->getInt(self::TAG_VALUE_PE);
		}

		$this->setXpValue($value);
	}

	public function saveNBT() : void
	{
		parent::saveNBT();

		$this->namedtag->setShort("Age", $this->age);

		$this->namedtag->setShort(self::TAG_VALUE_PC, $this->getXpValue());
		$this->namedtag->setInt(self::TAG_VALUE_PE, $this->getXpValue());
	}

	public function getXpValue() : int
	{
		return $this->propertyManager->getInt(self::DATA_EXPERIENCE_VALUE) ?? 0;
	}

	public function setXpValue(int $amount) : void
	{
		if ($amount <= 0) {
			throw new InvalidArgumentException("XP amount must be greater than 0, got $amount");
		}
		$this->propertyManager->setInt(self::DATA_EXPERIENCE_VALUE, $amount);
	}

	public function hasTargetPlayer() : bool
	{
		return $this->targetPlayerRuntimeId !== null;
	}

	public function getTargetPlayer() : ?Human
	{
		if ($this->targetPlayerRuntimeId === null) {
			return null;
		}

		$entity = $this->level->getEntity($this->targetPlayerRuntimeId);
		if ($entity instanceof Human) {
			return $entity;
		}

		return null;
	}

	public function setTargetPlayer(?Human $player) : void
	{
		$this->targetPlayerRuntimeId = $player !== null ? $player->getId() : null;
	}

	public function entityBaseTick(int $tickDiff = 1) : bool
	{
		$hasUpdate = parent::entityBaseTick($tickDiff);

		$this->age += $tickDiff;
		if ($this->age > 6000) {
			$this->flagForDespawn();
			return true;
		}

		$currentTarget = $this->getTargetPlayer();
		if ($currentTarget !== null && (!$currentTarget->isAlive() || $currentTarget->distanceSquared($this) > self::MAX_TARGET_DISTANCE ** 2)) {
			$currentTarget = null;
		}

		if ($this->lookForTargetTime >= 20) {
			if ($currentTarget === null) {
				$newTarget = $this->level->getNearestEntity($this, self::MAX_TARGET_DISTANCE, Human::class);

				if ($newTarget instanceof Human && !($newTarget instanceof Player && $newTarget->isSpectator())) {
					$currentTarget = $newTarget;
				}
			}

			$this->lookForTargetTime = 0;
		} else {
			$this->lookForTargetTime += $tickDiff;
		}

		$this->setTargetPlayer($currentTarget);

		if ($currentTarget !== null) {
			$vector = $currentTarget->add(0, $currentTarget->getEyeHeight() / 2, 0)->subtractVector($this)->divide(self::MAX_TARGET_DISTANCE);

			$distance = $vector->lengthSquared();
			if ($distance < 1) {
				$diff = $vector->normalize()->multiply(0.2 * (1 - sqrt($distance)) ** 2);

				$this->motion->x += $diff->x;
				$this->motion->y += $diff->y;
				$this->motion->z += $diff->z;
			}

			if ($currentTarget->canPickupXp() && $this->boundingBox->intersectsWith($currentTarget->getBoundingBox())) {
				$this->flagForDespawn();

				$currentTarget->onPickupXp($this->getXpValue());
			}
		}

		return $hasUpdate;
	}

	protected function tryChangeMovement() : void
	{
		$this->checkObstruction($this->x, $this->y, $this->z);
		parent::tryChangeMovement();
	}

	public function canBeCollidedWith() : bool
	{
		return false;
	}
}
