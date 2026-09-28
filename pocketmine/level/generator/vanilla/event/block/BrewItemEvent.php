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

use pocketmine\level\generator\vanilla\event\Cancellable;
use pocketmine\level\generator\vanilla\inventory\BrewingRecipe;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\tile\BrewingStand;

class BrewItemEvent extends BlockEvent implements Cancellable
{
	public function __construct(
		private BrewingStand $brewingStand,
		private int $slot,
		private Item $input,
		private Item $result,
		private BrewingRecipe $recipe
	) {
		parent::__construct($brewingStand->getBlock());
	}

	public function getBrewingStand() : BrewingStand
	{
		return $this->brewingStand;
	}

	/**
	 * Returns which slot of the brewing stand's inventory the potion is in.
	 */
	public function getSlot() : int
	{
		return $this->slot;
	}

	public function getInput() : Item
	{
		return clone $this->input;
	}

	public function getResult() : Item
	{
		return clone $this->result;
	}

	public function setResult(Item $result) : void
	{
		$this->result = clone $result;
	}

	public function getRecipe() : BrewingRecipe
	{
		return $this->recipe;
	}
}
