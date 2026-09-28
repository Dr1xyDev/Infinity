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
 * @allowHandle
 */
abstract class PlayerBucketEvent extends PlayerEvent implements Cancellable
{
	/** @var Block */
	private $blockClicked;
	/** @var int */
	private $blockFace;
	/** @var Item */
	private $bucket;
	/** @var Item */
	private $item;

	public function __construct(Player $who, Block $blockClicked, int $blockFace, Item $bucket, Item $itemInHand)
	{
		$this->player = $who;
		$this->blockClicked = $blockClicked;
		$this->blockFace = $blockFace;
		$this->item = $itemInHand;
		$this->bucket = $bucket;
	}

	/**
	 * Returns the bucket used in this event
	 */
	public function getBucket() : Item
	{
		return $this->bucket;
	}

	/**
	 * Returns the item in hand after the event
	 */
	public function getItem() : Item
	{
		return $this->item;
	}

	public function setItem(Item $item) : void
	{
		$this->item = $item;
	}

	public function getBlockClicked() : Block
	{
		return $this->blockClicked;
	}

	public function getBlockFace() : int
	{
		return $this->blockFace;
	}
}
