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
use pocketmine\level\generator\vanilla\level\Explosion;
use pocketmine\level\generator\vanilla\utils\Utils;

/**
 * Called when a block wants to explode, before the explosion impact is calculated.
 * This allows changing the explosion force, fire chance and whether it will destroy blocks.
 *
 * @see BlockExplodeEvent
 */
class EntityPreExplodeEvent extends EntityEvent implements Cancellable{

	private bool $blockBreaking = true;

	public function __construct(
		Entity $entity,
		protected float $radius,
		private float $fireChance = 0.0,
	){
		if($radius <= 0){
			throw new \InvalidArgumentException("Explosion radius must be positive");
		}
		Utils::checkFloatNotInfOrNaN("fireChance", $fireChance);
		if($fireChance < 0.0 || $fireChance > 1.0){
			throw new \InvalidArgumentException("Fire chance must be between 0 and 1.");
		}
		$this->entity = $entity;
	}

	public function getRadius() : float{
		return $this->radius;
	}

	public function setRadius(float $radius) : void{
		if($radius <= 0){
			throw new \InvalidArgumentException("Explosion radius must be positive");
		}
		$this->radius = $radius;
	}

	/**
	 * Returns whether the explosion will create a fire.
	 */
	public function isIncendiary() : bool{
		return $this->fireChance > 0;
	}

	/**
	 * Sets whether the explosion will create a fire by filling fireChance with default values.
	 *
	 * If $incendiary is true, the fire chance will be filled only if explosion isn't currently creating a fire (if fire chance is 0).
	 */
	public function setIncendiary(bool $incendiary) : void{
		if(!$incendiary){
			$this->fireChance = 0;
		}elseif($this->fireChance <= 0){
			$this->fireChance = Explosion::DEFAULT_FIRE_CHANCE;
		}
	}

	/**
	 * Returns a chance between 0 and 1 of creating a fire.
	 */
	public function getFireChance() : float{
		return $this->fireChance;
	}

	/**
	 * Sets a chance between 0 and 1 of creating a fire.
	 * For example, if the chance is 1/3, then that amount of affected blocks will be ignited.
	 *
	 * @param float $fireChance 0 ... 1
	 */
	public function setFireChance(float $fireChance) : void{
		Utils::checkFloatNotInfOrNaN("fireChance", $fireChance);
		if($fireChance < 0.0 || $fireChance > 1.0){
			throw new \InvalidArgumentException("Fire chance must be between 0 and 1.");
		}
		$this->fireChance = $fireChance;
	}

	public function isBlockBreaking() : bool{
		return $this->blockBreaking;
	}

	public function setBlockBreaking(bool $affectsBlocks) : void{
		$this->blockBreaking = $affectsBlocks;
	}
}
