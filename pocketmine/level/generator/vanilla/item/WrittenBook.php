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

class WrittenBook extends WritableBook
{
	public const GENERATION_ORIGINAL = 0;
	public const GENERATION_COPY = 1;
	public const GENERATION_COPY_OF_COPY = 2;
	public const GENERATION_TATTERED = 3;

	public const TAG_GENERATION = "generation"; //TAG_Int
	public const TAG_AUTHOR = "author"; //TAG_String
	public const TAG_TITLE = "title"; //TAG_String

	public function __construct(int $meta = 0)
	{
		Item::__construct(self::WRITTEN_BOOK, $meta, "Written Book");
	}

	public function getMaxStackSize() : int
	{
		return 16;
	}

	/**
	 * Returns the generation of the book.
	 * Generations higher than 1 can not be copied.
	 */
	public function getGeneration() : int
	{
		return $this->getNamedTag()->getInt(self::TAG_GENERATION, -1);
	}

	/**
	 * Sets the generation of a book.
	 */
	public function setGeneration(int $generation) : void
	{
		if ($generation < 0 || $generation > 3) {
			throw new InvalidArgumentException("Generation \"$generation\" is out of range");
		}
		$namedTag = $this->getNamedTag();
		$namedTag->setInt(self::TAG_GENERATION, $generation);
		$this->setNamedTag($namedTag);
	}

	/**
	 * Returns the author of this book.
	 * This is not a reliable way to get the name of the player who signed this book.
	 * The author can be set to anything when signing a book.
	 */
	public function getAuthor() : string
	{
		return $this->getNamedTag()->getString(self::TAG_AUTHOR, "");
	}

	/**
	 * Sets the author of this book.
	 */
	public function setAuthor(string $authorName) : void
	{
		$namedTag = $this->getNamedTag();
		$namedTag->setString(self::TAG_AUTHOR, $authorName);
		$this->setNamedTag($namedTag);
	}

	/**
	 * Returns the title of this book.
	 */
	public function getTitle() : string
	{
		return $this->getNamedTag()->getString(self::TAG_TITLE, "");
	}

	/**
	 * Sets the author of this book.
	 */
	public function setTitle(string $title) : void
	{
		$namedTag = $this->getNamedTag();
		$namedTag->setString(self::TAG_TITLE, $title);
		$this->setNamedTag($namedTag);
	}
}
