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
use pocketmine\Player;

interface Inventory{
	const MAX_STACK = 64;

	public function getSize();

	public function getMaxStackSize();

	
	public function setMaxStackSize($size);

	public function getName();

	public function getTitle();

	
	public function getItem($index);

	
	public function setItem($index, Item $item);

	
	public function addItem(...$slots);

	
	public function canAddItem(Item $item);

	
	public function removeItem(...$slots);

	
	public function getContents();

	
	public function setContents(array $items);

	
	public function sendContents($target);

	
	public function sendSlot($index, $target);

	
	public function contains(Item $item);

	
	public function all(Item $item);

	
	public function first(Item $item);

	
	public function firstEmpty();

	
	public function remove(Item $item);

	
	public function clear($index);

	
	public function clearAll();

	
	public function getViewers();

	
	public function getType();

	
	public function getHolder();

	
	public function onOpen(Player $who);

	
	public function open(Player $who);

	public function close(Player $who);

	
	public function onClose(Player $who);

	
	public function onSlotChange($index, $before, $send);
}
