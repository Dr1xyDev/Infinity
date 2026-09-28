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

namespace pocketmine\level\generator\vanilla\event\player;

use pocketmine\level\generator\vanilla\event\Cancellable;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\Player;

/**
 * Called when a player uses a smithing table (upgrading an item or applying an armor trim).
 */
class PlayerUseSmithingTableEvent extends PlayerEvent implements Cancellable{

	public function __construct(
		Player $player,
		private Item $inputItem,
		private Item $resultItem
	){
		$this->player = $player;
	}

	/**
	 * Returns the item being upgraded (the item placed in the smithing table's input slot).
	 */
	public function getInputItem() : Item{
		return $this->inputItem;
	}

	/**
	 * Returns the item that the player will receive as a result of the smithing operation.
	 */
	public function getResultItem() : Item{
		return $this->resultItem;
	}
}
