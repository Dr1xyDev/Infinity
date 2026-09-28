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

namespace pocketmine\level\generator\vanilla\command\defaults;

use pocketmine\level\generator\vanilla\command\CommandSender;
use pocketmine\level\generator\vanilla\command\utils\InvalidCommandSyntaxException;
use pocketmine\level\generator\vanilla\lang\TranslationContainer;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\command\CommandEnum;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\command\CommandOverload;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\command\CommandParameter;
use pocketmine\Player;

use function count;
use function strtolower;

class GameRuleCommand extends VanillaCommand
{
	public function __construct(string $name)
	{
		parent::__construct($name, "%pocketmine.command.gamerule.description", "%pocketmine.command.gamerule.usage", [], [
			new CommandOverload(false, [
				CommandParameter::enum("rule", new CommandEnum("BoolGameRule", $this->getKnownGameRules()), 0),
				CommandParameter::enum("value", new CommandEnum("Bool", ["true", "false"]), 0),
			]),
			new CommandOverload(false, [
				CommandParameter::enum("rule", new CommandEnum("IntGameRule", $this->getKnownIntGameRules()), 0),
				CommandParameter::standard("value", AvailableCommandsPacket::ARG_TYPE_INT, 0, true),
			])
		]);

		$this->setPermission("pocketmine.command.gamerule");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args)
	{
		if (!$this->testPermission($sender)) {
			return true;
		}

		if (count($args) < 2) {
			throw new InvalidCommandSyntaxException();
		}

		if ($sender instanceof Player) {
			$level = $sender->getLevel();
		} else {
			$level = $sender->getServer()->getDefaultLevel();
		}

		$ruleName = $this->matchRuleName($level->getGameRules()->getRules(), $args[0]);

		if ($level->getGameRules()->hasRule($ruleName)) {
			if ($level->getGameRules()->setRuleWithMatching($ruleName, $args[1])) {
				$sender->sendMessage(new TranslationContainer("commands.gamerule.success", [
					$ruleName,
					$level->getGameRules()->toStringValue(($level->getGameRules()->getRuleValue($ruleName)))
				]));
			} else {
				$sender->sendMessage(new TranslationContainer("commands.generic.syntax", ["/gamerule " . $args[0] . " ", $args[1], " "]));
			}
		} else {
			$sender->sendMessage(new TranslationContainer("commands.generic.syntax", ["/gamerule ", $args[0] . " ", " " . $args[1]]));
		}

		return true;
	}

	public function getKnownGameRules() : array
	{
		return [
			"commandblockoutput", "commandblocksenabled",
			"dodaylightcycle", "doentitydrops", "dofiretick", "doimmediaterespawn", "doinsomnia", "dolimitedcrafting", "domobloot", "domobspawning", "dotiledrops", "doweathercycle", "drowningdamage",
			"falldamage", "firedamage", "freezedamage",
			"keepinventory",
			"mobgriefing",
			"naturalregeneration",
			"projectilescanbreakblocks",
			"pvp",
			"recipesunlock", "respawnblocksexplode",
			"sendcommandfeedback", "showbordereffect", "showcoordinates", "showdaysplayed", "showdeathmessages", "showrecipemessages", "showtags",
			"tntexplodes", "tntexplosiondropdecay", "locatorbar"
		];
	}

	public function getKnownIntGameRules() : array
	{
		return [
			"functioncommandlimit", "maxcommandchainlength", "playerssleepingpercetage", "randomtickspeed", "spawnradius"
		];
	}

	/**
	 * This a fix for difference between bedrock and java edition game rule name
	 */
	public function matchRuleName(array $rules, string $input) : string
	{
		foreach ($rules as $name => $d) {
			if (strtolower($name) === $input) {
				return $name;
			}
		}

		return $input;
	}
}
