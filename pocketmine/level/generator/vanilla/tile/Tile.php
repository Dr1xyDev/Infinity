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

/**
 * All the Tile classes and related classes
 */

namespace pocketmine\level\generator\vanilla\tile;

use BadMethodCallException;
use InvalidStateException;
use pocketmine\level\generator\vanilla\block\Block;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\level\ChunkManager;
use pocketmine\level\generator\vanilla\level\Level;
use pocketmine\level\generator\vanilla\level\Position;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\nbt\tag\CompoundTag;
use pocketmine\level\generator\vanilla\nbt\tag\IntTag;
use pocketmine\level\generator\vanilla\nbt\tag\StringTag;
use pocketmine\Player;
use pocketmine\level\generator\vanilla\timings\Timings;
use pocketmine\level\generator\vanilla\timings\TimingsHandler;
use ReflectionClass;
use ReflectionException;

use function in_array;
use function is_a;
use function reset;

abstract class Tile extends Position
{
	public const TAG_ID = "id";
	public const TAG_X = "x";
	public const TAG_Y = "y";
	public const TAG_Z = "z";

	public const BANNER = "Banner";
	public const BED = "Bed";
	public const BREWING_STAND = "BrewingStand";
	public const CAULDRON = "Cauldron";
	public const CHEST = "Chest";
	public const DECORATED_POT = "Decorated Pot";
	public const ENCHANT_TABLE = "EnchantTable";
	public const ENDER_CHEST = "EnderChest";
	public const FLOWER_POT = "FlowerPot";
	public const FURNACE = "Furnace";
	public const BLAST_FURNACE = "BlastFurnace";
	public const SMOKER = "Smoker";
	public const ITEM_FRAME = "ItemFrame";
	public const MOB_SPAWNER = "MobSpawner";
	public const SIGN = "Sign";
	public const SKULL = "Skull";
	public const BEACON = "Beacon";
	public const HOPPER = "Hopper";
	public const NOTE_BLOCK = "NoteBlock";
	public const JUKEBOX = "Jukebox";
	public const LECTERN = "Lectern";
	public const SHULKER_BOX = "ShulkerBox";
	public const CAMPFIRE = "Campfire";

	/** @var int */
	public static $tileCount = 1;

	/**
	 * @var string[] classes that extend Tile
	 * @phpstan-var array<string, class-string<Tile>>
	 */
	private static $knownTiles = [];
	/**
	 * @var string[]
	 * @phpstan-var array<class-string<Tile>, string>
	 */
	private static $saveNames = [];

	public string $name;
	public int $id;
	public bool $closed = false;
	protected TimingsHandler $timings;

	public static function init() : void
	{
		self::registerTile(Banner::class, [self::BANNER, "minecraft:banner"]);
		self::registerTile(Bed::class, [self::BED, "minecraft:bed"]);
		self::registerTile(BrewingStand::class, [self::BREWING_STAND, "minecraft:brewing_stand"]);
		self::registerTile(Chest::class, [self::CHEST, "minecraft:chest"]);
		self::registerTile(DecoratedPot::class, [self::DECORATED_POT, "minecraft:decorated_pot"]);
		self::registerTile(EnchantTable::class, [self::ENCHANT_TABLE, "minecraft:enchanting_table"]);
		self::registerTile(EnderChest::class, [self::ENDER_CHEST, "minecraft:ender_chest"]);
		self::registerTile(FlowerPot::class, [self::FLOWER_POT, "minecraft:flower_pot"]);
		self::registerTile(Furnace::class, [self::FURNACE, "minecraft:furnace"]);
		self::registerTile(BlastFurnace::class, [self::BLAST_FURNACE, "minecraft:blast_furnace"]);
		self::registerTile(Smoker::class, [self::SMOKER, "minecraft:smoker"]);
		self::registerTile(ItemFrame::class, [self::ITEM_FRAME]); //this is an entity in PC
		self::registerTile(Sign::class, [self::SIGN, "minecraft:sign"]);
		self::registerTile(Skull::class, [self::SKULL, "minecraft:skull"]);
		self::registerTile(NoteBlock::class, [self::NOTE_BLOCK, "minecraft:noteblock"]);
		self::registerTile(Jukebox::class, [self::JUKEBOX, "minecraft:jukebox"]);
		self::registerTile(Lectern::class, [self::LECTERN, "minecraft:lectern"]);
		self::registerTile(MobSpawner::class, [self::MOB_SPAWNER, "minecraft:mob_spawner"]);
		self::registerTile(ShulkerBox::class, [self::SHULKER_BOX, "minecraft:shulker_box"]);
		self::registerTile(Hopper::class, [self::HOPPER, "minecraft:hopper"]);
		self::registerTile(Beacon::class, [self::BEACON, "minecraft:beacon"]);
		self::registerTile(Cauldron::class, [self::CAULDRON, "minecraft:cauldron"]);
		self::registerTile(Campfire::class, [self::CAMPFIRE, "minecraft:campfire"]);
	}

	/**
	 * @param string $type
	 * @param mixed  ...$args
	 */
	public static function createTile($type, ChunkManager $level, CompoundTag $nbt, ...$args) : ?Tile
	{
		if (isset(self::$knownTiles[$type])) {
			$class = self::$knownTiles[$type];
			/** @see Tile::__construct() */
			return new $class($level, $nbt, ...$args);
		}

		return null;
	}

	/**
	 * @param string[] $saveNames
	 *
	 * @phpstan-param class-string<Tile> $className
	 *
	 * @throws ReflectionException
	 */
	public static function registerTile(string $className, array $saveNames = []) : bool
	{
		$class = new ReflectionClass($className);
		if (is_a($className, Tile::class, true) && !$class->isAbstract()) {
			$shortName = $class->getShortName();
			if (!in_array($shortName, $saveNames, true)) {
				$saveNames[] = $shortName;
			}

			foreach ($saveNames as $name) {
				self::$knownTiles[$name] = $className;
			}

			self::$saveNames[$className] = reset($saveNames);

			return true;
		}

		return false;
	}

	/**
	 * Returns the short save name
	 */
	public static function getSaveId() : string
	{
		if (!isset(self::$saveNames[static::class])) {
			throw new InvalidStateException("Tile is not registered");
		}

		return self::$saveNames[static::class];
	}

	public function __construct(ChunkManager $level, CompoundTag $nbt)
	{
		if ($level instanceof Level) {
			$this->timings = Timings::getTileEntityTimings($this);
		}

		$this->name = "";
		$this->id = Tile::$tileCount++;

		parent::__construct($nbt->getInt(self::TAG_X), $nbt->getInt(self::TAG_Y), $nbt->getInt(self::TAG_Z), ($level instanceof Level) ? $level : null);
		$this->readSaveData($nbt);

		$level->addTile($this);
	}

	public function getId() : int
	{
		return $this->id;
	}

	/**
	 * Reads additional data from the CompoundTag on tile creation.
	 */
	abstract protected function readSaveData(CompoundTag $nbt) : void;

	/**
	 * Writes additional save data to a CompoundTag, not including generic things like ID and coordinates.
	 */
	abstract protected function writeSaveData(CompoundTag $nbt) : void;

	public function saveNBT() : CompoundTag
	{
		$nbt = new CompoundTag();
		$nbt->setString(self::TAG_ID, static::getSaveId());
		$nbt->setInt(self::TAG_X, $this->x);
		$nbt->setInt(self::TAG_Y, $this->y);
		$nbt->setInt(self::TAG_Z, $this->z);
		$this->writeSaveData($nbt);

		return $nbt;
	}

	public function getCleanedNBT() : ?CompoundTag
	{
		$this->writeSaveData($tag = new CompoundTag());
		return $tag->getCount() > 0 ? $tag : null;
	}

	/**
	 * Creates and returns a CompoundTag containing the necessary information to spawn a tile of this type.
	 */
	public static function createNBT(Vector3 $pos, ?int $face = null, ?Item $item = null, ?Player $player = null) : CompoundTag
	{
		if (static::class === self::class) {
			throw new BadMethodCallException(__METHOD__ . " must be called from the scope of a child class");
		}
		$nbt = new CompoundTag("", [
			new StringTag(self::TAG_ID, static::getSaveId()),
			new IntTag(self::TAG_X, (int) $pos->x),
			new IntTag(self::TAG_Y, (int) $pos->y),
			new IntTag(self::TAG_Z, (int) $pos->z)
		]);

		static::createAdditionalNBT($nbt, $pos, $face, $item, $player);

		if ($item !== null) {
			$customBlockData = $item->getCustomBlockData();
			if ($customBlockData !== null) {
				foreach ($customBlockData as $customBlockDataTag) {
					$nbt->setTag(clone $customBlockDataTag);
				}
			}
		}

		return $nbt;
	}

	/**
	 * Called by createNBT() to allow descendent classes to add their own base NBT using the parameters provided.
	 */
	protected static function createAdditionalNBT(CompoundTag $nbt, Vector3 $pos, ?int $face = null, ?Item $item = null, ?Player $player = null) : void
	{

	}

	public function getBlock() : Block
	{
		return $this->level->getBlockAt($this->x, $this->y, $this->z);
	}

	public function onUpdate() : bool
	{
		return false;
	}

	public function isClosed() : bool
	{
		return $this->closed;
	}

	public function __destruct()
	{
		$this->close();
	}

	public function close() : void
	{
		if (!$this->closed) {
			$this->closed = true;

			if ($this->isValid()) {
				$this->level->removeTile($this);
				$this->setLevel(null);
			}
		}
	}

	public function getName() : string
	{
		return $this->name;
	}
}
