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

namespace pocketmine\level\generator\vanilla\event\block;

use pocketmine\level\generator\vanilla\block\Anvil;
use pocketmine\level\generator\vanilla\event\Cancellable;

class AnvilUseEvent extends BlockEvent implements Cancellable{

	public function __construct(
		private Anvil $anvil,
		private bool $takeDamage = false
	){
		parent::__construct($this->anvil);
	}

	public function getAnvil() : Anvil{
		return $this->anvil;
	}

	public function shouldTakeDamage() : bool{
		return $this->takeDamage;
	}

	public function setTakeDamage(bool $takeDamage) : void{
		$this->takeDamage = $takeDamage;
	}
}
