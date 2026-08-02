<?php
/*    
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█    
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░    
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.1
 *               InfinityProject By @Dr1xyDev    
 *   YT:         @Dr1xyDev    
 *   GitHub:     github.com/Dr1xyDev/Infinity    
*/

namespace pocketmine\inventory;

use pocketmine\item\Item;
use pocketmine\tile\Furnace;

class FurnaceInventory extends ContainerInventory{

	const SMELTING = 0;
	const FUEL = 1;
	const RESULT = 2;

	public function __construct(Furnace $tile){
		parent::__construct($tile, InventoryType::get(InventoryType::FURNACE));
	}

	
	public function getHolder(){
		return $this->holder;
	}

	
	public function getResult(){
		return $this->getItem(self::RESULT);
	}

	
	public function getFuel(){
		return $this->getItem(self::FUEL);
	}

	
	public function getSmelting(){
		return $this->getItem(self::SMELTING);
	}

	
	public function setResult(Item $item){
		return $this->setItem(self::RESULT, $item);
	}

	
	public function setFuel(Item $item){
		return $this->setItem(self::FUEL, $item);
	}

	
	public function setSmelting(Item $item){
		return $this->setItem(self::SMELTING, $item);
	}

	public function onSlotChange($index, $before, $send){
		parent::onSlotChange($index, $before, $send);

		$this->getHolder()->scheduleUpdate();
	}
}
