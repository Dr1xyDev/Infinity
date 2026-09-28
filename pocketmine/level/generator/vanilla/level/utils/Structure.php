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

namespace pocketmine\level\generator\vanilla\level\utils;

use Exception;
use InvalidStateException;
use pocketmine\level\generator\vanilla\entity\Entity;
use pocketmine\level\generator\vanilla\item\ItemBlock;
use pocketmine\level\generator\vanilla\item\ItemFactory;
use pocketmine\level\generator\vanilla\level\Level;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\nbt\BigEndianNBTStream;
use pocketmine\level\generator\vanilla\nbt\tag\CompoundTag;
use pocketmine\level\generator\vanilla\nbt\tag\IntTag;
use pocketmine\level\generator\vanilla\nbt\tag\ListTag;
use pocketmine\level\generator\vanilla\nbt\tag\StringTag;
use UnexpectedValueException;
use function file_get_contents;
use function is_file;

class Structure
{
	// TODO: creating a structure nbt from an area

	public const TAG_BLOCKS = "blocks";
	public const TAG_ENTITIES = "entities";
	public const TAG_SIZE = "size";
	public const TAG_PALETTE = "palette";
	public const TAG_AUTHOR = "author";
	public const TAG_VERSION = "version";
	public const TAG_DATA_VERSION = "DataVersion";

	public const TAG_BLOCK_NAME = "Name";
	public const TAG_BLOCK_PROPERTIES = "Properties";
	public const TAG_BLOCK_POS = "pos";
	public const TAG_BLOCK_STATE = "state";

	/** @var CompoundTag */
	protected $nbt;

	public function __construct(string $structurePath)
	{
		if (is_file($structurePath)) {
			$stream = new BigEndianNBTStream();

			$data = $stream->readCompressed(file_get_contents($structurePath));

			if ($data instanceof CompoundTag) {
				if ($this->verifyStructureNbt($data)) {
					$this->nbt = $data;
				} else {
					throw new InvalidStateException("Structure: Given Nbt is not verified");
				}
			} else {
				throw new UnexpectedValueException("Structure: Unexpected nbt data is given");
			}
		} else {
			throw new Exception("Structure: Wrong path is given");
		}
	}

	private function verifyStructureNbt(CompoundTag $nbt) : bool
	{
		return $nbt->hasTag(self::TAG_BLOCKS, ListTag::class) &&
			$nbt->hasTag(self::TAG_ENTITIES, ListTag::class) &&
			$nbt->hasTag(self::TAG_SIZE, ListTag::class) &&
			$nbt->hasTag(self::TAG_PALETTE, ListTag::class);
	}

	public function getAuthor() : string
	{
		return $this->nbt->getString(self::TAG_AUTHOR, "");
	}

	public function getVersion() : int
	{
		return $this->nbt->getInt(self::TAG_VERSION, 0);
	}

	public function getDataVersion() : int
	{
		return $this->nbt->getInt(self::TAG_DATA_VERSION, 0);
	}

	/**
	 * @return int[]
	 */
	public function getSize() : array
	{
		return $this->nbt->getListTag(self::TAG_SIZE)->getAllValues();
	}

	/**
	 * @return CompoundTag[]
	 */
	public function getEntities() : array
	{
		return $this->nbt->getListTag(self::TAG_ENTITIES)->getAllValues();
	}

	/**
	 * @return CompoundTag[]
	 */
	public function getBlocks() : array
	{
		return $this->nbt->getListTag(self::TAG_BLOCKS)->getAllValues();
	}

	/**
	 * @return CompoundTag[]
	 */
	public function getPalette() : array
	{
		return $this->nbt->getListTag(self::TAG_PALETTE)->getAllValues();
	}

	public function place(Level $level, Vector3 $baseVector, bool $spawnEntities = true) : void
	{
		$palette = $this->getPalette();

		$tempVector = new Vector3(0, 0, 0);

		foreach ($this->getBlocks() as $b) {
			if ($b instanceof CompoundTag) {
				if ($b->hasTag(self::TAG_BLOCK_STATE, IntTag::class) && $b->hasTag(self::TAG_BLOCK_POS, ListTag::class)) {
					$pos = $b->getListTag(self::TAG_BLOCK_POS)->getAllValues();
					$state = $b->getInt(self::TAG_BLOCK_STATE);

					if (isset($palette[$state])) {
						$type = $palette[$state];
						if ($type instanceof CompoundTag && $type->hasTag(self::TAG_BLOCK_NAME, StringTag::class)) {
							try {
								$item = ItemFactory::fromString($type->getString(self::TAG_BLOCK_NAME));
							} catch (Exception $e) {
								continue; // unexpected block id given, continue
							}

							if ($item instanceof ItemBlock) {
								$block = $item->getBlock();

								if ($type->hasTag(self::TAG_BLOCK_PROPERTIES, CompoundTag::class)) {
									// TODO
									// this may just when api is 4.0 because we need block state implementation to do this
								}

								$level->setBlock($baseVector->add(...$pos), $block, true, true);
							}
						}
					}
				}
			}
		}

		if ($spawnEntities) {
			foreach ($this->getEntities() as $tag) {
				if ($tag instanceof CompoundTag) {
					if ($tag->hasTag("id")) {
						$entity = Entity::createEntity($tag->getTag("id")->getValue(), $level, $tag);

						if ($entity !== null) {
							$entity->spawnToAll();
						}
					}
				}
			}
		}
	}
}
