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
use pocketmine\level\generator\vanilla\command\utils\CommandSelector;
use pocketmine\level\generator\vanilla\command\utils\InvalidCommandSyntaxException;
use pocketmine\level\generator\vanilla\lang\TranslationContainer;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\command\CommandOverload;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\command\CommandParameter;
use pocketmine\Player;

use function count;
use function floatval;

class PlaySoundCommand extends VanillaCommand
{
	public function __construct(string $name)
	{
		parent::__construct($name, "Plays a sound", "/playsound <sound: string> [player: target] [position: x y z] [volume: float] [pitch: float]", [], [
			new CommandOverload(false, [
				CommandParameter::standard("sound", AvailableCommandsPacket::ARG_TYPE_STRING),
				CommandParameter::standard("player", AvailableCommandsPacket::ARG_TYPE_TARGET, 0, true),
				CommandParameter::standard("pos", AvailableCommandsPacket::ARG_TYPE_POSITION, 0, true),
				CommandParameter::standard("volume", AvailableCommandsPacket::ARG_TYPE_FLOAT, 0, true),
				CommandParameter::standard("pitch", AvailableCommandsPacket::ARG_TYPE_FLOAT, 0, true)
			])
		]);

		$this->setPermission("pocketmine.command.playsound");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args)
	{
		if (!$this->testPermission($sender)) {
			return true;
		}

		if (empty($args)) {
			throw new InvalidCommandSyntaxException();
		}

		$soundName = $args[0];
		$pos = $sender instanceof Player ? $sender->asVector3() : new Vector3(0, 0, 0);

		if (count($args) > 1) {
			/** @var Player[] $targets */
			$targets = CommandSelector::findTargets($sender, $args[1], Player::class, $pos);

			if (count($args) >= 5) {
				$pos = new Vector3(
					$this->getRelativeDouble($pos->x, $sender, $args[2]),
					$this->getRelativeDouble($pos->y, $sender, $args[3]),
					$this->getRelativeDouble($pos->y, $sender, $args[4])
				);
			}
		} elseif ($sender instanceof Player) {
			$targets = [$sender];
		} else {
			throw new InvalidCommandSyntaxException();
		}

		$pk = new PlaySoundPacket();
		$pk->soundName = $soundName;
		$pk->x = $pos->x;
		$pk->y = $pos->y;
		$pk->z = $pos->z;
		$pk->volume = floatval($args[5] ?? 1.0);
		$pk->pitch = floatval($args[6] ?? 1.0);

		$sender->getServer()->broadcastPacket($targets, $pk);
		$sender->sendMessage(new TranslationContainer("commands.playsound.success", [$soundName, $sender->getName()]));

		return true;
	}
}
