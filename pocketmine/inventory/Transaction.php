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

use pocketmine\Player;
use pocketmine\item\Item;

interface Transaction{

	
	const TYPE_NORMAL = 0;
	const TYPE_DROP_ITEM = 1;

	
	public function getInventory();

	
	public function getSlot();

	
	public function getTargetItem();

	
	public function getCreationTime();

	
	public function execute(Player $source): bool;
}