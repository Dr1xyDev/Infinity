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

use pocketmine\level\generator\vanilla\nbt\tag\CompoundTag;
use pocketmine\level\generator\vanilla\nbt\tag\IntTag;
use pocketmine\level\generator\vanilla\utils\Binary;
use pocketmine\level\generator\vanilla\utils\Color;

use function boolval;
use function intval;

class Cauldron extends Spawnable
{
	protected $potionId = -1;
	protected $splashPotion = false;
	/** @var Color|null */
	protected $customColor;

	public function getName() : string
	{
		return "Cauldron";
	}

	protected function readSaveData(CompoundTag $nbt) : void
	{
		$this->potionId = $nbt->getShort("PotionId", -1);
		$this->splashPotion = boolval($nbt->getByte("SplashPotion", 0));

		if ($nbt->hasTag("CustomColor", IntTag::class)) {
			$this->customColor = Color::fromARGB(Binary::unsignInt($nbt->getInt("CustomColor")));
		}
	}

	protected function writeSaveData(CompoundTag $nbt) : void
	{
		$nbt->setShort("PotionId", $this->potionId);
		$nbt->setByte("SplashPotion", intval($this->splashPotion));

		if ($this->customColor !== null) {
			$nbt->setInt("CustomColor", Binary::signInt($this->customColor->toARGB()));
		}
	}

	protected function addAdditionalSpawnData(CompoundTag $nbt, int $protocolVersion) : void
	{
		$this->writeSaveData($nbt);
	}

	public function getCustomColor() : ?Color
	{
		return $this->customColor;
	}

	public function setCustomColor(?Color $customColor) : void
	{
		$this->customColor = $customColor;
		$this->onChanged();
	}

	public function getPotionId() : int
	{
		return $this->potionId;
	}

	public function setPotionId(int $potionId) : void
	{
		$this->potionId = $potionId;
		$this->onChanged();
	}

	public function hasPotion() : bool
	{
		return $this->potionId !== -1;
	}

	public function isSplashPotion() : bool
	{
		return $this->splashPotion;
	}

	public function setSplashPotion(bool $splashPotion) : void
	{
		$this->splashPotion = $splashPotion;
		$this->onChanged();
	}
}
