<?php

/*
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.0
 *               InfinityProject By @Dr1xyDev
 *   YT:         @Dr1xyDev
 *   GitHub:     github.com/Dr1xyDev/Infinity
*/

namespace pocketmine\event\server;

use pocketmine\event;
use pocketmine\event\Cancellable;
use pocketmine\network\protocol\DataPacket;
use pocketmine\Player;

class DataPacketReceiveEvent extends ServerEvent implements Cancellable{
	public static $handlerList = null;

	private $packet;
	private $player;

	public function __construct(Player $player, /*DataPacket*/ $packet){
		$this->packet = $packet;
		$this->player = $player;
	}

	public function getPacket(){
		return $this->packet;
	}

	public function getPlayer(){
		return $this->player;
	}
}