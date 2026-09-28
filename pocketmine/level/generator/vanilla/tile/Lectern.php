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

namespace pocketmine\level\generator\vanilla\tile;

use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\item\WritableBook;
use pocketmine\level\generator\vanilla\nbt\tag\CompoundTag;
use function count;
use function max;
use function min;

class Lectern extends Spawnable
{
	public const TAG_HAS_BOOK = "hasBook";
	public const TAG_BOOK = "book";
	public const TAG_PAGE = "page";
	public const TAG_TOTAL_PAGES = "totalPages";

	protected ?WritableBook $book = null;
	protected int $page = 0;

	public function getDefaultName() : string
	{
		return "Lectern";
	}

	public function hasBook() : bool
	{
		return $this->book !== null;
	}

	public function getBook() : ?WritableBook
	{
		return $this->book !== null ? clone $this->book : null;
	}

	/**
	 * Puts a book and quill or a written book on the lectern (null removes it), opening it at the first page
	 */
	public function setBook(?WritableBook $book) : void
	{
		$this->book = $book !== null ? (clone $book)->setCount(1) : null;
		$this->page = 0;
		$this->onChanged();
	}

	public function getPage() : int
	{
		return $this->page;
	}

	public function getTotalPages() : int
	{
		return $this->book !== null ? count($this->book->getPages()) : 0;
	}

	/**
	 * @return bool whether the page changed
	 */
	public function setPage(int $page) : bool
	{
		$page = max(0, min($page, $this->getTotalPages() - 1));
		if ($page === $this->page) {
			return false;
		}
		$this->page = $page;
		$this->onChanged();
		return true;
	}

	/**
	 * Drops the book (if any) on top of the lectern
	 */
	public function dropBook() : void
	{
		if ($this->book !== null && $this->level !== null) {
			$this->level->dropItem($this->add(0.5, 1, 0.5), $this->book);
		}
		$this->setBook(null);
	}

	protected function readSaveData(CompoundTag $nbt) : void
	{
		$this->book = null;
		if ($nbt->getByte(self::TAG_HAS_BOOK, 0) !== 0 && $nbt->hasTag(self::TAG_BOOK, CompoundTag::class)) {
			$book = Item::nbtDeserialize($nbt->getCompoundTag(self::TAG_BOOK));
			if ($book instanceof WritableBook) {
				$this->book = $book;
			}
		}
		$this->page = max(0, $nbt->getInt(self::TAG_PAGE, 0));
	}

	protected function writeSaveData(CompoundTag $nbt) : void
	{
		$nbt->setByte(self::TAG_HAS_BOOK, $this->book !== null ? 1 : 0);
		if ($this->book !== null) {
			$nbt->setTag($this->book->nbtSerialize(-1, self::TAG_BOOK));
		}
		$nbt->setInt(self::TAG_PAGE, $this->page);
		$nbt->setInt(self::TAG_TOTAL_PAGES, $this->getTotalPages());
	}

	protected function addAdditionalSpawnData(CompoundTag $nbt, int $protocolVersion) : void
	{
		$nbt->setByte(self::TAG_HAS_BOOK, $this->book !== null ? 1 : 0);
		if ($this->book !== null) {
			$nbt->setTag($this->book->nbtSerialize(-1, self::TAG_BOOK, $protocolVersion));
		}
		$nbt->setInt(self::TAG_PAGE, $this->page);
		$nbt->setInt(self::TAG_TOTAL_PAGES, $this->getTotalPages());
	}
}
