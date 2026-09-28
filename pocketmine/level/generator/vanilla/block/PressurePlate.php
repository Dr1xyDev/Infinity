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

use pocketmine\level\generator\vanilla\block\utils\StaticSupportTrait;
use pocketmine\level\generator\vanilla\entity\Entity;
use pocketmine\level\generator\vanilla\event\block\PressurePlateUpdateEvent;
use pocketmine\level\generator\vanilla\level\sound\PressurePlateActivateSound;
use pocketmine\level\generator\vanilla\level\sound\PressurePlateDeactivateSound;
use pocketmine\level\generator\vanilla\math\Axis;
use pocketmine\level\generator\vanilla\math\AxisAlignedBB;
use pocketmine\level\generator\vanilla\math\Facing;

use function count;

abstract class PressurePlate extends Transparent
{
	use StaticSupportTrait;

	public function getVariantBitmask() : int
	{
		return 0;
	}

	public function isSolid() : bool
	{
		return false;
	}

	protected function recalculateCollisionBoxes() : array
	{
		return [];
	}

	protected function canBeSupportedAt(Block $block) : bool
	{
		$down = $this->getSide(Facing::DOWN);
		return !$down->isTransparent() || $down->isNarrowSurface() || $this->canStayOnFullSolid($down);
	}

	public function hasEntityCollision() : bool
	{
		return true;
	}

	public function onEntityCollide(Entity $entity) : void
	{
		if (!$this->hasOutputSignal()) {
			$this->level->scheduleDelayedBlockUpdate($this, 0);
		}
	}

	/**
	 * Returns the AABB that entities must intersect to activate the pressure plate.
	 * Note that this is not the same as the collision box (pressure plate doesn't have one), nor the visual bounding
	 * box. The activation area has a height of 0.25 blocks.
	 */
	protected function getActivationBox() : AxisAlignedBB
	{
		return AxisAlignedBB::one()
			->squash(Axis::X, 1 / 8)
			->squash(Axis::Z, 1 / 8)
			->trim(Facing::UP, 3 / 4)
			->offset($this->x, $this->y, $this->z);
	}

	protected function hasOutputSignal() : bool
	{
		return $this->isActivated(); //TODO: Redstone
	}

	protected function calculatePlateState(array $entities) : array
	{
		$newPressed = count($entities) > 0;
		if ($newPressed === $this->isActivated()) {
			return [$this, null];
		}
		return [
			(clone $this)->setDamage($newPressed ? 1 : 0),
			$newPressed
		];
	}

	/**
	 * Filters entities which don't affect the pressure plate state from the given list.
	 *
	 * @param Entity[] $entities
	 * @return Entity[]
	 */
	protected function filterIrrelevantEntities(array $entities) : array
	{
		return $entities;
	}

	public function onScheduledUpdate() : void
	{
		$intersectionAABB = $this->getActivationBox();
		$activatingEntities = $this->filterIrrelevantEntities($this->level->getNearbyEntities($intersectionAABB));

		//if an irrelevant entity is inside the full cube space of the pressure plate but not activating the plate,
		//it will cause scheduled updates on the plate every tick. We don't want to fire events in this case if the
		//plate is already deactivated.
		if (count($activatingEntities) > 0 || $this->hasOutputSignal()) {
			[$newState, $pressedChange] = $this->calculatePlateState($activatingEntities);

			$ev = new PressurePlateUpdateEvent($this, $newState, $activatingEntities);
			$ev->call();
			$newState = $ev->isCancelled() ? null : $ev->getNewState();

			if ($newState !== null) {
				$this->level->setBlock($this, $newState);
				if ($pressedChange !== null) {
					$this->level->addSound(
						$pressedChange ?
						new PressurePlateActivateSound($this, $this) :
						new PressurePlateDeactivateSound($this, $this)
					);
				}
			}
			if ($pressedChange ?? $this->hasOutputSignal()) {
				$this->level->scheduleDelayedBlockUpdate($this, 20);
			}
		}
	}

	abstract public function getDeactivationDelayTicks() : int;

	public function isActivated() : bool
	{
		return $this->meta !== 0;
	}
}
