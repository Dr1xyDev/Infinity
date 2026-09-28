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

use pocketmine\level\generator\vanilla\block\Block;
use pocketmine\level\generator\vanilla\event\Cancellable;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\Player;
use pocketmine\level\generator\vanilla\tile\ItemFrame;

class ItemFrameDropItemEvent extends BlockEvent implements Cancellable
{
	/** @var  Player */
	private $player;
	/** @var  Item */
	private $item;
	/** @var  ItemFrame */
	private $itemFrame;

	/**
	 * ItemFrameDropItemEvent constructor.
	 */
	public function __construct(Player $player, Block $block, ItemFrame $itemFrame, Item $item)
	{
		$this->player = $player;
		$this->block = $block;
		$this->itemFrame = $itemFrame;
		$this->item = $item;
	}

	/**
	 * @return Player
	 */
	public function getPlayer()
	{
		return $this->player;
	}

	/**
	 * @return ItemFrame
	 */
	public function getItemFrame()
	{
		return $this->itemFrame;
	}

	/**
	 * @return Item
	 */
	public function getItem()
	{
		return $this->item;
	}
}
