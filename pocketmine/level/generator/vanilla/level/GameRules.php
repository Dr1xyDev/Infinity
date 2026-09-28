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

use pocketmine\level\generator\vanilla\nbt\tag\CompoundTag;
use pocketmine\level\generator\vanilla\nbt\tag\StringTag;
use function floatval;
use function intval;
use function is_bool;
use function is_float;
use function is_int;
use function strtolower;
use function strval;

class GameRules
{
	public const RULE_COMMAND_BLOCK_OUTPUT = "commandBlockOutput";
	public const RULE_COMMAND_BLOCKS_ENABLED = "commandBlocksEnabled";
	public const RULE_DO_DAYLIGHT_CYCLE = "doDaylightCycle";
	public const RULE_DO_ENTITY_DROPS = "doEntityDrops";
	public const RULE_DO_FIRE_TICK = "doFireTick";
	public const RULE_DO_IMMEDIATE_RESPAWN = "doImmediateRespawn";
	public const RULE_DO_INSOMNIA = "doInsomnia";
	public const RULE_DO_LIMITED_CRAFTING = "doLimitedCrafting";
	public const RULE_DO_MOB_LOOT = "doMobLoot";
	public const RULE_DO_MOB_SPAWNING = "doMobSpawning";
	public const RULE_DO_TILE_DROPS = "doTileDrops";
	public const RULE_DO_WEATHER_CYCLE = "doWeatherCycle";
	public const RULE_DROWNING_DAMAGE = "drowningDamage";
	public const RULE_FALL_DAMAGE = "fallDamage";
	public const RULE_FIRE_DAMAGE = "fireDamage";
	public const RULE_FREEZE_DAMAGE = "freezeDamage";
	public const RULE_FUNCTION_COMMAND_LIMIT = "functionCommandLimit";
	public const RULE_KEEP_INVENTORY = "keepInventory";
	public const RULE_MAX_COMMAND_CHAIN_LENGTH = "maxCommandChainLength";
	public const RULE_MOB_GRIEFING = "mobGriefing";
	public const RULE_NATURAL_REGENERATION = "naturalRegeneration";
	public const RULE_PLAYERS_SLEEPING_PERCETAGE = "playersSleepingPercentage";
	public const RULE_PROJECTILES_CAN_BREAK_BLOCKS = "projectilesCanBreakBlocks";
	public const RULE_PVP = "pvp";
	public const RULE_RANDOM_TICK_SPEED = "randomTickSpeed";
	public const RULE_RECIPES_UNLOCK = "recipesUnlock";
	public const RULE_RESPAWN_BLOCKS_EXPLODE = "respawnBlocksExplode";
	public const RULE_SEND_COMMAND_FEEDBACK = "sendCommandFeedback";
	public const RULE_SHOW_BORDER_EFFECT = "showBorderEffect";
	public const RULE_SHOW_COORDINATES = "showCoordinates";
	public const RULE_SHOW_DAYS_PLAYED = "showDaysPlayed";
	public const RULE_SHOW_DEATH_MESSAGES = "showDeathMessages";
	public const RULE_SHOW_RECIPE_MESSAGES = "showRecipeMessages";
	public const RULE_SHOW_TAGS = "showTags";
	public const RULE_SPAWN_RADIUS = "spawnRadius";
	public const RULE_TNT_EXPLODES = "tntExplodes";
	public const RULE_TNT_EXPLOSION_DROP_DECAY = "tntExplosionDropDecay";
	public const RULE_LOCALOR_BAER = "locatorbar";

	public const RULE_TYPE_BOOL = 1;
	public const RULE_TYPE_INT = 2;
	public const RULE_TYPE_FLOAT = 3;

	/** @var int[][] */
	public $rules = [];
	/** @var int[][] */
	public $dirtyRules = [];

	public function __construct()
	{
		// default bedrock edition game rules
		$this->setBool(self::RULE_COMMAND_BLOCK_OUTPUT, true);
		$this->setBool(self::RULE_COMMAND_BLOCKS_ENABLED, true);
		$this->setBool(self::RULE_DO_DAYLIGHT_CYCLE, true);
		$this->setBool(self::RULE_DO_ENTITY_DROPS, true);
		$this->setBool(self::RULE_DO_FIRE_TICK, true);
		$this->setBool(self::RULE_DO_IMMEDIATE_RESPAWN, false);
		$this->setBool(self::RULE_DO_INSOMNIA, true);
		$this->setBool(self::RULE_DO_LIMITED_CRAFTING, false);
		$this->setBool(self::RULE_DO_MOB_LOOT, true);
		$this->setBool(self::RULE_DO_MOB_SPAWNING, false);
		$this->setBool(self::RULE_DO_TILE_DROPS, true);
		$this->setBool(self::RULE_DO_WEATHER_CYCLE, true);
		$this->setBool(self::RULE_DROWNING_DAMAGE, true);
		$this->setBool(self::RULE_FALL_DAMAGE, true);
		$this->setBool(self::RULE_FIRE_DAMAGE, true);
		$this->setBool(self::RULE_FREEZE_DAMAGE, true);
		$this->setInt(self::RULE_FUNCTION_COMMAND_LIMIT, 10000);
		$this->setBool(self::RULE_KEEP_INVENTORY, false);
		$this->setInt(self::RULE_MAX_COMMAND_CHAIN_LENGTH, 65536);
		$this->setBool(self::RULE_MOB_GRIEFING, true);
		$this->setBool(self::RULE_NATURAL_REGENERATION, true);
		$this->setInt(self::RULE_PLAYERS_SLEEPING_PERCETAGE, 100);
		$this->setBool(self::RULE_PROJECTILES_CAN_BREAK_BLOCKS, true);
		$this->setBool(self::RULE_PVP, true);
		$this->setInt(self::RULE_RANDOM_TICK_SPEED, 3);
		$this->setBool(self::RULE_RECIPES_UNLOCK, true);
		$this->setBool(self::RULE_RESPAWN_BLOCKS_EXPLODE, true);
		$this->setBool(self::RULE_SEND_COMMAND_FEEDBACK, true);
		$this->setBool(self::RULE_SHOW_BORDER_EFFECT, true);
		$this->setBool(self::RULE_SHOW_COORDINATES, false);
		$this->setBool(self::RULE_SHOW_DAYS_PLAYED, false);
		$this->setBool(self::RULE_SHOW_DEATH_MESSAGES, true);
		$this->setBool(self::RULE_SHOW_RECIPE_MESSAGES, true);
		$this->setBool(self::RULE_SHOW_TAGS, true);
		$this->setInt(self::RULE_SPAWN_RADIUS, 10);
		$this->setBool(self::RULE_TNT_EXPLODES, true);
		$this->setBool(self::RULE_TNT_EXPLOSION_DROP_DECAY, false);
		$this->setBool(self::RULE_LOCALOR_BAER, true);
	}

	public function setRule(string $name, $value, int $valueType) : bool
	{
		if ($this->checkType($value, $valueType)) {
			$this->rules[$name] = $this->dirtyRules[$name] = [
				$valueType, $value
			];
			return true;
		}
		return false;
	}

	public function setRuleWithMatching(string $name, $value) : bool
	{
		if ($this->hasRule($name)) {
			$type = $this->rules[$name][0];
			$value = $this->convertType($value, $type);

			return $this->setRule($name, $value, $type);
		}

		return false;
	}

	/**
	 * @return int|float|bool|null
	 */
	public function getRule(string $name, int $expectedType, $defaultValue)
	{
		if ($this->hasRule($name)) {
			$rule = $this->rules[$name];

			if ($this->checkType($rule[1], $expectedType)) {
				return $rule[1];
			}
		}
		return $defaultValue;
	}

	/**
	 * @return bool|int|null
	 */
	public function getRuleValue(string $name)
	{
		return isset($this->rules[$name]) ? $this->rules[$name][1] : null;
	}

	public function hasRule(string $name) : bool
	{
		return isset($this->rules[$name]) && isset($this->rules[$name][0]) && isset($this->rules[$name][1]);
	}

	public function checkType($input, int $wantedType) : bool
	{
		switch ($wantedType) {
			default:
				return false;
			case self::RULE_TYPE_INT:
				return is_int($input);
			case self::RULE_TYPE_FLOAT:
				return is_float($input);
			case self::RULE_TYPE_BOOL:
				return is_bool($input);
		}
	}

	public function convertType(string $input, int $wantedType) : float|bool|int|string
	{
		switch ($wantedType) {
			default:
				return $input;
			case self::RULE_TYPE_INT:
				return intval($input);
			case self::RULE_TYPE_FLOAT:
				return floatval($input);
			case self::RULE_TYPE_BOOL:
				return strtolower($input) === "true";
		}
	}

	public function toStringValue($value) : string
	{
		if (is_bool($value)) {
			return $value ? "true" : "false";
		}
		return strval($value);
	}

	public function setBool(string $name, bool $value) : void
	{
		$this->setRule($name, $value, self::RULE_TYPE_BOOL);
	}

	public function getBool(string $name, bool $defaultValue = false) : bool
	{
		return $this->getRule($name, self::RULE_TYPE_BOOL, $defaultValue);
	}

	public function setInt(string $name, int $value) : void
	{
		$this->setRule($name, $value, self::RULE_TYPE_INT);
	}

	public function getInt(string $name, int $defaultValue = 0) : int
	{
		return $this->getRule($name, self::RULE_TYPE_INT, $defaultValue);
	}

	public function setFloat(string $name, float $value) : void
	{
		$this->setRule($name, $value, self::RULE_TYPE_FLOAT);
	}

	public function getFloat(string $name, float $defaultValue = 0.0) : float
	{
		return $this->getRule($name, self::RULE_TYPE_FLOAT, $defaultValue);
	}

	public function getRules() : array
	{
		return $this->rules;
	}

	public function readSaveData(CompoundTag $nbt) : void
	{
		foreach ($nbt->getValue() as $tag) {
			if ($tag instanceof StringTag) {
				$this->setRuleWithMatching($tag->getName(), $tag->getValue());
			}
		}

		$this->clearDirtyRules();
	}

	public function writeSaveData() : CompoundTag
	{
		$nbt = new CompoundTag("GameRules");

		foreach ($this->rules as $name => $rule) {
			$nbt->setString($name, $this->toStringValue($rule[1]));
		}

		return $nbt;
	}

	public function clearDirtyRules() : void
	{
		$this->dirtyRules = [];
	}

	public function getDirtyRules() : array
	{
		return $this->dirtyRules;
	}
}
