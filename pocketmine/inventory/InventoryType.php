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

class InventoryType{
	const CHEST = 0;
	const DOUBLE_CHEST = 1;
	const PLAYER = 2;
	const FURNACE = 3;
	const CRAFTING = 4;
	const WORKBENCH = 5;
	
	const BREWING_STAND = 7;
	const ANVIL = 8;
	const ENCHANT_TABLE = 9;
	const DISPENSER = 10;
	const DROPPER = 11;
	const HOPPER = 12;

	const PLAYER_FLOATING = 254;

	private static $default = [];

	private $size;
	private $title;
	private $typeId;

	
	public static function get($index){
		return isset(static::$default[$index]) ? static::$default[$index] : null;
	}

	public static function init(){
		if(count(static::$default) > 0){
			return;
		}

		static::$default[static::CHEST] = new InventoryType(27, "Chest", 0);
		static::$default[static::DOUBLE_CHEST] = new InventoryType(27 + 27, "Double Chest", 0);
		static::$default[static::PLAYER] = new InventoryType(36 + 4, "Player", 0); 
		static::$default[static::FURNACE] = new InventoryType(3, "Furnace", 2);
		static::$default[static::CRAFTING] = new InventoryType(5, "Crafting", 1); 
		static::$default[static::WORKBENCH] = new InventoryType(10, "Crafting", 1); 
		static::$default[static::ENCHANT_TABLE] = new InventoryType(2, "Enchant", 3); 
		static::$default[static::BREWING_STAND] = new InventoryType(4, "Brewing", 4); 
		static::$default[static::ANVIL] = new InventoryType(3, "Anvil", 5); 
		static::$default[static::DISPENSER] = new InventoryType(9, "Dispenser", 6); 
		static::$default[static::DROPPER] = new InventoryType(9, "Dropper", 7); 
		static::$default[static::HOPPER] = new InventoryType(5, "Hopper", 8); 

		static::$default[static::PLAYER_FLOATING] = new InventoryType(36, "Floating", null); 
	}

	
	private function __construct($defaultSize, $defaultTitle, $typeId = 0){
		$this->size = $defaultSize;
		$this->title = $defaultTitle;
		$this->typeId = $typeId;
	}

	
	public function getDefaultSize(){
		return $this->size;
	}

	
	public function getDefaultTitle(){
		return $this->title;
	}

	
	public function getNetworkType(){
		return $this->typeId;
	}
}