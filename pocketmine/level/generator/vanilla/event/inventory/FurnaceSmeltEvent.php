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

class FurnaceSmeltEvent extends BlockEvent implements Cancellable
{
	/** @var Furnace */
	private $furnace;
	/** @var Item */
	private $source;
	/** @var Item */
	private $result;

	public function __construct(Furnace $furnace, Item $source, Item $result)
	{
		parent::__construct($furnace->getBlock());
		$this->source = clone $source;
		$this->source->setCount(1);
		$this->result = $result;
		$this->furnace = $furnace;
	}

	public function getFurnace() : Furnace
	{
		return $this->furnace;
	}

	public function getSource() : Item
	{
		return $this->source;
	}

	public function getResult() : Item
	{
		return $this->result;
	}

	public function setResult(Item $result) : void
	{
		$this->result = $result;
	}
}
