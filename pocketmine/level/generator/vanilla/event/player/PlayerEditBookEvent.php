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

use pocketmine\level\generator\vanilla\event\Cancellable;
use pocketmine\level\generator\vanilla\item\WritableBook;
use pocketmine\Player;

class PlayerEditBookEvent extends PlayerEvent implements Cancellable
{
	public const ACTION_REPLACE_PAGE = 0;
	public const ACTION_ADD_PAGE = 1;
	public const ACTION_DELETE_PAGE = 2;
	public const ACTION_SWAP_PAGES = 3;
	public const ACTION_SIGN_BOOK = 4;

	/** @var WritableBook */
	private $oldBook;
	/** @var int */
	private $action;
	/** @var WritableBook */
	private $newBook;
	/** @var int[] */
	private $modifiedPages;

	/**
	 * @param int[] $modifiedPages
	 */
	public function __construct(Player $player, WritableBook $oldBook, WritableBook $newBook, int $action, array $modifiedPages)
	{
		$this->player = $player;
		$this->oldBook = $oldBook;
		$this->newBook = $newBook;
		$this->action = $action;
		$this->modifiedPages = $modifiedPages;
	}

	/**
	 * Returns the action of the event.
	 */
	public function getAction() : int
	{
		return $this->action;
	}

	/**
	 * Returns the book before it was modified.
	 */
	public function getOldBook() : WritableBook
	{
		return $this->oldBook;
	}

	/**
	 * Returns the book after it was modified.
	 * The new book may be a written book, if the book was signed.
	 */
	public function getNewBook() : WritableBook
	{
		return $this->newBook;
	}

	/**
	 * Sets the new book as the given instance.
	 */
	public function setNewBook(WritableBook $book) : void
	{
		$this->newBook = $book;
	}

	/**
	 * Returns an array containing the page IDs of modified pages.
	 *
	 * @return int[]
	 */
	public function getModifiedPages() : array
	{
		return $this->modifiedPages;
	}
}
