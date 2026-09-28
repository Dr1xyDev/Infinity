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

use pocketmine\level\generator\vanilla\inventory\ChestInventory;
use pocketmine\level\generator\vanilla\inventory\DoubleChestInventory;
use pocketmine\level\generator\vanilla\inventory\InventoryHolder;
use pocketmine\level\generator\vanilla\level\format\Chunk;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\nbt\tag\CompoundTag;
use pocketmine\level\generator\vanilla\nbt\tag\IntTag;

class Chest extends Spawnable implements InventoryHolder, LootContainer, Nameable
{
	use NameableTrait {
		addAdditionalSpawnData as addNameSpawnData;
	}
	use ContainerTrait;

	public const TAG_PAIRX = "pairx";
	public const TAG_PAIRZ = "pairz";
	public const TAG_PAIR_LEAD = "pairlead";

	protected ?ChestInventory $inventory = null;
	protected ?DoubleChestInventory $doubleInventory = null;

	protected ?int $pairX = null;
	protected ?int $pairZ = null;

	protected function readSaveData(CompoundTag $nbt) : void
	{
		if ($nbt->hasTag(self::TAG_PAIRX, IntTag::class) && $nbt->hasTag(self::TAG_PAIRZ, IntTag::class)) {
			$this->pairX = $nbt->getInt(self::TAG_PAIRX);
			$this->pairZ = $nbt->getInt(self::TAG_PAIRZ);
		}
		$this->loadName($nbt);

		$this->inventory = new ChestInventory($this);
		$this->loadItems($nbt);
	}

	protected function writeSaveData(CompoundTag $nbt) : void
	{
		if ($this->isPaired()) {
			$nbt->setInt(self::TAG_PAIRX, $this->pairX);
			$nbt->setInt(self::TAG_PAIRZ, $this->pairZ);
		}
		$this->saveName($nbt);
		$this->saveItems($nbt);
	}

	public function getCleanedNBT() : ?CompoundTag
	{
		$tag = parent::getCleanedNBT();
		if ($tag !== null) {
			//TODO: replace this with a purpose flag on writeSaveData()
			$tag->removeTag(self::TAG_PAIRX, self::TAG_PAIRZ);
		}
		return $tag;
	}

	public function close() : void
	{
		if (!$this->closed) {
			$this->inventory->removeAllViewers(true);

			if ($this->doubleInventory !== null) {
				if ($this->isPaired() && $this->level->isChunkLoaded($this->pairX >> Chunk::COORD_BIT_SIZE, $this->pairZ >> Chunk::COORD_BIT_SIZE)) {
					$this->doubleInventory->removeAllViewers(true);
					$this->doubleInventory->invalidate();
					if (($pair = $this->getPair()) !== null) {
						$pair->doubleInventory = null;
					}
				}
				$this->doubleInventory = null;
			}

			$this->inventory = null;

			parent::close();
		}
	}

	/**
	 * @return ChestInventory|DoubleChestInventory
	 */
	public function getInventory()
	{
		if ($this->isPaired() && $this->doubleInventory === null) {
			$this->checkPairing();
		}
		return $this->doubleInventory instanceof DoubleChestInventory ? $this->doubleInventory : $this->inventory;
	}

	/**
	 * @return ChestInventory
	 */
	public function getRealInventory()
	{
		return $this->inventory;
	}

	/**
	 * @return void
	 */
	protected function checkPairing()
	{
		if ($this->isPaired() && !$this->getLevel()->isInLoadedTerrain(new Vector3($this->pairX, $this->y, $this->pairZ))) {
			//paired to a tile in an unloaded chunk
			$this->doubleInventory = null;

		} elseif (($pair = $this->getPair()) instanceof Chest) {
			if (!$pair->isPaired()) {
				$pair->createPair($this);
				$pair->checkPairing();
			}
			if ($this->doubleInventory === null) {
				if ($pair->doubleInventory !== null) {
					$this->doubleInventory = $pair->doubleInventory;
				} else {
					if (($pair->x + ($pair->z << 15)) > ($this->x + ($this->z << 15))) { //Order them correctly
						$this->doubleInventory = $pair->doubleInventory = new DoubleChestInventory($pair, $this);
					} else {
						$this->doubleInventory = $pair->doubleInventory = new DoubleChestInventory($this, $pair);
					}
				}
			}
		} else {
			$this->doubleInventory = null;
			$this->pairX = $this->pairZ = null;
		}
	}

	public function getDefaultName() : string
	{
		return "Chest";
	}

	/**
	 * @return bool
	 */
	public function isPaired()
	{
		return $this->pairX !== null && $this->pairZ !== null;
	}

	public function getPair() : ?Chest
	{
		if ($this->isPaired()) {
			$tile = $this->getLevel()->getTileAt($this->pairX, $this->y, $this->pairZ);
			if ($tile instanceof Chest) {
				return $tile;
			}
		}

		return null;
	}

	/**
	 * @return bool
	 */
	public function pairWith(Chest $tile)
	{
		if ($this->isPaired() || $tile->isPaired()) {
			return false;
		}

		$this->createPair($tile);

		$this->onChanged();
		$tile->onChanged();
		$this->checkPairing();

		return true;
	}

	private function createPair(Chest $tile) : void
	{
		$this->pairX = $tile->x;
		$this->pairZ = $tile->z;

		$tile->pairX = $this->x;
		$tile->pairZ = $this->z;
	}

	/**
	 * @return bool
	 */
	public function unpair()
	{
		if (!$this->isPaired()) {
			return false;
		}

		$tile = $this->getPair();
		$this->pairX = $this->pairZ = null;

		$this->onChanged();

		if ($tile instanceof Chest) {
			$tile->pairX = $tile->pairZ = null;
			$tile->checkPairing();
			$this->onChanged();
		}
		$this->checkPairing();

		return true;
	}

	protected function addAdditionalSpawnData(CompoundTag $nbt, int $protocolVersion) : void
	{
		if ($this->isPaired()) {
			$nbt->setInt(self::TAG_PAIRX, $this->pairX);
			$nbt->setInt(self::TAG_PAIRZ, $this->pairZ);
		}

		$this->addNameSpawnData($nbt, $protocolVersion);
	}
}
