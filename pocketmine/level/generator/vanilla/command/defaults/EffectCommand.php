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
use pocketmine\level\generator\vanilla\entity\Effect;
use pocketmine\level\generator\vanilla\entity\EffectInstance;
use pocketmine\level\generator\vanilla\lang\TranslationContainer;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\command\CommandEnum;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\command\CommandOverload;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\command\CommandParameter;
use pocketmine\level\generator\vanilla\utils\TextFormat;

use function count;
use function strtolower;

use const INT32_MAX;

class EffectCommand extends VanillaCommand
{
	public function __construct(string $name)
	{
		parent::__construct($name, "%pocketmine.command.effect.description", "%commands.effect.usage", [], [
			new CommandOverload(false, [
				CommandParameter::standard("player", AvailableCommandsPacket::ARG_TYPE_TARGET),
				CommandParameter::enum("clear", new CommandEnum("clear", ["clear"]), 0)
			]),
			new CommandOverload(false, [
				CommandParameter::standard("player", AvailableCommandsPacket::ARG_TYPE_TARGET),
				CommandParameter::enum("effect", new CommandEnum("Effect", []), 0),
				CommandParameter::standard("seconds", AvailableCommandsPacket::ARG_TYPE_INT, 0, true),
				CommandParameter::standard("amplifier", AvailableCommandsPacket::ARG_TYPE_INT, 0, true),
				CommandParameter::enum("bool", new CommandEnum("effectBool", [
					"true", "false"
				]), 0)
			])
		]);
		$this->setPermission("pocketmine.command.effect");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args)
	{
		if (!$this->testPermission($sender)) {
			return true;
		}

		if (count($args) < 2) {
			throw new InvalidCommandSyntaxException();
		}

		$player = $sender->getServer()->getPlayer($args[0]);

		if ($player === null) {
			$sender->sendMessage(new TranslationContainer(TextFormat::RED . "%commands.generic.player.notFound"));
			return true;
		}

		if (strtolower($args[1]) === "clear") {
			foreach ($player->getEffects() as $effect) {
				$player->removeEffect($effect->getId());
			}

			$sender->sendMessage(new TranslationContainer("commands.effect.success.removed.all", [$player->getDisplayName()]));
			return true;
		}

		$effect = Effect::getEffectByName($args[1]);

		if ($effect === null) {
			$effect = Effect::getEffect((int) $args[1]);
		}

		if ($effect === null) {
			$sender->sendMessage(new TranslationContainer(TextFormat::RED . "%commands.effect.notFound", [$args[1]]));
			return true;
		}

		$amplification = 0;

		if (count($args) >= 3) {
			if (($d = $this->getBoundedInt($sender, $args[2], 0, (int) (INT32_MAX / 20))) === null) {
				return false;
			}
			$duration = $d * 20; //ticks
		} else {
			$duration = null;
		}

		if (count($args) >= 4) {
			$amplification = $this->getBoundedInt($sender, $args[3], 0, 255);
			if ($amplification === null) {
				return false;
			}
		}

		$visible = true;
		if (count($args) >= 5) {
			$v = strtolower($args[4]);
			if ($v === "on" || $v === "true" || $v === "t" || $v === "1") {
				$visible = false;
			}
		}

		if ($duration === 0) {
			if (!$player->hasEffect($effect->getId())) {
				if (count($player->getEffects()) === 0) {
					$sender->sendMessage(new TranslationContainer("commands.effect.failure.notActive.all", [$player->getDisplayName()]));
				} else {
					$sender->sendMessage(new TranslationContainer("commands.effect.failure.notActive", [$effect->getName(), $player->getDisplayName()]));
				}
				return true;
			}

			$player->removeEffect($effect->getId());
			$sender->sendMessage(new TranslationContainer("commands.effect.success.removed", [$effect->getName(), $player->getDisplayName()]));
		} else {
			$instance = new EffectInstance($effect, $duration, $amplification, $visible);
			$player->addEffect($instance);
			self::broadcastCommandMessage($sender, new TranslationContainer("%commands.effect.success", [$effect->getName(), $instance->getAmplifier(), $player->getDisplayName(), $instance->getDuration() / 20]));
		}

		return true;
	}
}
