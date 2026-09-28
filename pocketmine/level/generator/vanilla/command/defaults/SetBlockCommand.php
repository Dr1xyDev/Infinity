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
use pocketmine\level\generator\vanilla\item\ItemFactory;
use pocketmine\level\generator\vanilla\lang\TranslationContainer;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\command\CommandEnum;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\command\CommandOverload;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\command\CommandParameter;
use pocketmine\Player;
use pocketmine\level\generator\vanilla\utils\TextFormat;

use function count;

class SetBlockCommand extends VanillaCommand
{
	public function __construct(string $name)
	{
		parent::__construct($name, "Changes a block to another block.", "/setblock <position: x y z> <tileName: string> [tileData: int] [oldBlockHandling: string]", [], [
			new CommandOverload(false, [
				CommandParameter::standard("position", AvailableCommandsPacket::ARG_TYPE_POSITION),
				CommandParameter::enum("tileName", new CommandEnum("Block", []), 0),
				CommandParameter::standard("tileData", AvailableCommandsPacket::ARG_TYPE_INT, 0, true),
				CommandParameter::enum("mode", new CommandEnum("mode", [
					"replace", "destroy", "keep"
				]), 0)
			])
		]);
		$this->setPermission("pocketmine.command.setblock");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args)
	{
		if (!$this->testPermission($sender) || !($sender instanceof Player)) {
			return true;
		}

		if (count($args) < 4) {
			throw new InvalidCommandSyntaxException();
		}

		$level = $sender->level;
		$x = $this->getRelativeDouble($sender->x, $sender, $args[0]);
		$y = $this->getRelativeDouble($sender->y, $sender, $args[1], 0, 256);
		$z = $this->getRelativeDouble($sender->z, $sender, $args[2]);
		$pos = [
			(int) $x, (int) $y, (int) $z
		];
		if (!$level->isInWorld(...$pos)) {
			$sender->sendMessage(new TranslationContainer(TextFormat::RED . "%commands.setblock.outOfWorld"));
			return true;
		}

		$pos = new Vector3(...$pos);

		$item = ItemFactory::fromString($args[3] . ":" . ($args[4] ?? 0));

		$handling = $args[5] ?? "replace";
		$block = $item->getBlock();

		$place = true;
		switch ($handling) {
			case "destroy":
				$level->useBreakOn($pos);
				break;
			case "keep":
				$place = $level->getBlockAt($pos->x, $pos->y, $pos->z)->getId() === 0;
				break;
			case "replace":
				break;
			default:
				throw new InvalidCommandSyntaxException();
		}

		if ($place) {
			$level->setBlock($pos, $block);
			$sender->sendMessage(new TranslationContainer(TextFormat::GREEN . "%commands.setblock.success"));
		}

		return true;
	}
}
