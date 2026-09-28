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

namespace pocketmine\level\generator\vanilla\stats;

use function array_map;

readonly class StatsData implements \JsonSerializable {

	public function __construct(
		private int             $timestamp,
		private string          $motd,
		private float           $tickPerSecond,
		private float           $tickUsage,
		private int             $online,
		private int             $port,
		private StatsMemoryData $statsRequestMemoryData,
		private string          $apiVersion,
		private string          $submarineVersion,
		/** @var StatsWorldData[] */
		private array           $statsRequestWorldData
	) {}

	/** @return StatsWorldData[] */
	public function getStatsRequestWorldData() : array{
		return $this->statsRequestWorldData;
	}

	public function jsonSerialize() : array{
		$worldData = array_map(static function ($requestWorldData) {
			return $requestWorldData->jsonSerialize();
		}, $this->getStatsRequestWorldData());

		return [
			'timestamp' => $this->timestamp,
			'motd' => $this->motd,
			'tps' => $this->tickPerSecond,
			'tpsUsage' => $this->tickUsage,
			'online' => $this->online,
			'port' => $this->port,
			'memory' => $this->statsRequestMemoryData->jsonSerialize(),
			'api' => $this->apiVersion,
			'version' => $this->submarineVersion,
			'worlds' => $worldData
		];
	}
}
