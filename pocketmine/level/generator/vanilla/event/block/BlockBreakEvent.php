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

use InvalidArgumentException;
use pocketmine\level\generator\vanilla\block\Block;
use pocketmine\level\generator\vanilla\event\Cancellable;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\Player;

/**
 * Called when a player destroys a block somewhere in the world.
 */
class BlockBreakEvent extends BlockEvent implements Cancellable
{
	/** @var Player */
	protected $player;

	/** @var Item */
	protected $item;

	/** @var bool */
	protected $instaBreak = false;
	/** @var Item[] */
	protected $blockDrops = [];
	/** @var int */
	protected $xpDrops;

	/**
	 * @param Item[] $drops
	 */
	public function __construct(Player $player, Block $block, Item $item, bool $instaBreak = false, array $drops = [], int $xpDrops = 0)
	{
		parent::__construct($block);
		$this->item = $item;
		$this->player = $player;

		$this->instaBreak = $instaBreak;
		$this->setDrops($drops);
		$this->xpDrops = $xpDrops;
	}

	/**
	 * Returns the player who is destroying the block.
	 */
	public function getPlayer() : Player
	{
		return $this->player;
	}

	/**
	 * Returns the item used to destroy the block.
	 */
	public function getItem() : Item
	{
		return $this->item;
	}

	/**
	 * Returns whether the block may be broken in less than the amount of time calculated. This is usually true for
	 * creative players.
	 */
	public function getInstaBreak() : bool
	{
		return $this->instaBreak;
	}

	public function setInstaBreak(bool $instaBreak) : void
	{
		$this->instaBreak = $instaBreak;
	}

	/**
	 * @return Item[]
	 */
	public function getDrops() : array
	{
		return $this->blockDrops;
	}

	/**
	 * @param Item[] $drops
	 */
	public function setDrops(array $drops) : void
	{
		$this->setDropsVariadic(...$drops);
	}

	/**
	 * Variadic hack for easy array member type enforcement.
	 */
	public function setDropsVariadic(Item ...$drops) : void
	{
		$this->blockDrops = $drops;
	}

	/**
	 * Returns how much XP will be dropped by breaking this block.
	 */
	public function getXpDropAmount() : int
	{
		return $this->xpDrops;
	}

	/**
	 * Sets how much XP will be dropped by breaking this block.
	 */
	public function setXpDropAmount(int $amount) : void
	{
		if ($amount < 0) {
			throw new InvalidArgumentException("Amount must be at least zero");
		}
		$this->xpDrops = $amount;
	}
}
