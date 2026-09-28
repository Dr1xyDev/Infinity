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

namespace pocketmine\level\generator\vanilla\event\player;

use InvalidArgumentException;
use pocketmine\level\generator\vanilla\entity\Human;
use pocketmine\level\generator\vanilla\event\Cancellable;
use pocketmine\level\generator\vanilla\event\entity\EntityEvent;

/**
 * Called when a player gains or loses XP levels and/or progress.
 * @phpstan-extends EntityEvent<Human>
 */
class PlayerExperienceChangeEvent extends EntityEvent implements Cancellable
{
	/** @var int */
	private $oldLevel;
	/** @var float */
	private $oldProgress;
	/** @var int|null */
	private $newLevel;
	/** @var float|null */
	private $newProgress;

	public function __construct(Human $player, int $oldLevel, float $oldProgress, ?int $newLevel, ?float $newProgress)
	{
		$this->entity = $player;

		$this->oldLevel = $oldLevel;
		$this->oldProgress = $oldProgress;
		$this->newLevel = $newLevel;
		$this->newProgress = $newProgress;
	}

	public function getOldLevel() : int
	{
		return $this->oldLevel;
	}

	public function getOldProgress() : float
	{
		return $this->oldProgress;
	}

	/**
	 * @return int|null null indicates no change
	 */
	public function getNewLevel() : ?int
	{
		return $this->newLevel;
	}

	/**
	 * @return float|null null indicates no change
	 */
	public function getNewProgress() : ?float
	{
		return $this->newProgress;
	}

	public function setNewLevel(?int $newLevel) : void
	{
		$this->newLevel = $newLevel;
	}

	public function setNewProgress(?float $newProgress) : void
	{
		if ($newProgress < 0.0 || $newProgress > 1.0) {
			throw new InvalidArgumentException("XP progress must be in range 0-1");
		}
		$this->newProgress = $newProgress;
	}
}
