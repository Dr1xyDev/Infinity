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
use pocketmine\level\generator\vanilla\utils\Process;
use pocketmine\level\generator\vanilla\utils\TextFormat;

use function count;
use function floor;
use function microtime;
use function number_format;
use function round;

class StatusCommand extends VanillaCommand
{
	public function __construct(string $name)
	{
		parent::__construct(
			$name,
			"%pocketmine.command.status.description",
			"%pocketmine.command.status.usage"
		);
		$this->setPermission("pocketmine.command.status");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args)
	{
		if (!$this->testPermission($sender)) {
			return true;
		}

		$rUsage = Process::getRealMemoryUsage();
		$mUsage = Process::getAdvancedMemoryUsage();

		$server = $sender->getServer();
		$sender->sendMessage(TextFormat::GREEN . "---- " . TextFormat::WHITE . "Server status" . TextFormat::GREEN . " ----");

		$time = (int) (microtime(true) - $server->getStartTime());

		$seconds = $time % 60;
		$minutes = null;
		$hours = null;
		$days = null;

		if ($time >= 60) {
			$minutes = floor(($time % 3600) / 60);
			if ($time >= 3600) {
				$hours = floor(($time % (3600 * 24)) / 3600);
				if ($time >= 3600 * 24) {
					$days = floor($time / (3600 * 24));
				}
			}
		}

		$uptime = ($minutes !== null ?
				($hours !== null ?
					($days !== null ?
						"$days days "
						: "") . "$hours hours "
					: "") . "$minutes minutes "
				: "") . "$seconds seconds";

		$sender->sendMessage(TextFormat::GOLD . "Uptime: " . TextFormat::RED . $uptime);

		$tpsColor = TextFormat::GREEN;
		if ($server->getTicksPerSecond() < 12) {
			$tpsColor = TextFormat::RED;
		} elseif ($server->getTicksPerSecond() < 17) {
			$tpsColor = TextFormat::GOLD;
		}

		$sender->sendMessage(TextFormat::GOLD . "Current TPS: {$tpsColor}{$server->getTicksPerSecond()} ({$server->getTickUsage()}%)");
		$sender->sendMessage(TextFormat::GOLD . "Average TPS: {$tpsColor}{$server->getTicksPerSecondAverage()} ({$server->getTickUsageAverage()}%)");

		$sender->sendMessage(TextFormat::GOLD . "Online: " . TextFormat::RED . count($server->getOnlinePlayers()) . "/" . $server->getMaxPlayers());

		$sender->sendMessage(TextFormat::GOLD . "Network upload: " . TextFormat::RED . round($server->getNetwork()->getUpload() / 1024, 2) . " kB/s");
		$sender->sendMessage(TextFormat::GOLD . "Network download: " . TextFormat::RED . round($server->getNetwork()->getDownload() / 1024, 2) . " kB/s");

		$sender->sendMessage(TextFormat::GOLD . "Thread count: " . TextFormat::RED . Process::getThreadCount());

		$sender->sendMessage(TextFormat::GOLD . "Main thread memory: " . TextFormat::RED . number_format(round(($mUsage[0] / 1024) / 1024, 2), 2) . " MB.");
		$sender->sendMessage(TextFormat::GOLD . "Total memory: " . TextFormat::RED . number_format(round(($mUsage[1] / 1024) / 1024, 2), 2) . " MB.");
		$sender->sendMessage(TextFormat::GOLD . "Total virtual memory: " . TextFormat::RED . number_format(round(($mUsage[2] / 1024) / 1024, 2), 2) . " MB.");
		$sender->sendMessage(TextFormat::GOLD . "Heap memory: " . TextFormat::RED . number_format(round(($rUsage[0] / 1024) / 1024, 2), 2) . " MB.");

		$globalLimit = $server->getMemoryManager()->getGlobalMemoryLimit();
		if($globalLimit > 0){
			$sender->sendMessage(TextFormat::GOLD . "Maximum memory (manager): " . TextFormat::RED . number_format(round(($globalLimit / 1024) / 1024, 2), 2) . " MB.");
		}

		foreach($server->getLevels() as $level){
			$levelName = $level->getFolderName() !== $level->getName() ? " (" . $level->getName() . ")" : "";
			$timeColor = $level->getTickRateTime() > 40 ? TextFormat::RED : TextFormat::YELLOW;
			$sender->sendMessage(TextFormat::GOLD . "World \"{$level->getFolderName()}\"$levelName: " .
				TextFormat::RED . number_format(count($level->getChunks())) . TextFormat::GREEN . " chunks, " .
				TextFormat::RED . number_format(count($level->getEntities())) . TextFormat::GREEN . " entities, " .
				TextFormat::RED . number_format(count($level->getTiles())) . TextFormat::GREEN . " tiles. " .
				"Time $timeColor" . round($level->getTickRateTime(), 2) . "ms"
			);
		}
		return true;
	}
}
