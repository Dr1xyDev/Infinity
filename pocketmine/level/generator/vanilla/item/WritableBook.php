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

namespace pocketmine\level\generator\vanilla\item;

use InvalidArgumentException;
use pocketmine\level\generator\vanilla\nbt\NBT;
use pocketmine\level\generator\vanilla\nbt\tag\CompoundTag;
use pocketmine\level\generator\vanilla\nbt\tag\ListTag;
use pocketmine\level\generator\vanilla\nbt\tag\StringTag;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ProtocolInfo;

class WritableBook extends Item
{
	public const TAG_PAGES = "pages"; //TAG_List<TAG_Compound>
	public const TAG_PAGE_TEXT = "text"; //TAG_String
	public const TAG_PAGE_PHOTONAME = "photoname"; //TAG_String - TODO

	public function __construct(int $meta = 0)
	{
		parent::__construct(self::WRITABLE_BOOK, $meta, "Book & Quill");
	}

	/**
	 * Returns whether the given page exists in this book.
	 */
	public function pageExists(int $pageId) : bool
	{
		return $this->getPagesTag()->isset($pageId);
	}

	/**
	 * Returns a string containing the content of a page (which could be empty), or null if the page doesn't exist.
	 */
	public function getPageText(int $pageId) : ?string
	{
		$pages = $this->getNamedTag()->getListTag(self::TAG_PAGES);
		if ($pages === null || !$pages->isset($pageId)) {
			return null;
		}

		$page = $pages->get($pageId);
		if ($page instanceof CompoundTag) {
			return $page->getString(self::TAG_PAGE_TEXT, "");
		}

		return null;
	}

	/**
	 * Sets the text of a page in the book. Adds the page if the page does not yet exist.
	 *
	 * @return bool indicating whether the page was created or not.
	 */
	public function setPageText(int $pageId, string $pageText) : bool
	{
		$created = false;
		if (!$this->pageExists($pageId)) {
			$this->addPage($pageId);
			$created = true;
		}

		/** @var CompoundTag[]|ListTag $pagesTag */
		$pagesTag = $this->getPagesTag();
		/** @var CompoundTag $page */
		$page = $pagesTag->get($pageId);
		$page->setString(self::TAG_PAGE_TEXT, $pageText);

		$this->setNamedTagEntry($pagesTag);

		return $created;
	}

	/**
	 * Adds a new page with the given page ID.
	 * Creates a new page for every page between the given ID and existing pages that doesn't yet exist.
	 */
	public function addPage(int $pageId) : void
	{
		if ($pageId < 0) {
			throw new InvalidArgumentException("Page number \"$pageId\" is out of range");
		}

		$pagesTag = $this->getPagesTag();

		for ($current = $pagesTag->count(); $current <= $pageId; $current++) {
			$pagesTag->push(new CompoundTag("", [
				new StringTag(self::TAG_PAGE_TEXT, ""),
				new StringTag(self::TAG_PAGE_PHOTONAME, "")
			]));
		}

		$this->setNamedTagEntry($pagesTag);
	}

	/**
	 * Deletes an existing page with the given page ID.
	 *
	 * @return bool indicating success
	 */
	public function deletePage(int $pageId) : bool
	{
		$pagesTag = $this->getPagesTag();
		$pagesTag->remove($pageId);
		$this->setNamedTagEntry($pagesTag);

		return true;
	}

	/**
	 * Inserts a new page with the given text and moves other pages upwards.
	 *
	 * @return bool indicating success
	 */
	public function insertPage(int $pageId, string $pageText = "") : bool
	{
		$pagesTag = $this->getPagesTag();

		$pagesTag->insert($pageId, new CompoundTag("", [
			new StringTag(self::TAG_PAGE_TEXT, $pageText),
			new StringTag(self::TAG_PAGE_PHOTONAME, "")
		]));

		$this->setNamedTagEntry($pagesTag);

		return true;
	}

	/**
	 * Switches the text of two pages with each other.
	 *
	 * @return bool indicating success
	 */
	public function swapPages(int $pageId1, int $pageId2) : bool
	{
		if (!$this->pageExists($pageId1) || !$this->pageExists($pageId2)) {
			return false;
		}

		$pageContents1 = $this->getPageText($pageId1);
		$pageContents2 = $this->getPageText($pageId2);
		$this->setPageText($pageId1, $pageContents2);
		$this->setPageText($pageId2, $pageContents1);

		return true;
	}

	public function getMaxStackSize() : int
	{
		return 1;
	}

	/**
	 * Returns an array containing all pages of this book.
	 *
	 * @return CompoundTag[]
	 */
	public function getPages() : array
	{
		/** @var CompoundTag[] $pages */
		$pages = $this->getPagesTag()->getValue();

		return $pages;
	}

	protected function getPagesTag() : ListTag
	{
		$pagesTag = $this->getNamedTag()->getListTag(self::TAG_PAGES);
		if ($pagesTag !== null && $pagesTag->getTagType() === NBT::TAG_Compound) {
			return $pagesTag;
		}
		return new ListTag(self::TAG_PAGES, [], NBT::TAG_Compound);
	}

	/**
	 * @param CompoundTag[] $pages
	 */
	public function setPages(array $pages) : void
	{
		$nbt = $this->getNamedTag();
		$nbt->setTag(new ListTag(self::TAG_PAGES, $pages, NBT::TAG_Compound));
		$this->setNamedTag($nbt);
	}

	public function getItemProtocol(int $playerProtocol) : ?TranslatedItemData
	{
		if ($playerProtocol < ProtocolInfo::PROTOCOL_407) {
			return new TranslatedItemData(ItemIds::BOOK, $this->getDamage(), $this->getName());
		}

		return parent::getItemProtocol($playerProtocol);
	}
}
