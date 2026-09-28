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

use pocketmine\level\generator\vanilla\block\Block;
use pocketmine\level\generator\vanilla\block\BlockFactory;
use pocketmine\level\generator\vanilla\nbt\tag\CompoundTag;
use pocketmine\level\generator\vanilla\nbt\tag\IntTag;
use pocketmine\level\generator\vanilla\nbt\tag\ListTag;
use pocketmine\level\generator\vanilla\nbt\tag\StringTag;
use pocketmine\level\generator\vanilla\tile\Banner as TileBanner;

use function assert;

class Banner extends Item
{
	public const TAG_BASE = TileBanner::TAG_BASE;
	public const TAG_PATTERNS = TileBanner::TAG_PATTERNS;
	public const TAG_PATTERN_COLOR = TileBanner::TAG_PATTERN_COLOR;
	public const TAG_PATTERN_NAME = TileBanner::TAG_PATTERN_NAME;

	public function __construct(int $meta = 0)
	{
		parent::__construct(self::BANNER, $meta, "Banner");
	}

	public function getBlock() : Block
	{
		return BlockFactory::get(Block::STANDING_BANNER);
	}

	public function getMaxStackSize() : int
	{
		return 16;
	}

	/**
	 * Returns the color of the banner base.
	 */
	public function getBaseColor() : int
	{
		return $this->getNamedTag()->getInt(self::TAG_BASE, 0);
	}

	/**
	 * Sets the color of the banner base.
	 * Banner items have to be resent to see the changes in the inventory.
	 */
	public function setBaseColor(int $color) : void
	{
		$namedTag = $this->getNamedTag();
		$namedTag->setInt(self::TAG_BASE, $color & 0x0f);
		$this->setNamedTag($namedTag);
	}

	/**
	 * Applies a new pattern on the banner with the given color.
	 * Banner items have to be resent to see the changes in the inventory.
	 *
	 * @return int ID of pattern.
	 */
	public function addPattern(string $pattern, int $color) : int
	{
		$patternsTag = $this->getNamedTag()->getListTag(self::TAG_PATTERNS);
		assert($patternsTag !== null);

		$patternsTag->push(new CompoundTag("", [
			new IntTag(self::TAG_PATTERN_COLOR, $color & 0x0f),
			new StringTag(self::TAG_PATTERN_NAME, $pattern)
		]));

		$this->setNamedTagEntry($patternsTag);

		return $patternsTag->count() - 1;
	}

	/**
	 * Returns whether a pattern with the given ID exists on the banner or not.
	 */
	public function patternExists(int $patternId) : bool
	{
		$this->correctNBT();
		return $this->getNamedTag()->getListTag(self::TAG_PATTERNS)->isset($patternId);
	}

	/**
	 * Returns the data of a pattern with the given ID.
	 *
	 * @return mixed[]
	 * @phpstan-return array{Color?: int, Pattern?: string}
	 */
	public function getPatternData(int $patternId) : array
	{
		if (!$this->patternExists($patternId)) {
			return [];
		}

		$patternsTag = $this->getNamedTag()->getListTag(self::TAG_PATTERNS);
		assert($patternsTag !== null);
		$pattern = $patternsTag->get($patternId);
		assert($pattern instanceof CompoundTag);

		return [
			self::TAG_PATTERN_COLOR => $pattern->getInt(self::TAG_PATTERN_COLOR),
			self::TAG_PATTERN_NAME => $pattern->getString(self::TAG_PATTERN_NAME)
		];
	}

	/**
	 * Changes the pattern of a previously existing pattern.
	 * Banner items have to be resent to see the changes in the inventory.
	 *
	 * @return bool indicating success.
	 */
	public function changePattern(int $patternId, string $pattern, int $color) : bool
	{
		if (!$this->patternExists($patternId)) {
			return false;
		}

		$patternsTag = $this->getNamedTag()->getListTag(self::TAG_PATTERNS);
		assert($patternsTag !== null);

		$patternsTag->set($patternId, new CompoundTag("", [
			new IntTag(self::TAG_PATTERN_COLOR, $color & 0x0f),
			new StringTag(self::TAG_PATTERN_NAME, $pattern)
		]));

		$this->setNamedTagEntry($patternsTag);
		return true;
	}

	/**
	 * Deletes a pattern from the banner with the given ID.
	 * Banner items have to be resent to see the changes in the inventory.
	 *
	 * @return bool indicating whether the pattern existed or not.
	 */
	public function deletePattern(int $patternId) : bool
	{
		if (!$this->patternExists($patternId)) {
			return false;
		}

		$patternsTag = $this->getNamedTag()->getListTag(self::TAG_PATTERNS);
		if ($patternsTag instanceof ListTag) {
			$patternsTag->remove($patternId);
			$this->setNamedTagEntry($patternsTag);
		}

		return true;
	}

	/**
	 * Deletes the top most pattern of the banner.
	 * Banner items have to be resent to see the changes in the inventory.
	 *
	 * @return bool indicating whether the banner was empty or not.
	 */
	public function deleteTopPattern() : bool
	{
		return $this->deletePattern($this->getPatternCount() - 1);
	}

	/**
	 * Deletes the bottom pattern of the banner.
	 * Banner items have to be resent to see the changes in the inventory.
	 *
	 * @return bool indicating whether the banner was empty or not.
	 */
	public function deleteBottomPattern() : bool
	{
		return $this->deletePattern(0);
	}

	/**
	 * Returns the total count of patterns on this banner.
	 */
	public function getPatternCount() : int
	{
		return $this->getNamedTag()->getListTag(self::TAG_PATTERNS)->count();
	}

	public function correctNBT() : void
	{
		$tag = $this->getNamedTag();
		if (!$tag->hasTag(self::TAG_BASE, IntTag::class)) {
			$tag->setInt(self::TAG_BASE, 0);
		}

		if (!$tag->hasTag(self::TAG_PATTERNS, ListTag::class)) {
			$tag->setTag(new ListTag(self::TAG_PATTERNS));
		}
		$this->setNamedTag($tag);
	}

	public function getFuelTime() : int
	{
		return 300;
	}
}
