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
use pocketmine\level\generator\vanilla\entity\object\FallingBlock;
use pocketmine\level\generator\vanilla\level\sound\Sound;
use pocketmine\level\generator\vanilla\math\Facing;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\utils\AssumptionFailedError;

abstract class Fallable extends Solid
{
	public function onNearbyBlockChange() : void
	{
		$level = $this->level;
		$down = $level->getBlock($this->getSide(Facing::DOWN));
		if($down->canBeReplaced()){
			$level->setBlock($this, BlockFactory::get(BlockIds::AIR));

			$block = $this;
			if(!($block instanceof Block)) throw new AssumptionFailedError(__TRAIT__ . " should only be used by Blocks");

			$nbt = Entity::createBaseNBT($this->add(0.5, 0, 0.5));
			$nbt->setInt("TileID", $this->getId());
			$nbt->setByte("Data", $this->getDamage());

			$fall = Entity::createEntity("FallingSand", $this->getLevel(), $nbt);
			if ($fall instanceof FallingBlock) {
				$fall->spawnToAll();
			}
		}
	}

	/**
	 * Called every tick by FallingBlock to update the falling state of this block. Used by concrete to check when it
	 * hits water.
	 * Return null if you don't want to change the usual behaviour.
	 */
	public function tickFalling() : ?Block {
		return null;
	}

	/**
	 * Called when FallingBlock hits the ground.
	 * Returns whether the block should be placed.
	 */
	public function onHitGround(FallingBlock $blockEntity, float $fallDistance) : bool {
		return true;
	}

	/**
	 * Returns the damage caused per fallen block. This is multiplied by the fall distance (and capped according to
	 * {@link Fallable::getMaxFallDamage()}) to calculate the damage dealt to any entities who intersect with the block
	 * when it hits the ground.
	 */
	public function getFallDamagePerBlock() : float {
		return 0.0;
	}

	/**
	 * Returns the maximum damage the block can deal to an entity when it hits the ground.
	 */
	public function getMaxFallDamage() : float {
		return 0.0;
	}

	/**
	 * Returns the sound that will be played when FallingBlock hits the ground.
	 */
	public function getLandSound(Vector3 $position) : ?Sound{
		return null;
	}
}
