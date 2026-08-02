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

use pocketmine\nbt\NBT;

class FloatTag extends NamedTag{

	public function getType(){
		return NBT::TAG_Float;
	}

	public function read(NBT $nbt){
		$this->value = $nbt->getFloat();
	}

	public function write(NBT $nbt){
		$nbt->putFloat($this->value);
	}
}