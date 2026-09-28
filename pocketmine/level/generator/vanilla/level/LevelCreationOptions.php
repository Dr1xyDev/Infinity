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

namespace pocketmine\level\generator\vanilla\level;

use pocketmine\level\generator\vanilla\level\generator\dimension\Overworld;
use pocketmine\level\generator\vanilla\level\generator\Generator;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\utils\Utils;
use function random_int;

/**
 * Represents user-customizable settings for world creation
 */
final class LevelCreationOptions
{
	/** @phpstan-var class-string<Generator> */
	private string $generatorClass = Overworld::class;
	private int $seed;
	private int $difficulty = Level::DIFFICULTY_NORMAL;
	private string $generatorOptions = "";
	private Vector3 $spawnPosition;

	public function __construct()
	{
		$this->seed = random_int(INT32_MIN, INT32_MAX);
		$this->spawnPosition = new Vector3(256, 70, 256);
	}

	public static function create() : self
	{
		return new self();
	}

	/** @phpstan-return class-string<Generator> */
	public function getGeneratorClass() : string
	{
		return $this->generatorClass;
	}

	/**
	 * @phpstan-param class-string<Generator> $generatorClass
	 * @return $this
	 */
	public function setGeneratorClass(string $generatorClass) : self
	{
		Utils::testValidInstance($generatorClass, Generator::class);
		$this->generatorClass = $generatorClass;
		return $this;
	}

	public function getSeed() : int
	{
		return $this->seed;
	}

	/** @return $this */
	public function setSeed(int $seed) : self
	{
		$this->seed = $seed;
		return $this;
	}

	public function getDifficulty() : int
	{
		return $this->difficulty;
	}

	/** @return $this */
	public function setDifficulty(int $difficulty) : self
	{
		$this->difficulty = $difficulty;
		return $this;
	}

	public function getGeneratorOptions() : string
	{
		return $this->generatorOptions;
	}

	/** @return $this */
	public function setGeneratorOptions(string $generatorOptions) : self
	{
		$this->generatorOptions = $generatorOptions;
		return $this;
	}

	public function getSpawnPosition() : Vector3
	{
		return $this->spawnPosition;
	}

	/** @return $this */
	public function setSpawnPosition(Vector3 $spawnPosition) : self
	{
		$this->spawnPosition = $spawnPosition;
		return $this;
	}
}
