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

namespace pocketmine\level\generator\vanilla\block;

use pocketmine\level\generator\vanilla\entity\Entity;

class Slime extends Solid
{
	protected $id = self::SLIME_BLOCK;

	public function __construct()
	{

	}

	public function hasEntityCollision() : bool
	{
		return true;
	}

	public function getHardness() : float
	{
		return 0;
	}

	public function getFrictionFactor() : float
	{
		return 0.8;
	}

	public function getName() : string
	{
		return "Slime Block";
	}

	public function onEntityCollideUpon(Entity $entity) : void
	{
		$entity->resetFallDistance();
	}

	public function getBounceMotionMultiplier() : float
	{
		return 1.0;
	}

	public function getBounceFallDistanceMultiplier() : float
	{
		return 0.0;
	}

	public function onEntityFallenUpon(Entity $entity, float $fallDistance) : void
	{
		if ($entity->isSneaking()) {
			parent::onEntityFallenUpon($entity, $fallDistance);
		} else {
			$entity->fall($fallDistance);
		}
	}
}
