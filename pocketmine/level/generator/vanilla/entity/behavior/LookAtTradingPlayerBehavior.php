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

namespace pocketmine\level\generator\vanilla\entity\behavior;

use pocketmine\level\generator\vanilla\entity\passive\Villager;

class LookAtTradingPlayerBehavior extends LookAtPlayerBehavior
{
	/** @var Villager */
	protected $mob;

	public function __construct(Villager $villager)
	{
		parent::__construct($villager, 8);
	}

	public function canStart() : bool
	{
		if ($this->mob->getTradingPlayer() !== null) {
			$this->nearestEntity = $this->mob->getTradingPlayer();

			return true;
		}

		return false;
	}

}
