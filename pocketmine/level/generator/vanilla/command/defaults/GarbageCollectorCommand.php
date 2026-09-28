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
use pocketmine\level\generator\vanilla\utils\TextFormat;

use function count;
use function memory_get_usage;
use function number_format;
use function round;

class GarbageCollectorCommand extends VanillaCommand
{
	public function __construct(string $name)
	{
		parent::__construct(
			$name,
			"%pocketmine.command.gc.description",
			"%pocketmine.command.gc.usage"
		);
		$this->setPermission("pocketmine.command.gc");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args)
	{
		if (!$this->testPermission($sender)) {
			return true;
		}

		$chunksCollected = 0;
		$entitiesCollected = 0;
		$tilesCollected = 0;

		$memory = memory_get_usage();

		foreach ($sender->getServer()->getLevels() as $level) {
			$diff = [count($level->getChunks()), count($level->getEntities())];
			$level->doChunkGarbageCollection();
			$level->unloadChunks(true);
			$chunksCollected += $diff[0] - count($level->getChunks());
			$entitiesCollected += $diff[1] - count($level->getEntities());
			$level->clearCache(true);
		}

		$cyclesCollected = $sender->getServer()->getMemoryManager()->triggerGarbageCollector();

		$sender->sendMessage(TextFormat::GREEN . "---- " . TextFormat::WHITE . "Garbage collection result" . TextFormat::GREEN . " ----");
		$sender->sendMessage(TextFormat::GOLD . "Chunks: " . TextFormat::RED . number_format($chunksCollected));
		$sender->sendMessage(TextFormat::GOLD . "Entities: " . TextFormat::RED . number_format($entitiesCollected));
		$sender->sendMessage(TextFormat::GOLD . "Tiles: " . TextFormat::RED . number_format($tilesCollected));

		$sender->sendMessage(TextFormat::GOLD . "Cycles: " . TextFormat::RED . number_format($cyclesCollected));
		$sender->sendMessage(TextFormat::GOLD . "Memory freed: " . TextFormat::RED . number_format(round((($memory - memory_get_usage()) / 1024) / 1024, 2), 2) . " MB");
		return true;
	}
}
