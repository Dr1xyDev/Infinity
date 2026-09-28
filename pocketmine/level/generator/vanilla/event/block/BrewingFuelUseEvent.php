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
use pocketmine\level\generator\vanilla\tile\BrewingStand;

/**
 * Called when a brewing stand consumes a new fuel item.
 */
class BrewingFuelUseEvent extends BlockEvent implements Cancellable
{
	private int $fuelTime = 20;

	public function __construct(
		private BrewingStand $brewingStand
	) {
		parent::__construct($brewingStand->getBlock());
	}

	public function getBrewingStand() : BrewingStand
	{
		return $this->brewingStand;
	}

	/**
	 * Returns how many times the fuel can be used for potion brewing before it runs out.
	 */
	public function getFuelTime() : int
	{
		return $this->fuelTime;
	}

	/**
	 * Sets how many times the fuel can be used for potion brewing before it runs out.
	 */
	public function setFuelTime(int $fuelTime) : void
	{
		if ($fuelTime <= 0) {
			throw new \InvalidArgumentException("Fuel time must be positive");
		}
		$this->fuelTime = $fuelTime;
	}
}
