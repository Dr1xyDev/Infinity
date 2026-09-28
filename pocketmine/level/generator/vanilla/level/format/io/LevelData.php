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

namespace pocketmine\level\generator\vanilla\level\format\io;

use pocketmine\level\generator\vanilla\level\GameRules;
use pocketmine\level\generator\vanilla\math\Vector3;

interface LevelData
{
	/**
	 * Saves information about the world state, such as weather, time, etc.
	 */
	public function save() : void;

	public function getName() : string;

	/**
	 * Returns the generator name
	 */
	public function getGenerator() : string;

	public function getGeneratorOptions() : array;

	public function getSeed() : int;

	public function getTime() : int;

	public function setTime(int $value) : void;

	public function getSpawn() : Vector3;

	public function setSpawn(Vector3 $pos) : void;

	public function getGameRules() : GameRules;

	public function setGameRules(GameRules $rules) : void;

	/**
	 * Returns the world difficulty. This will be one of the World constants.
	 */
	public function getDifficulty() : int;

	/**
	 * Sets the world difficulty.
	 */
	public function setDifficulty(int $difficulty) : void;

	/**
	 * Returns the time in ticks to the next rain level change.
	 */
	public function getRainTime() : int;

	/**
	 * Sets the time in ticks to the next rain level change.
	 */
	public function setRainTime(int $ticks) : void;

	/**
	 * @return float 0.0 - 1.0
	 */
	public function getRainLevel() : float;

	/**
	 * @param float $level 0.0 - 1.0
	 */
	public function setRainLevel(float $level) : void;

	/**
	 * Returns the time in ticks to the next lightning level change.
	 */
	public function getLightningTime() : int;

	/**
	 * Sets the time in ticks to the next lightning level change.
	 */
	public function setLightningTime(int $ticks) : void;

	/**
	 * @return float 0.0 - 1.0
	 */
	public function getLightningLevel() : float;

	/**
	 * @param float $level 0.0 - 1.0
	 */
	public function setLightningLevel(float $level) : void;
}
