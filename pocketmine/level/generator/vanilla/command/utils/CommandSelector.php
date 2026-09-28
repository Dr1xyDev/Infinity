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

namespace pocketmine\level\generator\vanilla\command\utils;

use pocketmine\level\generator\vanilla\command\CommandSender;
use pocketmine\level\generator\vanilla\entity\Entity;
use pocketmine\level\generator\vanilla\level\Position;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\Player;

use function array_values;
use function count;
use function mt_rand;
use function reset;

class CommandSelector
{
	public const SELECTOR_ALL_PLAYERS = "@a";
	public const SELECTOR_ALL_ENTITIES = "@e";
	public const SELECTOR_CLOSEST_PLAYER = "@p";
	public const SELECTOR_RANDOM_PLAYER = "@r";
	public const SELECTOR_YOURSELF = "@s";

	private function __construct()
	{
		// NOOP
	}

	/**
	 * @return Entity[]
	 * @throws NoSelectorMatchException
	 */
	public static function findTargets(CommandSender $sender, string $selector, string $entityType = Entity::class, ?Vector3 $pos = null) : array
	{
		if (!($pos instanceof Position) && $pos !== null) {
			if ($sender instanceof Position) {
				$pos = $sender->asPosition();
				$pos->x = $sender->getX();
				$pos->y = $sender->getY();
				$pos->z = $sender->getZ();
			} else {
				$pos = new Position($pos->x, $pos->y, $pos->z, $sender->getServer()->getDefaultLevel());
			}
		}

		if ($pos === null) {
			$pos = $sender instanceof Position ? $sender : $sender->getServer()->getDefaultLevel()->getSpawnLocation();
		}
		switch ($selector) {
			case CommandSelector::SELECTOR_ALL_PLAYERS:
				$targets = $sender->getServer()->getOnlinePlayers();
				break;
			case CommandSelector::SELECTOR_ALL_ENTITIES:
				$targets = $pos->getLevel()->getEntities();
				break;
			case CommandSelector::SELECTOR_CLOSEST_PLAYER:
				$targets = [$pos->getLevel()->getNearestEntity($pos, 100, Player::class)];
				break;
			case CommandSelector::SELECTOR_RANDOM_PLAYER:
				$players = array_values($sender->getServer()->getOnlinePlayers());
				$targets = !empty($players) ? [$players[mt_rand(0, count($players) - 1)]] : [];
				break;
			case CommandSelector::SELECTOR_YOURSELF:
				$targets = [$sender];
				break;
			default:
				$targets = [$sender->getServer()->getPlayerExact($selector)];
				break;
		}

		foreach ($targets as $i => $target) {
			if ($target === null || !($target instanceof $entityType)) {
				unset($targets[$i]);
			}
		}

		if (empty($targets)) {
			throw new NoSelectorMatchException();
		}

		return $targets;
	}

	public static function findTarget(CommandSender $sender, string $selector, string $entityType = Entity::class, ?Vector3 $pos = null) : ?Entity
	{
		return !empty($targets = self::findTargets($sender, $selector, $entityType, $pos)) ? reset($targets) : null;
	}
}
