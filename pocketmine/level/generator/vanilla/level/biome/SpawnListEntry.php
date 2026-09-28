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

namespace pocketmine\level\generator\vanilla\level\biome;

use pocketmine\level\generator\vanilla\utils\WeightedRandomItem;

class SpawnListEntry extends WeightedRandomItem {
	public string $entityClass;
	public int $minGroupCount = 0;
	public int $maxGroupCount = 0;

	public function __construct(string $entityClass, int $itemWeight, int $minGroupCount, int $maxGroupCount)
	{
		parent::__construct($itemWeight);

		$this->entityClass = $entityClass;
		$this->minGroupCount = $minGroupCount;
		$this->maxGroupCount = $maxGroupCount;
	}
}
