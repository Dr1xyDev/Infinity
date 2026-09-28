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

use Exception;
use InvalidArgumentException;
use pocketmine\level\generator\vanilla\command\Command;
use pocketmine\level\generator\vanilla\command\CommandSender;
use pocketmine\level\generator\vanilla\command\utils\CommandSelector;
use pocketmine\level\generator\vanilla\command\utils\InvalidCommandSyntaxException;
use pocketmine\level\generator\vanilla\item\ItemFactory;
use pocketmine\level\generator\vanilla\lang\TranslationContainer;
use pocketmine\level\generator\vanilla\nbt\JsonNbtParser;
use pocketmine\level\generator\vanilla\nbt\tag\CompoundTag;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\command\CommandEnum;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\command\CommandParameter;
use pocketmine\Player;
use pocketmine\level\generator\vanilla\utils\TextFormat;

use function array_slice;
use function count;
use function implode;

class GiveCommand extends VanillaCommand
{
	public function __construct(string $name)
	{
		parent::__construct($name, "%pocketmine.command.give.description", "%pocketmine.command.give.usage");
		$this->setPermission("pocketmine.command.give");

		$parameters = [
			CommandParameter::standard("player", AvailableCommandsPacket::ARG_TYPE_TARGET),
			CommandParameter::enum("itemName", new CommandEnum("Item", []), 0),
			CommandParameter::standard("amount", AvailableCommandsPacket::ARG_TYPE_INT, 0, true),
			CommandParameter::standard("components", AvailableCommandsPacket::ARG_TYPE_JSON, 0, true)
		];
		$this->setParameters($parameters, 0);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args)
	{
		if (!$this->testPermission($sender)) {
			return true;
		}

		if (count($args) < 2) {
			throw new InvalidCommandSyntaxException();
		}

		/** @var Player[] $targets */
		$targets = CommandSelector::findTargets($sender, $args[0], Player::class);
		if (empty($targets)) {
			$sender->sendMessage(new TranslationContainer(TextFormat::RED . "%commands.generic.player.notFound"));
			return true;
		}

		try {
			$item = ItemFactory::fromStringSingle($args[1]);
		} catch (InvalidArgumentException $e) {
			$sender->sendMessage(new TranslationContainer(TextFormat::RED . "%commands.give.item.notFound", [$args[1]]));
			return true;
		}

		if (!isset($args[2])) {
			$item->setCount($item->getMaxStackSize());
		} else {
			$item->setCount((int) $args[2]);
		}

		if (isset($args[3])) {
			$tags = $exception = null;
			$data = implode(" ", array_slice($args, 3));
			try {
				$tags = JsonNbtParser::parseJson($data);
			} catch (Exception $ex) {
				$exception = $ex;
			}

			if (!($tags instanceof CompoundTag) || $exception !== null) {
				$sender->sendMessage(new TranslationContainer("commands.give.tagError", [$exception !== null ? $exception->getMessage() : "Invalid tag conversion"]));
				return true;
			}

			$item->setNamedTag($tags);
		}

		foreach ($targets as $player) {
			//TODO: overflow
			$player->getInventory()->addItem(clone $item);

			Command::broadcastCommandMessage($sender, new TranslationContainer("%commands.give.success", [
				$item->getName() . " (" . $item->getId() . ":" . $item->getDamage() . ")", (string) $item->getCount(),
				$player->getName()
			]));
		}

		return true;
	}
}
