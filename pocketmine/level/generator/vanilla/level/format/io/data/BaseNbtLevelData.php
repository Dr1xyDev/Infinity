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
use pocketmine\level\generator\vanilla\level\format\io\LevelData;
use pocketmine\level\generator\vanilla\level\GameRules;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\nbt\tag\CompoundTag;
use pocketmine\level\generator\vanilla\nbt\tag\IntTag;
use function file_exists;

abstract class BaseNbtLevelData implements LevelData
{
	protected string $dataPath;

	protected CompoundTag $compoundTag;

	/**
	 * @throws CorruptedLevelException
	 * @throws UnsupportedLevelFormatException
	 */
	public function __construct(string $dataPath)
	{
		$this->dataPath = $dataPath;

		if (!file_exists($this->dataPath)) {
			throw new CorruptedLevelException("World data not found at $dataPath");
		}

		try {
			$this->compoundTag = $this->load();
		} catch (CorruptedLevelException $e) {
			throw new CorruptedLevelException("Corrupted world data: " . $e->getMessage(), 0, $e);
		}
		$this->fix();
	}

	/**
	 * @throws CorruptedLevelException
	 * @throws UnsupportedLevelFormatException
	 */
	abstract protected function load() : CompoundTag;

	/**
	 * @throws CorruptedLevelException
	 * @throws UnsupportedLevelFormatException
	 */
	abstract protected function fix() : void;

	/**
	 * Hack to fix worlds broken previously by older versions of PocketMine-MP which incorrectly saved classpaths of
	 * generators into level.dat on imported (not generated) worlds.
	 *
	 * This should only have affected leveldb worlds as far as I know, because PC format worlds include the
	 * generatorName tag by default. However, MCPE leveldb ones didn't, and so they would get filled in with something
	 * broken.
	 *
	 * This bug took a long time to get found because previously the generator manager would just return the default
	 * generator silently on failure to identify the correct generator, which caused lots of unexpected bugs.
	 *
	 * Only classnames which were written into the level.dat from "fixing" the level data are included here. These are
	 * hardcoded to avoid problems fixing broken worlds in the future if these classes get moved, renamed or removed.
	 *
	 * @param string $className Classname saved in level.dat
	 *
	 * @return null|string Name of the correct generator to replace the broken value
	 */
	protected static function hackyFixForGeneratorClasspathInLevelDat(string $className) : ?string
	{
		//THESE ARE DELIBERATELY HARDCODED, DO NOT CHANGE!
		switch ($className) {
			/** @noinspection ClassConstantCanBeUsedInspection */
			case 'pocketmine\level\generator\vanilla\level\generator\dimension\Overworld':
				return "normal";
				/** @noinspection ClassConstantCanBeUsedInspection */
			case 'pocketmine\level\generator\vanilla\level\generator\FlatGenerator':
				return "flat";
		}

		return null;
	}

	public function getCompoundTag() : CompoundTag
	{
		return $this->compoundTag;
	}

	/* The below are common between PC and PE */

	public function getName() : string
	{
		return $this->compoundTag->getString("LevelName");
	}

	public function getGenerator() : string
	{
		return $this->compoundTag->getString("generatorName", "DEFAULT");
	}

	public function getGeneratorOptions() : array
	{
		return ["preset" => $this->compoundTag->getString("generatorOptions", "")];
	}

	public function getSeed() : int
	{
		return $this->compoundTag->getLong("RandomSeed");
	}

	public function getTime() : int
	{
		if (($timeTag = $this->compoundTag->getTag("Time")) instanceof IntTag) { //some older PM worlds had this in the wrong format
			return $timeTag->getValue();
		}
		return $this->compoundTag->getLong("Time", 0);
	}

	public function setTime(int $value) : void
	{
		$this->compoundTag->setLong("Time", $value);
	}

	public function getSpawn() : Vector3
	{
		return new Vector3($this->compoundTag->getInt("SpawnX"), $this->compoundTag->getInt("SpawnY"), $this->compoundTag->getInt("SpawnZ"));
	}

	public function setSpawn(Vector3 $pos) : void
	{
		$this->compoundTag->setInt("SpawnX", $pos->getFloorX());
		$this->compoundTag->setInt("SpawnY", $pos->getFloorY());
		$this->compoundTag->setInt("SpawnZ", $pos->getFloorZ());
	}

	public function getGameRules() : GameRules
	{
		$rules = new GameRules();
		if ($this->compoundTag->hasTag("GameRules", CompoundTag::class)) {
			$rules->readSaveData($this->compoundTag->getCompoundTag("GameRules"));
		}
		return $rules;
	}

	public function setGameRules(GameRules $rules) : void
	{
		$this->compoundTag->setTag($rules->writeSaveData());
	}
}
