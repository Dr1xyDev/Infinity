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
use pocketmine\level\generator\vanilla\level\generator\GeneratorManager;
use pocketmine\level\generator\vanilla\level\Level;
use pocketmine\level\generator\vanilla\level\LevelCreationOptions;
use pocketmine\level\generator\vanilla\nbt\BigEndianNBTStream;
use pocketmine\level\generator\vanilla\nbt\tag\ByteTag;
use pocketmine\level\generator\vanilla\nbt\tag\CompoundTag;
use pocketmine\level\generator\vanilla\nbt\tag\FloatTag;
use pocketmine\level\generator\vanilla\nbt\tag\IntTag;
use pocketmine\level\generator\vanilla\nbt\tag\LongTag;
use pocketmine\level\generator\vanilla\nbt\tag\StringTag;
use pocketmine\level\generator\vanilla\utils\Filesystem;
use pocketmine\level\generator\vanilla\utils\Utils;
use Symfony\Component\Filesystem\Path;
use UnexpectedValueException;
use function ceil;
use function file_put_contents;
use function microtime;
use function zlib_decode;
use function zlib_encode;
use const ZLIB_ENCODING_GZIP;

class JavaLevelData extends BaseNbtLevelData
{
	public static function generate(string $path, string $name, LevelCreationOptions $options, int $version = 19133) : void
	{
		//TODO, add extra details

		$levelData = new CompoundTag("Data", [
			new ByteTag("hardcore", 0),
			new ByteTag("Difficulty", $options->getDifficulty()),
			new ByteTag("initialized", 1),
			new IntTag("GameType", 0),
			new IntTag("generatorVersion", 1), //2 in MCPE
			new IntTag("SpawnX", $options->getSpawnPosition()->getFloorX()),
			new IntTag("SpawnY", $options->getSpawnPosition()->getFloorY()),
			new IntTag("SpawnZ", $options->getSpawnPosition()->getFloorZ()),
			new IntTag("version", $version),
			new IntTag("DayTime", 0),
			new LongTag("LastPlayed", (int) (microtime(true) * 1000)),
			new LongTag("RandomSeed", $options->getSeed()),
			new LongTag("SizeOnDisk", 0),
			new LongTag("Time", 0),
			new StringTag("generatorName", GeneratorManager::getGeneratorName($options->getGeneratorClass())),
			new StringTag("generatorOptions", $options->getGeneratorOptions()),
			new StringTag("LevelName", $name),
			new CompoundTag("GameRules", [])
		]);

		$nbt = new BigEndianNBTStream();
		$data = new CompoundTag("", [
			$levelData
		]);
		$buffer = zlib_encode($nbt->write($data), ZLIB_ENCODING_GZIP);
		file_put_contents(Path::join($path, "level.dat"), $buffer);
	}

	protected function load() : CompoundTag
	{
		try {
			$rawLevelData = Filesystem::fileGetContents($this->dataPath);
		} catch (\RuntimeException $e) {
			throw new CorruptedLevelException($e->getMessage(), 0, $e);
		}
		$nbt = new BigEndianNBTStream();
		$decompressed = @zlib_decode($rawLevelData);
		if ($decompressed === false) {
			throw new CorruptedLevelException("Failed to decompress level.dat contents");
		}
		try {
			$levelData = $nbt->read($decompressed);
			/** @var CompoundTag $levelData */
		} catch (UnexpectedValueException $e) {
			throw new CorruptedLevelException($e->getMessage(), 0, $e);
		}

		$dataTag = $levelData->getTag("Data");
		if (!($dataTag instanceof CompoundTag)) {
			throw new CorruptedLevelException("Missing 'Data' key or wrong type");
		}
		return $dataTag;
	}

	protected function fix() : void
	{
		$generatorNameTag = $this->compoundTag->getTag("generatorName");
		if (!($generatorNameTag instanceof StringTag)) {
			$this->compoundTag->setString("generatorName", "default");
		} elseif (($generatorName = self::hackyFixForGeneratorClasspathInLevelDat($generatorNameTag->getValue())) !== null) {
			$this->compoundTag->setString("generatorName", $generatorName);
		}

		if (!($this->compoundTag->getTag("generatorOptions") instanceof StringTag)) {
			$this->compoundTag->setString("generatorOptions", "");
		}
	}

	public function save() : void
	{
		$nbt = new BigEndianNBTStream();
		$data = new CompoundTag("", [
			$this->compoundTag
		]);
		$buffer = Utils::assumeNotFalse(zlib_encode($nbt->write($data), ZLIB_ENCODING_GZIP));
		Filesystem::safeFilePutContents($this->dataPath, $buffer);
	}

	public function getDifficulty() : int
	{
		return $this->compoundTag->getByte("Difficulty", Level::DIFFICULTY_NORMAL);
	}

	public function setDifficulty(int $difficulty) : void
	{
		$this->compoundTag->setByte("Difficulty", $difficulty);
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
		if (($rainLevelTag = $this->compoundTag->getTag("rainLevel")) instanceof FloatTag) { //PocketMine/MCPE
			return $rainLevelTag->getValue();
		}

		return (float) $this->compoundTag->getByte("raining", 0); //PC vanilla
	}

	public function setRainLevel(float $level) : void
	{
		$this->compoundTag->setFloat("rainLevel", $level); //PocketMine/MCPE
		$this->compoundTag->setByte("raining", (int) ceil($level)); //PC vanilla
	}

	public function getLightningTime() : int
	{
		return $this->compoundTag->getInt("thunderTime", 0);
	}

	public function setLightningTime(int $ticks) : void
	{
		$this->compoundTag->setInt("thunderTime", $ticks);
	}

	public function getLightningLevel() : float
	{
		if (($lightningLevelTag = $this->compoundTag->getTag("lightningLevel")) instanceof FloatTag) { //PocketMine/MCPE
			return $lightningLevelTag->getValue();
		}

		return (float) $this->compoundTag->getByte("thundering", 0); //PC vanilla
	}

	public function setLightningLevel(float $level) : void
	{
		$this->compoundTag->setFloat("lightningLevel", $level); //PocketMine/MCPE
		$this->compoundTag->setByte("thundering", (int) ceil($level)); //PC vanilla
	}
}
