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

namespace pocketmine\level\generator\vanilla\level\format\io\data;

use pocketmine\level\generator\vanilla\level\format\io\exception\CorruptedLevelException;
use pocketmine\level\generator\vanilla\level\format\io\exception\UnsupportedLevelFormatException;
use pocketmine\level\generator\vanilla\level\generator\FlatGenerator;
use pocketmine\level\generator\vanilla\level\generator\GeneratorManager;
use pocketmine\level\generator\vanilla\level\Level;
use pocketmine\level\generator\vanilla\level\LevelCreationOptions;
use pocketmine\level\generator\vanilla\nbt\LittleEndianNBTStream;
use pocketmine\level\generator\vanilla\nbt\tag\ByteTag;
use pocketmine\level\generator\vanilla\nbt\tag\CompoundTag;
use pocketmine\level\generator\vanilla\nbt\tag\FloatTag;
use pocketmine\level\generator\vanilla\nbt\tag\IntTag;
use pocketmine\level\generator\vanilla\nbt\tag\LongTag;
use pocketmine\level\generator\vanilla\nbt\tag\StringTag;
use pocketmine\level\generator\vanilla\utils\Binary;
use pocketmine\level\generator\vanilla\utils\Filesystem;
use Symfony\Component\Filesystem\Path;
use UnexpectedValueException;
use function file_put_contents;
use function strlen;
use function substr;
use function time;
use const LEVELDB_ZLIB_RAW_COMPRESSION;

class BedrockLevelData extends BaseNbtLevelData
{
	public const CURRENT_STORAGE_VERSION = 10;
	public const CURRENT_STORAGE_NETWORK_VERSION = 748;

	public const GENERATOR_LIMITED = 0;
	public const GENERATOR_INFINITE = 1;
	public const GENERATOR_FLAT = 2;

	public static function generate(string $path, string $name, LevelCreationOptions $options) : void
	{
		switch ($options->getGeneratorClass()) {
			case FlatGenerator::class:
				$generatorType = self::GENERATOR_FLAT;
				break;
			default:
				$generatorType = self::GENERATOR_INFINITE;
				//TODO: add support for limited worlds
		}

		$levelData = new CompoundTag("", [
			//Vanilla fields
			new IntTag("DayCycleStopTime", -1),
			new IntTag("Difficulty", $options->getDifficulty()),
			new ByteTag("ForceGameType", 0),
			new IntTag("GameType", 0),
			new IntTag("Generator", $generatorType),
			new LongTag("LastPlayed", time()),
			new StringTag("LevelName", $name),
			new IntTag("NetworkVersion", self::CURRENT_STORAGE_NETWORK_VERSION),
			//new IntTag("Platform", 2), //TODO: find out what the possible values are for
			new LongTag("RandomSeed", $options->getSeed()),
			new IntTag("SpawnX", $options->getSpawnPosition()->getFloorX()),
			new IntTag("SpawnY", $options->getSpawnPosition()->getFloorY()),
			new IntTag("SpawnZ", $options->getSpawnPosition()->getFloorZ()),
			new IntTag("StorageVersion", self::CURRENT_STORAGE_VERSION),
			new LongTag("Time", 0),
			new ByteTag("eduLevel", 0),
			new ByteTag("falldamage", 1),
			new ByteTag("firedamage", 1),
			new ByteTag("hasBeenLoadedInCreative", 1), //badly named, this actually determines whether achievements can be earned in this world...
			new ByteTag("immutableLevel", 0),
			new FloatTag("lightningLevel", 0.0),
			new IntTag("lightningTime", 0),
			new ByteTag("pvp", 1),
			new FloatTag("rainLevel", 0.0),
			new IntTag("rainTime", 0),
			new ByteTag("spawnMobs", 1),
			new ByteTag("texturePacksRequired", 0), //TODO
			new ByteTag("commandsEnabled", 1),

			//Additional PocketMine-MP fields
			new CompoundTag("GameRules", []),
			new ByteTag("hardcore", 0),
			new StringTag("generatorName", GeneratorManager::getGeneratorName($options->getGeneratorClass())),
			new StringTag("generatorOptions", $options->getGeneratorOptions()),
			new IntTag("CompressionType", LEVELDB_ZLIB_RAW_COMPRESSION),
		]);

		$nbt = new LittleEndianNBTStream();
		$buffer = $nbt->write($levelData);
		file_put_contents(Path::join($path, "level.dat"), Binary::writeLInt(self::CURRENT_STORAGE_VERSION) . Binary::writeLInt(strlen($buffer)) . $buffer);
	}

	protected function load() : CompoundTag
	{
		try {
			$rawLevelData = Filesystem::fileGetContents($this->dataPath);
		} catch (\RuntimeException $e) {
			throw new CorruptedLevelException($e->getMessage(), 0, $e);
		}
		if (strlen($rawLevelData) <= 8) {
			throw new CorruptedLevelException("Truncated level.dat");
		}
		$nbt = new LittleEndianNBTStream();
		try {
			$levelData = $nbt->read(substr($rawLevelData, 8));
			/** @var CompoundTag $levelData */
		} catch (UnexpectedValueException $e) {
			throw new CorruptedLevelException($e->getMessage(), 0, $e);
		}

		$version = $levelData->getInt("StorageVersion", INT32_MAX);
		if ($version > self::CURRENT_STORAGE_VERSION) {
			throw new UnsupportedLevelFormatException("LevelDB world format version $version is currently unsupported");
		}

		return $levelData;
	}

	protected function fix() : void
	{
		$generatorNameTag = $this->compoundTag->getTag("generatorName");
		if (!($generatorNameTag instanceof StringTag)) {
			if (($mcpeGeneratorTypeTag = $this->compoundTag->getTag("Generator")) instanceof IntTag) {
				switch ($mcpeGeneratorTypeTag->getValue()) { //Detect correct generator from MCPE data
					case self::GENERATOR_FLAT:
						$this->compoundTag->setString("generatorName", "flat");
						$this->compoundTag->setString("generatorOptions", "2;7,3,3,2;1");
						break;
					case self::GENERATOR_INFINITE:
						//TODO: add a null generator which does not generate missing chunks (to allow importing back to MCPE and generating more normal terrain without PocketMine messing things up)
						$this->compoundTag->setString("generatorName", "default");
						$this->compoundTag->setString("generatorOptions", "");
						break;
					case self::GENERATOR_LIMITED:
						throw new UnsupportedLevelFormatException("Limited worlds are not currently supported");
					default:
						throw new UnsupportedLevelFormatException("Unknown LevelDB generator type");
				}
			} else {
				$this->compoundTag->setString("generatorName", "default");
			}
		} elseif (($generatorName = self::hackyFixForGeneratorClasspathInLevelDat($generatorNameTag->getValue())) !== null) {
			$this->compoundTag->setString("generatorName", $generatorName);
		}

		if (!($this->compoundTag->getTag("generatorOptions")) instanceof StringTag) {
			$this->compoundTag->setString("generatorOptions", "");
		}
	}

	public function save() : void
	{
		$this->compoundTag->setInt("NetworkVersion", self::CURRENT_STORAGE_NETWORK_VERSION);
		$this->compoundTag->setInt("StorageVersion", self::CURRENT_STORAGE_VERSION);

		$nbt = new LittleEndianNBTStream();
		$buffer = $nbt->write($this->compoundTag);
		Filesystem::safeFilePutContents($this->dataPath, Binary::writeLInt(self::CURRENT_STORAGE_VERSION) . Binary::writeLInt(strlen($buffer)) . $buffer);
	}

	public function getCompressionType() : int
	{
		return $this->compoundTag->getInt("CompressionType", LEVELDB_ZLIB_RAW_COMPRESSION);
	}

	public function getDifficulty() : int
	{
		return $this->compoundTag->getInt("Difficulty", Level::DIFFICULTY_NORMAL);
	}

	public function setDifficulty(int $difficulty) : void
	{
		$this->compoundTag->setInt("Difficulty", $difficulty); //yes, this is intended! (in PE: int, PC: byte)
	}

	public function getRainTime() : int
	{
		return $this->compoundTag->getInt("rainTime", 0);
	}

	public function setRainTime(int $ticks) : void
	{
		$this->compoundTag->setInt("rainTime", $ticks);
	}

	public function getRainLevel() : float
	{
		return $this->compoundTag->getFloat("rainLevel", 0.0);
	}

	public function setRainLevel(float $level) : void
	{
		$this->compoundTag->setFloat("rainLevel", $level);
	}

	public function getLightningTime() : int
	{
		return $this->compoundTag->getInt("lightningTime", 0);
	}

	public function setLightningTime(int $ticks) : void
	{
		$this->compoundTag->setInt("lightningTime", $ticks);
	}

	public function getLightningLevel() : float
	{
		return $this->compoundTag->getFloat("lightningLevel", 0.0);
	}

	public function setLightningLevel(float $level) : void
	{
		$this->compoundTag->setFloat("lightningLevel", $level);
	}
}
