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

namespace pocketmine\level\generator\vanilla\inventory;

use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\inventory\WindowTypes;
use pocketmine\level\generator\vanilla\tile\Furnace;

class FurnaceInventory extends ContainerInventory
{
	/** @var Furnace */
	protected $holder;

	public function __construct(Furnace $tile)
	{
		parent::__construct($tile);
	}

	public function getNetworkType() : int
	{
		return match($this->holder->getFurnaceType()){
			FurnaceType::BLAST_FURNACE => WindowTypes::BLAST_FURNACE,
			FurnaceType::SMOKER => WindowTypes::SMOKER,
			default => WindowTypes::FURNACE
		};
	}

	public function getName() : string
	{
		return "Furnace";
	}

	public function getDefaultSize() : int
	{
		return 3; //1 input, 1 fuel, 1 output
	}

	/**
	 * This override is here for documentation and code completion purposes only.
	 * @return Furnace
	 */
	public function getHolder()
	{
		return $this->holder;
	}

	public function getResult() : Item
	{
		return $this->getItem(2);
	}

	public function getFuel() : Item
	{
		return $this->getItem(1);
	}

	public function getSmelting() : Item
	{
		return $this->getItem(0);
	}

	public function setResult(Item $item) : bool
	{
		return $this->setItem(2, $item);
	}

	public function setFuel(Item $item) : bool
	{
		return $this->setItem(1, $item);
	}

	public function setSmelting(Item $item) : bool
	{
		return $this->setItem(0, $item);
	}
}
