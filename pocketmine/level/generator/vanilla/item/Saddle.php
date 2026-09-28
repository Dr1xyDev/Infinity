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

use pocketmine\level\generator\vanilla\entity\Entity;
use pocketmine\level\generator\vanilla\entity\passive\Pig;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\Player;

class Saddle extends Item
{
	public function __construct(int $meta = 0)
	{
		parent::__construct(self::SADDLE, $meta, "Saddle");
	}

	public function getMaxStackSize() : int
	{
		return 1;
	}

	public function onInteractEntity(Player $player, Entity $entity, Vector3 $clickVector) : bool
	{
		if ($entity instanceof Pig) {
			if (!$entity->isSaddled() && !$entity->isBaby()) {
				$entity->setSaddled(true);
				$entity->level->broadcastLevelSoundEvent($entity, LevelSoundEventPacket::SOUND_SADDLE);
				$this->pop();
			}

			return true;
		}
		return false;
	}
}
