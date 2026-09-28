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

namespace pocketmine\level\generator\vanilla\entity;

class CreatureType
{
	/** @var string */
	protected $creatureClass;
	/** @var int */
	protected $maxSpawn;
	/** @var int */
	protected $materialIn;
	/** @var bool */
	protected $peacefulCreature = false;

	public function __construct(string $creatureClass, int $maxSpawn, int $materialIn, bool $peacefulCreature)
	{
		$this->creatureClass = $creatureClass;
		$this->maxSpawn = $maxSpawn;
		$this->materialIn = $materialIn;
		$this->peacefulCreature = $peacefulCreature;
	}

	public function getCreatureClass() : string
	{
		return $this->creatureClass;
	}

	public function getMaxSpawn() : int
	{
		return $this->maxSpawn;
	}

	public function getMaterialIn() : int
	{
		return $this->materialIn;
	}

	public function isPeacefulCreature() : bool
	{
		return $this->peacefulCreature;
	}
}
