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

namespace pocketmine\level\generator\vanilla\event\level;

use pocketmine\level\generator\vanilla\level\Level;
use pocketmine\level\generator\vanilla\level\Position;

/**
 * An event that is called when a level spawn changes.
 * The previous spawn is included
 */
class SpawnChangeEvent extends LevelEvent
{
	/** @var Position */
	private $previousSpawn;

	public function __construct(Level $level, Position $previousSpawn)
	{
		parent::__construct($level);
		$this->previousSpawn = $previousSpawn;
	}

	public function getPreviousSpawn() : Position
	{
		return $this->previousSpawn;
	}
}
