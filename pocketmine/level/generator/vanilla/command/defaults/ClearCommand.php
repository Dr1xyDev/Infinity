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
use pocketmine\level\generator\vanilla\inventory\Inventory;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\item\ItemFactory;
use pocketmine\level\generator\vanilla\item\ItemIds;
use pocketmine\level\generator\vanilla\lang\TranslationContainer;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\command\CommandEnum;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\command\CommandOverload;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\command\CommandParameter;
use pocketmine\Player;
use pocketmine\level\generator\vanilla\utils\TextFormat;
use ReflectionClass;

use function array_values;
use function count;
use function strtolower;

class ClearCommand extends VanillaCommand
{
	public function __construct(string $name)
	{
		$itemNames = [];
		foreach ((new ReflectionClass(ItemIds::class))->getConstants() as $n => $id) {
			if (ItemFactory::isRegistered($id)) {
				for ($i = 0; $i < 15; $i++) {
					if (ItemFactory::isRegistered($id)) {
						$itemName = (ItemFactory::get($id, $i))->getName();
						$itemNames[$itemName] = $itemName;
					} else {
						goto go_to_next;
					}
				}
			} else {
				$itemNames[$id] = strtolower($n);
			}
			go_to_next:
		}
		parent::__construct($name, "%pocketmine.command.clear.description", "%pocketmine.command.clear.usage", [], [
			new CommandOverload(false, [// 3 parameter for Submarine (normal 4)
				CommandParameter::standard("player", AvailableCommandsPacket::ARG_TYPE_TARGET, 0, true),
				CommandParameter::enum("itemName", new CommandEnum("clear_item_names", array_values($itemNames)), 0, true),
				CommandParameter::standard("maxCount", AvailableCommandsPacket::ARG_TYPE_INT, 0, true)
			])
		]);

		$this->setPermission("pocketmine.command.clear.self;pocketmine.command.clear.other");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args)
	{
		if (empty($args)) {
			if (!$sender->hasPermission("pocketmine.command.clear.self")) {
				$sender->sendMessage(new TranslationContainer(TextFormat::RED . "%commands.generic.permission"));
				return true;
			}

			if ($sender instanceof Player) {
				$targets = [$sender];
			} else {
				throw new InvalidCommandSyntaxException();
			}
		} else {
			if (!$sender->hasPermission("pocketmine.command.clear.other")) {
				$sender->sendMessage(new TranslationContainer(TextFormat::RED . "%commands.generic.permission"));
				return true;
			}

			$targets = CommandSelector::findTargets($sender, $args[0], Player::class);
		}

		if (isset($args[1])) {
			$removedCount = 0;

			$item = ItemFactory::fromString($args[1]);
			if (isset($args[2])) {
				$maxCount = (int) $args[2];
				$removedCount = $maxCount;
			}

			if ($item->isNull() && isset($maxCount) && $maxCount <= 0) {
				$sender->sendMessage(new TranslationContainer(TextFormat::RED . "%commands.clear.failure.no.items"));
				return true;
			}

			/** @var Player $player */
			foreach ($targets as $player) {
				$all = $this->getItemCount($item, $player->getInventory());

				if (isset($maxCount)) {
					$item->setCount($maxCount);
					$remaining = $player->getInventory()->removeItem($item);

					if (empty($remaining)) {
						$maxCount -= $all;
						$all = $this->getItemCount($item, $player->getArmorInventory());
						if ($all <= $maxCount) {
							$item->setCount($maxCount);
							$player->getArmorInventory()->removeItem($item);
						}
					}

					if ($maxCount > 0) {
						$removedCount += $maxCount;
					}
				} else {
					$all = $this->getItemCount($item, $player->getInventory());
					$all += $this->getItemCount($item, $player->getArmorInventory());
					$item->setCount($all);
					$player->getInventory()->removeItem($item);
					$player->getArmorInventory()->removeItem($item);

					$removedCount = $all;
				}

				$sender->sendMessage(new TranslationContainer("%commands.clear.success", [
					$player->getName(), $removedCount
				]));
			}

			return true;
		}

		/** @var Player $player */
		foreach ($targets as $player) {
			$removedCount = count($player->getInventory()->getContents(false)) + count($player->getArmorInventory()->getContents(false));
			$player->getInventory()->clearAll();
			$player->getArmorInventory()->clearAll();

			$sender->sendMessage(new TranslationContainer("%commands.clear.success", [
				$player->getName(), $removedCount
			]));
		}

		return true;
	}

	public function getItemCount(Item $item, Inventory $inventory) : int
	{
		$count = 0;
		$checkDamage = !$item->hasAnyDamageValue();
		$checkTags = $item->hasCompoundTag();
		foreach ($inventory->getContents(false) as $index => $i) {
			if ($item->equals($i, $checkDamage, $checkTags)) {
				$count += $i->getCount();
			}
		}

		return $count;
	}
}
