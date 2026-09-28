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

namespace pocketmine\level\generator\vanilla\block;

use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\item\ItemFactory;

abstract class DoubleSlab extends Solid
{
	public function __construct(int $meta = 0)
	{
		$this->meta = $meta;
	}

	abstract public function getSlabId() : int;

	public function getName() : string
	{
		return "Double " . BlockFactory::get($this->getSlabId(), $this->getVariant())->getName();
	}

	public function getDropsForCompatibleTool(Item $item) : array
	{
		return [
			ItemFactory::get($this->getSlabId(), $this->getVariant(), 2)
		];
	}

	public function isAffectedBySilkTouch() : bool
	{
		return false;
	}

	public function getPickedItem(bool $addUserData = false) : Item
	{
		return ItemFactory::get($this->getSlabId(), $this->getVariant());
	}
}
