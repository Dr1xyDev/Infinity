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

namespace pocketmine\level\generator\vanilla\entity;

use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\Player;

abstract class Vehicle extends Entity implements Rideable
{
	public function onFirstInteract(Player $player, Vector3 $clickPos) : bool
	{
		return $player->mountEntity($this);
	}

	public function setHealth(float $amount) : void
	{
		parent::setHealth($amount);

		$this->propertyManager->setInt(self::DATA_HEALTH, (int) $amount);
	}

	public function getHurtTime() : int
	{
		return $this->propertyManager->getInt(self::DATA_HURT_TIME) ?? 0;
	}

	public function setHurtTime(int $value) : void
	{
		$this->propertyManager->setInt(self::DATA_HURT_TIME, $value);
	}

	public function getHurtDirection() : int
	{
		return $this->propertyManager->getInt(self::DATA_HURT_DIRECTION) ?? 0;
	}

	public function setHurtDirection(int $value) : void
	{
		$this->propertyManager->setInt(self::DATA_HURT_DIRECTION, $value);
	}
}
