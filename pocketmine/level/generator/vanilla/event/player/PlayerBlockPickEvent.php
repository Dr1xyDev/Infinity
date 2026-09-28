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

use pocketmine\level\generator\vanilla\block\Block;
use pocketmine\level\generator\vanilla\event\Cancellable;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\Player;

/**
 * Called when a player middle-clicks on a block to get an item in creative mode.
 */
class PlayerBlockPickEvent extends PlayerEvent implements Cancellable
{
	/** @var Block */
	private $blockClicked;
	/** @var Item */
	private $resultItem;

	public function __construct(Player $player, Block $blockClicked, Item $resultItem)
	{
		$this->player = $player;
		$this->blockClicked = $blockClicked;
		$this->resultItem = $resultItem;
	}

	public function getBlock() : Block
	{
		return $this->blockClicked;
	}

	public function getResultItem() : Item
	{
		return $this->resultItem;
	}

	public function setResultItem(Item $item) : void
	{
		$this->resultItem = clone $item;
	}
}
