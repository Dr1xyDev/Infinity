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

namespace pocketmine\level\generator\vanilla\level;

use function max;
use function min;

class DifficultyInstance
{
	/** @var int */
	protected $difficulty;
	/** @var float */
	protected $additionalDifficulty;

	public function __construct(int $difficulty, int $time, int $chunkInhabitedTime, int $moonPhaseFactor)
	{
		$this->difficulty = $difficulty;
		$this->additionalDifficulty = $this->calculateAdditionalDifficulty($difficulty, $time, $chunkInhabitedTime, $moonPhaseFactor);
	}

	private function calculateAdditionalDifficulty(int $difficulty, int $time, int $chunkInhabitedTime, int $moonPhaseFactor) : float
	{
		if ($difficulty === Level::DIFFICULTY_PEACEFUL) {
			return 0;
		} else {
			$f = 0.75 + max(0, min(1, ($time - 72000) / 1440000)) * 0.25;
			$f2 = max(0, min(1, $chunkInhabitedTime / 3600000)) * ($difficulty === Level::DIFFICULTY_HARD ? 1 : 0.75);
			$f2 += max(0, min($f - 0.75, $moonPhaseFactor * 0.25));

			if ($difficulty === Level::DIFFICULTY_EASY) {
				$f2 *= 0.5;
			}

			return $difficulty * ($f + $f2);
		}
	}

	public function getAdditionalDifficulty() : float
	{
		return $this->additionalDifficulty;
	}

	public function getClampedAdditionalDifficulty() : float
	{
		return $this->additionalDifficulty < 2 ? 0 : ($this->additionalDifficulty > 4 ? 1 : ($this->additionalDifficulty - 2) / 2);
	}

	public function getDifficulty() : int
	{
		return $this->difficulty;
	}
}
