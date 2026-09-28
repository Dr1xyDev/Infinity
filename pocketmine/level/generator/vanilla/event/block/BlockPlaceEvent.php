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

/**
 * Called when a player places a block
 */
class BlockPlaceEvent extends BlockEvent implements Cancellable
{
	/** @var Player */
	protected $player;

	/** @var Item */
	protected $item;

	/** @var Block */
	protected $blockReplace;
	/** @var Block */
	protected $blockAgainst;

	public function __construct(Player $player, Block $blockPlace, Block $blockReplace, Block $blockAgainst, Item $item)
	{
		parent::__construct($blockPlace);
		$this->blockReplace = $blockReplace;
		$this->blockAgainst = $blockAgainst;
		$this->item = $item;
		$this->player = $player;
	}

	/**
	 * Returns the player who is placing the block.
	 */
	public function getPlayer() : Player
	{
		return $this->player;
	}

	/**
	 * Gets the item in hand
	 */
	public function getItem() : Item
	{
		return $this->item;
	}

	public function getBlockReplaced() : Block
	{
		return $this->blockReplace;
	}

	public function getBlockAgainst() : Block
	{
		return $this->blockAgainst;
	}
}
