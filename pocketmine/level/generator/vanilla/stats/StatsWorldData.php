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

readonly class StatsWorldData implements \JsonSerializable {

	public function __construct(
		private string $worldName,
		private int    $players,
		private int    $chunks,
		private int    $entities
	) {}

	public function jsonSerialize() : array{
		return [
			'name' => $this->worldName,
			'players' => $this->players,
			'chunks' => $this->chunks,
			'entities' => $this->entities
		];
	}
}
