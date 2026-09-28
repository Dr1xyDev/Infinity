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

use pocketmine\level\generator\vanilla\command\Command;
use pocketmine\level\generator\vanilla\command\CommandSender;
use pocketmine\level\generator\vanilla\command\utils\InvalidCommandSyntaxException;
use pocketmine\level\generator\vanilla\lang\TranslationContainer;
use pocketmine\level\generator\vanilla\level\Location;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\command\CommandEnum;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\command\CommandParameter;
use pocketmine\Player;
use pocketmine\level\generator\vanilla\utils\AssumptionFailedError;
use pocketmine\level\generator\vanilla\utils\TextFormat;

use function array_shift;
use function count;
use function round;

class TeleportCommand extends VanillaCommand
{
	public function __construct(string $name)
	{
		parent::__construct($name, "%pocketmine.command.tp.description", "%commands.tp.usage", ["teleport"]);
		$this->setPermission("pocketmine.command.teleport");

		$destination = CommandParameter::standard("destination", AvailableCommandsPacket::ARG_TYPE_POSITION);
		$targetDestination = CommandParameter::standard("destination", AvailableCommandsPacket::ARG_TYPE_TARGET);
		$victim = CommandParameter::standard("victim", AvailableCommandsPacket::ARG_TYPE_TARGET);
		$yRot = CommandParameter::standard("yRot", AvailableCommandsPacket::ARG_TYPE_VALUE, 0, true);
		$xRot = CommandParameter::standard("xRot", AvailableCommandsPacket::ARG_TYPE_VALUE, 0, true);
		$facing = CommandParameter::enum("facing", new CommandEnum("facing", ["facing"]), 0);
		$lookAtPosition = CommandParameter::standard("lookAtPosition", AvailableCommandsPacket::ARG_TYPE_POSITION);
		$lookAtEntity = CommandParameter::standard("lookAtEntity", AvailableCommandsPacket::ARG_TYPE_TARGET);

		$this->setParameters([
			$destination, $yRot, $xRot
		], 0);
		$this->setParameters([
			$destination, $facing, $lookAtPosition
		], 1);
		$this->setParameters([
			$destination, $facing, $lookAtEntity
		], 2);
		$this->setParameters([
			$victim, $destination, $yRot, $xRot
		], 3);
		$this->setParameters([
			$victim, $destination, $facing, $lookAtPosition
		], 4);
		$this->setParameters([
			$victim, $destination, $facing, $lookAtEntity
		], 5);
		$this->setParameters([$targetDestination], 6);
		$this->setParameters([
			$victim, $targetDestination
		], 7);
	}

	private function findPlayer(CommandSender $sender, string $playerName) : ?Player
	{
		$subject = $sender->getServer()->getPlayer($playerName);
		if ($subject === null) {
			$sender->sendMessage(TextFormat::RED . "Can't find player " . $playerName);
			return null;
		}
		return $subject;
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args)
	{
		if (!$this->testPermission($sender)) {
			return true;
		}

		switch (count($args)) {
			case 1: // /tp targetPlayer
			case 3: // /tp x y z
			case 5: // /tp x y z yaw pitch - TODO: 5 args could be target x y z yaw :(
				if (!($sender instanceof Player)) {
					$sender->sendMessage(TextFormat::RED . "Please provide a player!");
					return true;
				}

				$subject = $sender;
				$targetArgs = $args;
				break;
			case 2: // /tp player1 player2
			case 4: // /tp player1 x y z - TODO: 4 args could be x y z yaw :(
			case 6: // /tp player1 x y z yaw pitch
				$subject = $this->findPlayer($sender, $args[0]);
				if ($subject === null) {
					return true;
				}
				$targetArgs = $args;
				array_shift($targetArgs);
				break;
			default:
				throw new InvalidCommandSyntaxException();
		}

		switch (count($targetArgs)) {
			case 1:
				$targetPlayer = $this->findPlayer($sender, $targetArgs[0]);
				if ($targetPlayer === null) {
					return true;
				}

				$subject->teleport($targetPlayer->getLocation());
				Command::broadcastCommandMessage($sender, new TranslationContainer("commands.tp.success", [$subject->getName(), $targetPlayer->getName()]));

				return true;
			case 3:
			case 5:
				$base = $subject->getLocation();
				if (count($targetArgs) === 5) {
					$yaw = (float) $targetArgs[3];
					$pitch = (float) $targetArgs[4];
				} else {
					$yaw = $base->yaw;
					$pitch = $base->pitch;
				}

				$x = $this->getRelativeDouble($base->x, $sender, $targetArgs[0]);
				$y = $this->getRelativeDouble($base->y, $sender, $targetArgs[1], 0, 256);
				$z = $this->getRelativeDouble($base->z, $sender, $targetArgs[2]);
				$targetLocation = new Location($x, $y, $z, $yaw, $pitch, $base->getLevel());

				$subject->teleport($targetLocation);
				Command::broadcastCommandMessage($sender, new TranslationContainer("commands.tp.success.coordinates", [
					$subject->getName(),
					round($targetLocation->x, 2),
					round($targetLocation->y, 2),
					round($targetLocation->z, 2)
				]));
				return true;
			default:
				throw new AssumptionFailedError("This branch should be unreachable (for now)");
		}
	}
}
