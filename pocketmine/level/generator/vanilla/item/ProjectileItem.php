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
use pocketmine\level\generator\vanilla\entity\projectile\Projectile;
use pocketmine\level\generator\vanilla\event\entity\ProjectileLaunchEvent;
use pocketmine\level\generator\vanilla\level\sound\ThrowSound;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\nbt\tag\CompoundTag;
use pocketmine\Player;

abstract class ProjectileItem extends Item
{
	abstract public function getProjectileEntityType() : string;

	abstract public function getThrowForce() : float;

	public function getPitchOffset() : float
	{
		return 0.0;
	}

	/**
	 * Helper function to apply extra NBT tags to pass to the created projectile.
	 */
	protected function addExtraTags(CompoundTag $tag) : void
	{

	}

	public function onClickAir(Player $player, Vector3 $directionVector) : bool
	{
		$nbt = Entity::createBaseNBT($player->add(0, $player->getEyeHeight(), 0), $directionVector, $player->yaw, $player->pitch);
		$this->addExtraTags($nbt);

		$projectile = Entity::createEntity($this->getProjectileEntityType(), $player->getLevel(), $nbt, $player);
		if ($projectile !== null) {
			$projectile->entityShoot($player, $this->getPitchOffset(), $this->getThrowForce(), 1.0);
		}

		$this->pop();

		if ($projectile instanceof Projectile) {
			$projectileEv = new ProjectileLaunchEvent($projectile);
			$projectileEv->call();
			if ($projectileEv->isCancelled()) {
				$projectile->flagForDespawn();
			} else {
				$projectile->spawnToAll();

				$player->getLevel()->addSound(new ThrowSound($player));
			}
		} elseif ($projectile !== null) {
			$projectile->spawnToAll();
		} else {
			return false;
		}

		return true;
	}
}
