<?php

/*
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.0
 *               InfinityProject By @Dr1xyDev
 *   YT:         @Dr1xyDev
 *   GitHub:     github.com/Dr1xyDev/Infinity
*/

namespace pocketmine\block;


class NetherReactor extends Solid{

	protected $id = self::NETHER_REACTOR;

	public function __construct($meta = 0){
		$this->meta = (int) $meta;
	}

	public function getName() : string{
		return "Nether Reactor";
	}

	public function canBeActivated() : bool {
		return true;
	}

}