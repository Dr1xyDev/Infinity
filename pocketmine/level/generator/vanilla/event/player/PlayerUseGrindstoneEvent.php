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
 * Called when a player takes the result out of a grindstone (disenchanting and/or repairing items).
 */
class PlayerUseGrindstoneEvent extends PlayerEvent implements Cancellable{

	/**
	 * @param Item[] $inputItems
	 */
	public function __construct(
		Player $player,
		private array $inputItems,
		private Item $resultItem,
		private int $experience
	){
		$this->player = $player;
	}

	/**
	 * @return Item[]
	 */
	public function getInputItems() : array{
		return $this->inputItems;
	}

	public function getResultItem() : Item{
		return $this->resultItem;
	}

	/**
	 * Amount of experience dropped by the grindstone
	 */
	public function getExperience() : int{
		return $this->experience;
	}

	public function setExperience(int $experience) : void{
		if ($experience < 0) {
			throw new \InvalidArgumentException("Experience must not be negative");
		}
		$this->experience = $experience;
	}
}
