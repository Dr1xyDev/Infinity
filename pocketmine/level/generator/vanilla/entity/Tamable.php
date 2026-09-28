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

use pocketmine\level\generator\vanilla\nbt\tag\StringTag;
use pocketmine\level\generator\vanilla\utils\UUID;

use function boolval;
use function intval;
use function strlen;

abstract class Tamable extends Animal
{
	public function saveNBT() : void
	{
		parent::saveNBT();

		$this->namedtag->setByte("Tamed", intval($this->isTamed()));
		$this->namedtag->setByte("Sitting", intval($this->isSitting()));

		if ($this->getOwningEntity() !== null) {
			$uuid = $this->getOwningEntity()->getUniqueId();

			$this->namedtag->setString("OwnerUUID", $uuid->toString());
		}
	}

	protected function initEntity() : void
	{
		parent::initEntity();

		$this->setTamed(boolval($this->namedtag->getByte("Tamed", 0)));
		$this->setSitting(boolval($this->namedtag->getByte("Sitting", 0)));
		if ($this->namedtag->hasTag("OwnerUUID", StringTag::class)) {
			$uuid = $this->namedtag->getString("OwnerUUID");
			if (strlen($uuid) === 16) {
				$owner = $this->server->getPlayerByUUID(UUID::fromString($uuid)); // why only player?

				if ($owner !== null) {
					$this->setOwningEntity($owner);
				}
			}
		}
	}

	public function isTamed() : bool
	{
		return $this->getGenericFlag(self::DATA_FLAG_TAMED);
	}

	public function setTamed(bool $tamed = true) : void
	{
		$this->setGenericFlag(self::DATA_FLAG_TAMED, $tamed);
	}

	public function isSitting() : bool
	{
		return $this->getGenericFlag(self::DATA_FLAG_SITTING);
	}

	public function setSitting(bool $sitting = true) : void
	{
		$this->setGenericFlag(self::DATA_FLAG_SITTING, $sitting);
	}

}
