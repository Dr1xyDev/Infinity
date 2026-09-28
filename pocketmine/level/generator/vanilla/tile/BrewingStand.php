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

use pocketmine\level\generator\vanilla\event\block\BrewingFuelUseEvent;
use pocketmine\level\generator\vanilla\event\block\BrewItemEvent;
use pocketmine\level\generator\vanilla\inventory\BrewingRecipe;
use pocketmine\level\generator\vanilla\inventory\BrewingStandInventory;
use pocketmine\level\generator\vanilla\inventory\BrewingStandInventoryEventProcessor;
use pocketmine\level\generator\vanilla\inventory\InventoryHolder;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\item\ItemFactory;
use pocketmine\level\generator\vanilla\level\Level;
use pocketmine\level\generator\vanilla\level\sound\PotionFinishBrewingSound;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\nbt\tag\CompoundTag;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ContainerSetDataPacket;
use pocketmine\Player;

use function count;

class BrewingStand extends Spawnable implements InventoryHolder, Container, Nameable
{
	use NameableTrait {
		addAdditionalSpawnData as addNameSpawnData;
	}
	use ContainerTrait;

	public const BREW_TIME_TICKS = 400; // Brew time in ticks

	private const TAG_BREW_TIME = "BrewTime"; //TAG_Short
	private const TAG_BREW_TIME_PE = "CookTime"; //TAG_Short
	private const TAG_MAX_FUEL_TIME = "FuelTotal"; //TAG_Short
	private const TAG_REMAINING_FUEL_TIME = "Fuel"; //TAG_Byte
	private const TAG_REMAINING_FUEL_TIME_PE = "FuelAmount"; //TAG_Short

	private BrewingStandInventory $inventory;

	private int $brewTime = 0;
	private int $maxFuelTime = 0;
	private int $remainingFuelTime = 0;

	protected static function createAdditionalNBT(CompoundTag $nbt, Vector3 $pos, ?int $face = null, ?Item $item = null, ?Player $player = null) : void
	{
		$nbt->setShort(self::TAG_REMAINING_FUEL_TIME_PE, 20);
	}

	public function readSaveData(CompoundTag $nbt) : void
	{
		$this->inventory = new BrewingStandInventory($this);
		if ($this->level instanceof Level) {
			$this->inventory->setEventProcessor(new BrewingStandInventoryEventProcessor($this, $this->level));
		}

		$this->loadName($nbt);
		$this->loadItems($nbt);

		$this->brewTime = $nbt->getShort(self::TAG_BREW_TIME, $nbt->getShort(self::TAG_BREW_TIME_PE, 0));
		$this->maxFuelTime = $nbt->getShort(self::TAG_MAX_FUEL_TIME, 0);
		$this->remainingFuelTime = $nbt->getByte(self::TAG_REMAINING_FUEL_TIME, $nbt->getShort(self::TAG_REMAINING_FUEL_TIME_PE, 0));
		if ($this->maxFuelTime === 0) {
			$this->maxFuelTime = $this->remainingFuelTime;
		}
		if ($this->remainingFuelTime === 0) {
			$this->maxFuelTime = $this->remainingFuelTime = $this->brewTime = 0;
		}
	}

	protected function writeSaveData(CompoundTag $nbt) : void
	{
		$this->saveName($nbt);
		$this->saveItems($nbt);

		$nbt->setShort(self::TAG_BREW_TIME_PE, $this->brewTime);
		$nbt->setShort(self::TAG_MAX_FUEL_TIME, $this->maxFuelTime);
		$nbt->setShort(self::TAG_REMAINING_FUEL_TIME_PE, $this->remainingFuelTime);
	}

	protected function addAdditionalSpawnData(CompoundTag $nbt, int $protocolVersion) : void
	{
		$this->addNameSpawnData($nbt, $protocolVersion);

		$nbt->setShort(self::TAG_BREW_TIME_PE, $this->brewTime);
		$nbt->setShort(self::TAG_MAX_FUEL_TIME, $this->maxFuelTime);
		$nbt->setShort(self::TAG_REMAINING_FUEL_TIME_PE, $this->remainingFuelTime);
	}

	public function getDefaultName() : string
	{
		return "Brewing Stand";
	}

	public function close() : void
	{
		if (!$this->closed) {
			$this->inventory->removeAllViewers();

			parent::close();
		}
	}

	/**
	 * @return BrewingStandInventory
	 */
	public function getInventory()
	{
		return $this->inventory;
	}

	/**
	 * @return BrewingStandInventory
	 */
	public function getRealInventory()
	{
		return $this->inventory;
	}

	private function checkFuel(Item $item) : void
	{
		$ev = new BrewingFuelUseEvent($this);
		if (!$item->equals(ItemFactory::get(Item::BLAZE_POWDER), true, false)) {
			$ev->setCancelled();
		}

		$ev->call();
		if ($ev->isCancelled()) {
			return;
		}

		$item->pop();
		$this->inventory->setItem(BrewingStandInventory::SLOT_FUEL, $item);

		$this->maxFuelTime = $this->remainingFuelTime = $ev->getFuelTime();
	}

	/**
	 * @return BrewingRecipe[]
	 * @phpstan-return array<int, BrewingRecipe>
	 */
	private function getBrewableRecipes() : array
	{
		$ingredient = $this->inventory->getItem(BrewingStandInventory::SLOT_INGREDIENT);
		if ($ingredient->isNull()) {
			return [];
		}

		$recipes = [];
		foreach ([BrewingStandInventory::SLOT_BOTTLE_LEFT, BrewingStandInventory::SLOT_BOTTLE_MIDDLE, BrewingStandInventory::SLOT_BOTTLE_RIGHT] as $slot) {
			$input = $this->inventory->getItem($slot);
			if ($input->isNull()) {
				continue;
			}

			if (($recipe = $this->level->getServer()->getCraftingManager()->matchBrewingRecipe($input, $ingredient)) !== null) {
				$recipes[$slot] = $recipe;
			}
		}

		return $recipes;
	}

	public function onUpdate() : bool
	{
		if ($this->closed) {
			return false;
		}

		$this->timings->startTiming();

		$prevBrewTime = $this->brewTime;
		$prevRemainingFuelTime = $this->remainingFuelTime;
		$prevMaxFuelTime = $this->maxFuelTime;

		$ret = false;

		$fuel = $this->inventory->getItem(BrewingStandInventory::SLOT_FUEL);
		$ingredient = $this->inventory->getItem(BrewingStandInventory::SLOT_INGREDIENT);

		$recipes = $this->getBrewableRecipes();
		$canBrew = count($recipes) !== 0;
		if ($this->remainingFuelTime <= 0 && $canBrew) {
			$this->checkFuel($fuel);
		}

		if ($this->remainingFuelTime > 0) {
			if ($canBrew) {
				if ($this->brewTime === 0) {
					$this->brewTime = self::BREW_TIME_TICKS;
					--$this->remainingFuelTime;
				}

				--$this->brewTime;

				if ($this->brewTime <= 0) {
					$anythingBrewed = false;
					foreach ($recipes as $slot => $recipe) {
						$input = $this->inventory->getItem($slot);
						$output = $recipe->getResultFor($input);
						if ($output === null) {
							continue;
						}

						$ev = new BrewItemEvent($this, $slot, $input, $output, $recipe);
						$ev->call();
						if ($ev->isCancelled()) {
							continue;
						}

						$this->inventory->setItem($slot, $ev->getResult());
						$anythingBrewed = true;
					}

					if ($anythingBrewed) {
						$this->getLevel()->addSound(new PotionFinishBrewingSound($this->add(0.5, 0.5, 0.5)));
					}

					$ingredient->pop();
					$this->inventory->setItem(BrewingStandInventory::SLOT_INGREDIENT, $ingredient);

					$this->brewTime = 0;
				} else {
					$ret = true;
				}
			} else {
				$this->brewTime = 0;
			}
		} else {
			$this->brewTime = $this->remainingFuelTime = $this->maxFuelTime = 0;
		}

		/** @var ContainerSetDataPacket[] $packets */
		$packets = [];

		if ($prevBrewTime !== $this->brewTime) {
			$pk = new ContainerSetDataPacket();
			$pk->property = ContainerSetDataPacket::PROPERTY_BREWING_STAND_BREW_TIME;
			$pk->value = $this->brewTime;
			$packets[] = $pk;
		}

		if ($prevRemainingFuelTime !== $this->remainingFuelTime) {
			$pk = new ContainerSetDataPacket();
			$pk->property = ContainerSetDataPacket::PROPERTY_BREWING_STAND_FUEL_AMOUNT;
			$pk->value = $this->remainingFuelTime;
			$packets[] = $pk;
		}

		if ($prevMaxFuelTime !== $this->maxFuelTime) {
			$pk = new ContainerSetDataPacket();
			$pk->property = ContainerSetDataPacket::PROPERTY_BREWING_STAND_FUEL_TOTAL;
			$pk->value = $this->maxFuelTime;
			$packets[] = $pk;
		}

		foreach ($this->getInventory()->getViewers() as $player) {
			$windowId = $player->getWindowId($this->getInventory());
			if ($windowId > 0) {
				foreach ($packets as $pk) {
					$pk->windowId = $windowId;
					$player->sendDataPacket(clone $pk);
				}
			}
		}

		$this->timings->stopTiming();

		return $ret;
	}
}
