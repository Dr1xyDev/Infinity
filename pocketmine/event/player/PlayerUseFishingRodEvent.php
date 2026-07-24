<?php

/*
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.0
 *               InfinityProject By @Dr1xyDev
 *   YT:         @Dr1xyDev
 *   GitHub:     github.com/Dr1xyDev/Infinity
*/

namespace pocketmine\event\player;

use pocketmine\event\Cancellable;
use pocketmine\Player;

class PlayerUseFishingRodEvent extends PlayerEvent implements Cancellable{
	public static $handlerList = null;

	const ACTION_START_FISHING = 0;
	const ACTION_STOP_FISHING = 1;

	private $action;

	public function __construct(Player $player, int $action = PlayerUseFishingRodEvent::ACTION_START_FISHING){
		$this->player = $player;
		$this->action = $action;
	}

	public function getAction() : int{
		return $this->action;
	}
}