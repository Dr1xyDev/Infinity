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
use pocketmine\level\generator\vanilla\entity\projectile\FishingHook;
use pocketmine\level\generator\vanilla\event\player\PlayerFishEvent;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\Player;

class FishingRod extends Tool
{
	public function __construct()
	{
		parent::__construct(self::FISHING_ROD, 0, "Fishing Rod");
	}

	public function getMaxStackSize() : int
	{
		return 1;
	}

	public function getMaxDurability() : int
	{
		return 384;
	}

	public function getEnchantAbility() : int{
		return 1;
	}

	public function onClickAir(Player $player, Vector3 $directionVector) : bool
	{
		if ($player->getFishingHook() === null) {
			$hook = new FishingHook($player->level, Entity::createBaseNBT($player->add(0, $player->getEyeHeight() - 0.1, 0), $player->getDirectionVector()->multiply(0.4)), $player);
			($ev = new PlayerFishEvent($player, $hook, PlayerFishEvent::STATE_FISHING, null, null, null))->call();
			if ($ev->isCancelled()) {
				$hook->flagForDespawn();
			} else {
				$hook->spawnToAll();
			}
			return true;
		} else {
			$hook = $player->getFishingHook();
			$hook->handleHookRetraction();
			$this->applyDamage(1);
		}

		return true;
	}
}
