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

use BadMethodCallException;
use InvalidStateException;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\inventory\UIInventorySlotOffset;
use pocketmine\Player;

use function max;
use function min;

use const PHP_INT_MAX;

class CraftingGrid extends BaseInventory implements FakeInventory
{
	public const SIZE_SMALL = 2;
	public const SIZE_BIG = 3;

	/** @var Player */
	protected $holder;
	/** @var int */
	private $gridWidth;

	/** @var int|null */
	private $startX;
	/** @var int|null */
	private $xLen;
	/** @var int|null */
	private $startY;
	/** @var int|null */
	private $yLen;

	public function __construct(Player $holder, int $gridWidth)
	{
		$this->holder = $holder;
		$this->gridWidth = $gridWidth;

		parent::__construct();
	}

	public function getGridWidth() : int
	{
		return $this->gridWidth;
	}

	public function getDefaultSize() : int
	{
		return $this->getGridWidth() ** 2;
	}

	public function getUIOffsets(?Player $player) : array
	{
		return $this->gridWidth === self::SIZE_SMALL ? UIInventorySlotOffset::CRAFTING2X2_INPUT : UIInventorySlotOffset::CRAFTING3X3_INPUT;
	}

	public function setSize(int $size)
	{
		throw new BadMethodCallException("Cannot change the size of a crafting grid");
	}

	public function getName() : string
	{
		return "Crafting";
	}

	public function setItem(int $index, Item $item, bool $send = true) : bool
	{
		if (parent::setItem($index, $item, $send)) {
			$this->seekRecipeBounds();

			return true;
		}

		return false;
	}

	public function sendSlot(int $index, $target) : void
	{
		//we can't send a slot of a client-sided inventory window
	}

	public function sendContents($target) : void
	{
		//no way to do this
	}

	/**
	 * @return Player
	 */
	public function getHolder()
	{
		return $this->holder;
	}

	private function seekRecipeBounds() : void
	{
		$minX = PHP_INT_MAX;
		$maxX = 0;

		$minY = PHP_INT_MAX;
		$maxY = 0;

		$empty = true;

		for ($y = 0; $y < $this->gridWidth; ++$y) {
			for ($x = 0; $x < $this->gridWidth; ++$x) {
				if (!$this->isSlotEmpty($y * $this->gridWidth + $x)) {
					$minX = min($minX, $x);
					$maxX = max($maxX, $x);

					$minY = min($minY, $y);
					$maxY = max($maxY, $y);

					$empty = false;
				}
			}
		}

		if (!$empty) {
			$this->startX = $minX;
			$this->xLen = $maxX - $minX + 1;
			$this->startY = $minY;
			$this->yLen = $maxY - $minY + 1;
		} else {
			$this->startX = $this->xLen = $this->startY = $this->yLen = null;
		}
	}

	/**
	 * Returns the item at offset x,y, offset by where the starts of the recipe rectangle are.
	 */
	public function getIngredient(int $x, int $y) : Item
	{
		if ($this->startX !== null && $this->startY !== null) {
			return $this->getItem(($y + $this->startY) * $this->gridWidth + ($x + $this->startX));
		}

		throw new InvalidStateException("No ingredients found in grid");
	}

	/**
	 * Returns the width of the recipe we're trying to craft, based on items currently in the grid.
	 */
	public function getRecipeWidth() : int
	{
		return $this->xLen ?? 0;
	}

	/**
	 * Returns the height of the recipe we're trying to craft, based on items currently in the grid.
	 */
	public function getRecipeHeight() : int
	{
		return $this->yLen ?? 0;
	}
}
