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
use pocketmine\level\generator\vanilla\entity\Mob;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\level\generator\vanilla\utils\Utils;

class SpawnEgg extends Item
{
	public function __construct(int $meta = 0)
	{
		parent::__construct(self::SPAWN_EGG, $meta, "Spawn Egg");
	}

	public function onActivate(Player $player, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector) : bool
	{
		$nbt = Entity::createBaseNBT($blockReplace->add(0.5, 0, 0.5), null, Utils::getRandomFloat() * 360, 0);

		if ($this->hasCustomName()) {
			$nbt->setString("CustomName", $this->getCustomName());
		}

		$entity = Entity::createEntity($this->meta, $player->getLevel(), $nbt);

		if ($entity instanceof Entity) {
			if ($entity instanceof Mob) {
				$entity->setImmobile(!Server::getInstance()->mobAiEnabled);
			}
			$this->pop();
			$entity->spawnToAll();
			return true;
		}

		return false;
	}
}
