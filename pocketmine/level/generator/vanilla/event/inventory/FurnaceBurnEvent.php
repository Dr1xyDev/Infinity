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

namespace pocketmine\level\generator\vanilla\event\inventory;

use pocketmine\level\generator\vanilla\event\block\BlockEvent;
use pocketmine\level\generator\vanilla\event\Cancellable;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\tile\Furnace;

/**
 * Called when a furnace is about to consume a new fuel item.
 */
class FurnaceBurnEvent extends BlockEvent implements Cancellable
{
	/** @var Furnace */
	private $furnace;
	/** @var Item */
	private $fuel;
	/** @var int */
	private $burnTime;
	/** @var bool */
	private $burning = true;

	public function __construct(Furnace $furnace, Item $fuel, int $burnTime)
	{
		parent::__construct($furnace->getBlock());
		$this->fuel = $fuel;
		$this->burnTime = $burnTime;
		$this->furnace = $furnace;
	}

	public function getFurnace() : Furnace
	{
		return $this->furnace;
	}

	public function getFuel() : Item
	{
		return $this->fuel;
	}

	/**
	 * Returns the number of ticks that the furnace will be powered for.
	 */
	public function getBurnTime() : int
	{
		return $this->burnTime;
	}

	/**
	 * Sets the number of ticks that the given fuel will power the furnace for.
	 */
	public function setBurnTime(int $burnTime) : void
	{
		$this->burnTime = $burnTime;
	}

	/**
	 * Returns whether the fuel item will be consumed.
	 */
	public function isBurning() : bool
	{
		return $this->burning;
	}

	/**
	 * Sets whether the fuel will be consumed. If false, the furnace will smelt as if it consumed fuel, but no fuel
	 * will be deducted.
	 */
	public function setBurning(bool $burning) : void
	{
		$this->burning = $burning;
	}
}
