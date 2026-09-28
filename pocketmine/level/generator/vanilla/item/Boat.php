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

namespace pocketmine\level\generator\vanilla\item;

use pocketmine\level\generator\vanilla\block\Block;
use pocketmine\level\generator\vanilla\entity\Entity;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\Player;

class Boat extends Item
{
	public function getFuelTime() : int
	{
		return 1200; //400 in PC
	}

	public function getMaxStackSize() : int
	{
		return 1;
	}

	public function onActivate(Player $player, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector) : bool
	{
		$nbt = Entity::createBaseNBT($blockReplace->add(0.5, 1, 0.5));
		$nbt->setInt("Variant", $this->getDamage());
		$entity = Entity::createEntity("Boat", $player->level, $nbt);
		$entity->spawnToAll();

		$this->pop();

		return true;
	}
}
