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

/**
 * Called when a player has its gamemode changed
 */
class PlayerGameModeChangeEvent extends PlayerEvent implements Cancellable{
	public static $handlerList = null;

	/** @var int */
	protected $gamemode;

	public function __construct(Player $player, $newGamemode){
		$this->player = $player;
		$this->gamemode = (int) $newGamemode;
	}

	public function getNewGamemode(){
		return $this->gamemode;
	}

}