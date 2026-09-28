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
use pocketmine\level\generator\vanilla\inventory\transaction\EnchantingTransaction;
use pocketmine\level\generator\vanilla\item\enchantment\EnchantingOption;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\Player;

/**
 * Called when a player enchants an item using an enchanting table.
 */
class PlayerItemEnchantEvent extends PlayerEvent implements Cancellable{

	public function __construct(
		Player $player,
		private readonly EnchantingTransaction $transaction,
		private readonly EnchantingOption $option,
		private readonly Item $inputItem,
		private readonly Item $outputItem,
		private readonly int $cost
	){
		$this->player = $player;
	}

	/**
	 * Returns the inventory transaction involved in this enchant event.
	 */
	public function getTransaction() : EnchantingTransaction{
		return $this->transaction;
	}

	/**
	 * Returns the enchantment option used.
	 */
	public function getOption() : EnchantingOption{
		return $this->option;
	}

	/**
	 * Returns the item to be enchanted.
	 */
	public function getInputItem() : Item{
		return clone $this->inputItem;
	}

	/**
	 * Returns the enchanted item.
	 */
	public function getOutputItem() : Item{
		return clone $this->outputItem;
	}

	/**
	 * Returns the number of XP levels and lapis that will be subtracted after enchanting
	 * if the player is not in creative mode.
	 */
	public function getCost() : int{
		return $this->cost;
	}
}
