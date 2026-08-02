<?php
/*    
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█    
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░    
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.1
 *               InfinityProject By @Dr1xyDev    
 *   YT:         @Dr1xyDev    
 *   GitHub:     github.com/Dr1xyDev/Infinity    
*/

namespace pocketmine\nbt\tag;

abstract class NamedTag extends Tag{

	protected $__name;

	
	public function __construct($name = "", $value = null){
		$this->__name = ($name === null or $name === false) ? "" : $name;
		if($value !== null){
			$this->value = $value;
		}
	}

	public function getName(){
		return $this->__name;
	}

	public function setName($name){
		$this->__name = $name;
	}
}