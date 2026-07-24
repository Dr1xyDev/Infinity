<?php

/*
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.0
 *               InfinityProject By @Dr1xyDev
 *   YT:         @Dr1xyDev
 *   GitHub:     github.com/Dr1xyDev/Infinity
*/

namespace pocketmine\nbt\tag;

use pocketmine\nbt\NBT;

#include <rules/NBT.h>

class StringTag extends NamedTag{
	
	public function getType(){
		return NBT::TAG_String;
	}

	public function read(NBT $nbt){
		$this->value = $nbt->get($nbt->getShort());
	}

	public function write(NBT $nbt){
		$nbt->putShort(strlen($this->value));
		$nbt->put($this->value);
	}
}