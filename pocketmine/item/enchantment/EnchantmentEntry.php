<?php
/*    
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█    
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░    
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.1
 *               InfinityProject By @Dr1xyDev    
 *   YT:         @Dr1xyDev    
 *   GitHub:     github.com/Dr1xyDev/Infinity    
*/

namespace pocketmine\item\enchantment;

class EnchantmentEntry{

	
	private $enchantments;
	private $cost;
	private $randomName;

	
	public function __construct(array $enchantments, $cost, $randomName){
		$this->enchantments = $enchantments;
		$this->cost = (int) $cost;
		$this->randomName = $randomName;
	}

	public function getEnchantments(){
		return $this->enchantments;
	}

	public function getCost(){
		return $this->cost;
	}

	public function getRandomName(){
		return $this->randomName;
	}

}