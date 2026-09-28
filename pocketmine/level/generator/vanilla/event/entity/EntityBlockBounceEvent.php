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

namespace pocketmine\level\generator\vanilla\event\entity;

use pocketmine\level\generator\vanilla\block\Block;
use pocketmine\level\generator\vanilla\entity\Entity;
use pocketmine\level\generator\vanilla\event\Cancellable;

class EntityBlockBounceEvent extends EntityEvent implements Cancellable
{
	public function __construct(
		Entity $entity,
		protected readonly Block $block,
		protected float $motionMultiplier,
		protected float $fallDistanceMultiplier
	) {
		parent::__construct($entity);
	}

	public function getBlock() : Block
	{
		return $this->block;
	}

	public function getMotionMultiplier() : float
	{
		return $this->motionMultiplier;
	}

	public function setMotionMultiplier(float $motionMultiplier) : void
	{
		$this->motionMultiplier = $motionMultiplier;
	}

	public function getFallDistanceMultiplier() : float
	{
		return $this->fallDistanceMultiplier;
	}

	public function setFallDistanceMultiplier(float $fallDistanceMultiplier) : void
	{
		$this->fallDistanceMultiplier = $fallDistanceMultiplier;
	}
}
