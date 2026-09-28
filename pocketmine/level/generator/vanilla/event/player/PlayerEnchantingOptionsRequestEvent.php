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
use pocketmine\level\generator\vanilla\inventory\EnchantInventory;
use pocketmine\level\generator\vanilla\item\enchantment\EnchantingOption;
use pocketmine\Player;
use pocketmine\level\generator\vanilla\utils\Utils;
use function count;

/**
 * Called when a player inserts an item into an enchanting table's input slot.
 * The options provided by the event will be shown on the enchanting table menu.
 */
class PlayerEnchantingOptionsRequestEvent extends PlayerEvent implements Cancellable{

	/**
	 * @param EnchantingOption[] $options
	 */
	public function __construct(
		Player $player,
		private readonly EnchantInventory $inventory,
		private array $options
	){
		$this->player = $player;
	}

	public function getInventory() : EnchantInventory{
		return $this->inventory;
	}

	/**
	 * @return EnchantingOption[]
	 */
	public function getOptions() : array{
		return $this->options;
	}

	/**
	 * @param EnchantingOption[] $options
	 */
	public function setOptions(array $options) : void{
		Utils::validateArrayValueType($options, function(EnchantingOption $_) : void{ });
		if(($optionCount = count($options)) > 3){
			throw new \LogicException("The maximum number of options for an enchanting table is 3, but $optionCount have been passed");
		}

		$this->options = $options;
	}
}
