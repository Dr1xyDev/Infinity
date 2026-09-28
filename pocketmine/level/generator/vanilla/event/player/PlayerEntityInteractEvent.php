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

use pocketmine\level\generator\vanilla\entity\Entity;
use pocketmine\level\generator\vanilla\event\Cancellable;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\Player;

/**
 * Called when a player interacts an entity.
 */
class PlayerEntityInteractEvent extends PlayerEvent implements Cancellable
{
	public function __construct(
		Player $player,
		private readonly Entity $entity,
		private readonly Vector3 $clickPos
	) {
		$this->player = $player;
	}

	public function getEntity() : Entity
	{
		return $this->entity;
	}

	public function getClickPosition() : Vector3
	{
		return $this->clickPos;
	}
}
