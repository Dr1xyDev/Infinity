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
use pocketmine\level\generator\vanilla\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\SetHudPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\command\CommandEnum;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\command\CommandOverload;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\command\CommandParameter;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\hud\HudElement;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\hud\HudVisibility;
use pocketmine\Player;

use function count;

class HudCommand extends VanillaCommand
{
	public function __construct(string $name)
	{
		parent::__construct(
			$name,
			"Changes the visibility of hud elements.",
			"/hud <target: target> <visible: HudVisibility> [hud_element: HudElement]",
			[],
			[
				new CommandOverload(false, [
					CommandParameter::standard("player", AvailableCommandsPacket::ARG_TYPE_TARGET),
					CommandParameter::enum("visible", new CommandEnum("HudVisibility", ["hide", "reset"]), 0),
					CommandParameter::enum("hud_element", new CommandEnum("HudElement", ["air_bubbles", "all", "armor", "crosshair", "health", "horse_health", "hotbar", "hunger", "item_text", "paperdoll", "progress_bar", "status_effects", "tooltips", "touch_controls"]), 0, true),
				])
			]
		);
		$this->setPermission("pocketmine.command.hud");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) : bool
	{
		if (!$this->testPermission($sender)) {
			return true;
		}

		if (!$sender instanceof Player) {
			$sender->sendMessage("This command must be executed as a player");
			return false;
		}

		if (count($args) < 2) {
			throw new InvalidCommandSyntaxException();
		}

		$visibility = match ($args[1]) {
			"hide" => HudVisibility::HIDE,
			"reset" => HudVisibility::RESET,
			default => throw new InvalidCommandSyntaxException()
		};

		$hudElements = match ($args[2] ?? "all") {
			"air_bubbles" => [HudElement::AIR_BUBBLES],
			"armor" => [HudElement::ARMOR],
			"crosshair" => [HudElement::CROSSHAIR],
			"health" => [HudElement::HEALTH],
			"horse_health" => [HudElement::VEHICLE_HEALTH],
			"hotbar" => [HudElement::HOTBAR],
			"hunger" => [HudElement::FOOD],
			"item_text" => [HudElement::ITEM_TEXT_POPUP],
			"paperdoll" => [HudElement::PAPER_DOLL],
			"progress_bar" => [HudElement::XP],
			"status_effects" => [HudElement::STATUS_EFFECTS],
			"tooltips" => [HudElement::TOOLTIPS],
			"touch_controls" => [HudElement::TOUCH_CONTROLS],
			"all" => [
				HudElement::AIR_BUBBLES,
				HudElement::ARMOR,
				HudElement::CROSSHAIR,
				HudElement::HEALTH,
				HudElement::VEHICLE_HEALTH,
				HudElement::HOTBAR,
				HudElement::FOOD,
				HudElement::PAPER_DOLL,
				HudElement::XP,
				HudElement::TOOLTIPS,
				HudElement::TOUCH_CONTROLS,
				HudElement::STATUS_EFFECTS,
				HudElement::ITEM_TEXT_POPUP,
			],
			default => throw new InvalidCommandSyntaxException()
		};

		$pk = new SetHudPacket();
		$pk->hudElements = $hudElements;
		$pk->visibility = $visibility;
		/** @var Player[] $targets */
		$targets = CommandSelector::findTargets($sender, $args[0], Player::class);
		$sender->getServer()->broadcastPacket($targets, $pk);

		$sender->sendMessage("Hud command successfully executed");

		return true;
	}
}
