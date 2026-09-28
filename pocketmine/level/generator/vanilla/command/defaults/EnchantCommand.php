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
use pocketmine\level\generator\vanilla\item\enchantment\Enchantment;
use pocketmine\level\generator\vanilla\item\enchantment\EnchantmentInstance;
use pocketmine\level\generator\vanilla\lang\TranslationContainer;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\command\CommandEnum;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\command\CommandOverload;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\command\CommandParameter;
use pocketmine\level\generator\vanilla\utils\TextFormat;

use function count;
use function is_numeric;

class EnchantCommand extends VanillaCommand
{
	public function __construct(string $name)
	{
		parent::__construct($name, "%pocketmine.command.enchant.description", "%commands.enchant.usage", [], [
			new CommandOverload(false, [
				CommandParameter::standard("player", AvailableCommandsPacket::ARG_TYPE_TARGET, 0, true),
				CommandParameter::enum("enchantName", new CommandEnum("Enchant", []), 0),
				CommandParameter::standard("level", AvailableCommandsPacket::ARG_TYPE_INT, 0, true)
			]),
			new CommandOverload(false, [
				CommandParameter::standard("player", AvailableCommandsPacket::ARG_TYPE_INT),
				CommandParameter::standard("enchantmentId", AvailableCommandsPacket::ARG_TYPE_INT),
				CommandParameter::standard("level", AvailableCommandsPacket::ARG_TYPE_INT)
			])
		]);
		$this->setPermission("pocketmine.command.enchant");
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

		$item = $player->getInventory()->getItemInHand();

		if ($item->isNull()) {
			$sender->sendMessage(new TranslationContainer("commands.enchant.noItem"));
			return true;
		}

		if (is_numeric($args[1])) {
			$enchantment = Enchantment::getEnchantment((int) $args[1]);
		} else {
			$enchantment = Enchantment::getEnchantmentByName($args[1]);
		}

		if (!($enchantment instanceof Enchantment)) {
			$sender->sendMessage(new TranslationContainer("commands.enchant.notFound", [$args[1]]));
			return true;
		}

		$level = 1;
		if (isset($args[2])) {
			$level = $this->getBoundedInt($sender, $args[2], 1, $sender->isOp() ? 255 : $enchantment->getMaxLevel());
			if ($level === null) {
				return false;
			}
		}

		$item->addEnchantment(new EnchantmentInstance($enchantment, $level));
		$player->getInventory()->setItemInHand($item);

		self::broadcastCommandMessage($sender, new TranslationContainer("%commands.enchant.success", [$player->getName()]));
		return true;
	}
}
