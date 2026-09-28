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

use pocketmine\level\generator\vanilla\block\Block;
use pocketmine\level\generator\vanilla\block\BlockFactory;
use pocketmine\level\generator\vanilla\block\BlockIds;
use pocketmine\level\generator\vanilla\event\inventory\FurnaceBurnEvent;
use pocketmine\level\generator\vanilla\event\inventory\FurnaceSmeltEvent;
use pocketmine\level\generator\vanilla\inventory\FurnaceInventory;
use pocketmine\level\generator\vanilla\inventory\FurnaceInventoryEventProcessor;
use pocketmine\level\generator\vanilla\inventory\FurnaceRecipe;
use pocketmine\level\generator\vanilla\inventory\FurnaceType;
use pocketmine\level\generator\vanilla\inventory\InventoryHolder;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\level\Level;
use pocketmine\level\generator\vanilla\nbt\tag\CompoundTag;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ContainerSetDataPacket;

use function count;
use function max;

class Furnace extends Spawnable implements InventoryHolder, Container, Nameable
{
	use NameableTrait {
		addAdditionalSpawnData as addNameSpawnData;
	}
	use ContainerTrait;

	public const TAG_BURN_TIME = "BurnTime";
	public const TAG_COOK_TIME = "CookTime";
	public const TAG_MAX_TIME = "MaxTime";

	protected FurnaceInventory $inventory;
	private int $remainingFuelTime = 0;
	private int $cookTime = 0;
	private int $maxFuelTime = 0;

	protected function readSaveData(CompoundTag $nbt) : void{
		$this->inventory = new FurnaceInventory($this);
		if ($this->level instanceof Level) {
			$this->inventory->setEventProcessor(new FurnaceInventoryEventProcessor($this, $this->level));
		}

		$this->remainingFuelTime = max(0, $nbt->getShort(self::TAG_BURN_TIME, $this->remainingFuelTime, true));

		$this->cookTime = $nbt->getShort(self::TAG_COOK_TIME, $this->cookTime, true);
		if ($this->remainingFuelTime === 0) {
			$this->cookTime = 0;
		}

		$this->maxFuelTime = $nbt->getShort(self::TAG_MAX_TIME, $this->maxFuelTime, true);
		if ($this->maxFuelTime === 0) {
			$this->maxFuelTime = $this->remainingFuelTime;
		}

		$this->loadName($nbt);
		$this->loadItems($nbt);

		if ($this->level instanceof Level && $this->remainingFuelTime > 0) {
			$this->level->scheduleDelayedBlockUpdate($this, 1);
		}
	}

	protected function writeSaveData(CompoundTag $nbt) : void
	{
		$nbt->setShort(self::TAG_BURN_TIME, $this->remainingFuelTime);
		$nbt->setShort(self::TAG_COOK_TIME, $this->cookTime);
		$nbt->setShort(self::TAG_MAX_TIME, $this->maxFuelTime);
		$this->saveName($nbt);
		$this->saveItems($nbt);
	}

	public function getDefaultName() : string
	{
		return "Furnace";
	}

	public function close() : void
	{
		if (!$this->closed) {
			$this->inventory->removeAllViewers(true);

			parent::close();
		}
	}

	public function getInventory() : FurnaceInventory
	{
		return $this->inventory;
	}

	public function getRealInventory() : FurnaceInventory
	{
		return $this->getInventory();
	}

	protected function checkFuel(Item $fuel) : void{
		$ev = new FurnaceBurnEvent($this, $fuel, $fuel->getFuelTime());
		$ev->call();
		if ($ev->isCancelled()) {
			return;
		}

		$this->maxFuelTime = $this->remainingFuelTime = $ev->getBurnTime();
		$this->onStartSmelting();

		if ($this->remainingFuelTime > 0 && $ev->isBurning()) {
			$this->inventory->setFuel($fuel->getFuelResidue());
		}
	}

	protected function onStartSmelting() : void{
		$block = $this->getBlock();
		if ($block->getId() === BlockIds::FURNACE) {
			$this->getLevel()->setBlock($this, BlockFactory::get(BlockIds::BURNING_FURNACE, $block->getDamage()), true);
		}
	}

	protected function onStopSmelting() : void{
		$block = $this->getBlock();
		if ($block->getId() === BlockIds::BURNING_FURNACE) {
			$this->getLevel()->setBlock($this, BlockFactory::get(BlockIds::FURNACE, $block->getDamage()), true);
		}
	}

	public function getFurnaceType() : FurnaceType {
		return FurnaceType::FURNACE;
	}

	public function onUpdate() : bool
	{
		//TODO: move this to Block
		if ($this->closed) {
			return false;
		}

		$this->timings->startTiming();

		$prevCookTime = $this->cookTime;
		$prevRemainingFuelTime = $this->remainingFuelTime;
		$prevMaxFuelTime = $this->maxFuelTime;

		$ret = false;

		$fuel = $this->inventory->getFuel();
		$raw = $this->inventory->getSmelting();
		$product = $this->inventory->getResult();

		$furnaceType = $this->getFurnaceType();
		$smelt = $this->level->getServer()->getCraftingManager()->matchFurnaceRecipe($raw, $furnaceType);
		$canSmelt = ($smelt instanceof FurnaceRecipe && $raw->getCount() > 0 && (($smelt->getResult()->canStackWith($product) && $product->getCount() < $product->getMaxStackSize()) || $product->isNull()));

		if($this->remainingFuelTime <= 0 && $canSmelt && $fuel->getFuelTime() > 0 && $fuel->getCount() > 0){
			$this->checkFuel($fuel);
		}

		if($this->remainingFuelTime > 0){
			--$this->remainingFuelTime;

			if($smelt instanceof FurnaceRecipe && $canSmelt){
				++$this->cookTime;

				if($this->cookTime >= $furnaceType->getCookDurationTicks()){
					$product = $smelt->getResult()->setCount($product->getCount() + 1);

					$ev = new FurnaceSmeltEvent($this, $raw, $product);
					$ev->call();

					if(!$ev->isCancelled()){
						$this->inventory->setResult($ev->getResult());
						$raw->pop();
						$this->inventory->setSmelting($raw);
					}

					$this->cookTime -= $furnaceType->getCookDurationTicks();
				}
			}elseif($this->remainingFuelTime <= 0){
				$this->remainingFuelTime = $this->cookTime = $this->maxFuelTime = 0;
			}else{
				$this->cookTime = 0;
			}
			$ret = true;
		}else{
			$this->onStopSmelting();
			$this->remainingFuelTime = $this->cookTime = $this->maxFuelTime = 0;
		}

		$packets = [];
		if($prevCookTime !== $this->cookTime){
			$pk = new ContainerSetDataPacket();
			$pk->property = ContainerSetDataPacket::PROPERTY_FURNACE_TICK_COUNT;
			$pk->value = $this->cookTime;
			$packets[] = $pk;
		}
		if($prevRemainingFuelTime !== $this->remainingFuelTime){
			$pk = new ContainerSetDataPacket();
			$pk->property = ContainerSetDataPacket::PROPERTY_FURNACE_LIT_TIME;
			$pk->value = $this->remainingFuelTime;
			$packets[] = $pk;
		}
		if($prevMaxFuelTime !== $this->maxFuelTime){
			$pk = new ContainerSetDataPacket();
			$pk->property = ContainerSetDataPacket::PROPERTY_FURNACE_LIT_DURATION;
			$pk->value = $this->maxFuelTime;
			$packets[] = $pk;
		}

		if (count($packets) > 0) {
			foreach ($this->getInventory()->getViewers() as $player) {
				$windowId = $player->getWindowId($this->getInventory());
				if ($windowId > 0) {
					foreach ($packets as $pk) {
						$pk->windowId = $windowId;
						$player->sendDataPacket(clone $pk);
					}
				}
			}
		}

		$this->timings->stopTiming();

		return $ret;
	}

	protected function addAdditionalSpawnData(CompoundTag $nbt, int $protocolVersion) : void
	{
		$nbt->setShort(self::TAG_BURN_TIME, $this->remainingFuelTime);
		$nbt->setShort(self::TAG_COOK_TIME, $this->cookTime);

		$this->addNameSpawnData($nbt, $protocolVersion);
	}
}
