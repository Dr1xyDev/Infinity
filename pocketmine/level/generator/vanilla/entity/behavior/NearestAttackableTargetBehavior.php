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

namespace pocketmine\level\generator\vanilla\entity\behavior;

use Closure;
use pocketmine\level\generator\vanilla\entity\Entity;
use pocketmine\level\generator\vanilla\entity\Living;
use pocketmine\level\generator\vanilla\entity\Mob;
use pocketmine\Player;

use function get_class;

use const PHP_INT_MAX;

class NearestAttackableTargetBehavior extends TargetBehavior
{
	protected $targetClass;
	private $targetChance;
	protected $targetEntity;
	protected $targetEntitySelector;

	public function __construct(Mob $mob, string $classTarget, bool $checkSight = false, int $chance = 0, bool $onlyNearby = false, ?Closure $targetSelector = null)
	{
		parent::__construct($mob, $checkSight, $onlyNearby);

		$this->targetClass = $classTarget;
		$this->targetChance = $chance;

		$this->setMutexBits(1);

		$this->targetEntitySelector = function (Entity $entity) use ($targetSelector) : bool {
			if ($targetSelector !== null && !$targetSelector($entity)) {
				return false;
			} else {
				if ($entity instanceof Player) {
					$d = $this->getTargetDistance();

					if ($entity->isSneaking()) {
						$d *= 0.8;
					}

					if ($entity->isInvisible()) {
						$av = 0;

						foreach ($entity->getArmorInventory()->getContents() as $item) {
							if (!$item->isNull()) {
								$av += 0.25;
							}
						}

						$d *= $av * 0.7;
					}

					if ($entity->distance($this->mob) > $d) {
						return false;
					}
				}

				return $entity instanceof Living && $this->isSuitableTargetLocal($entity, false);
			}
		};
	}

	public function canStart() : bool
	{
		if ($this->targetChance > 0 && $this->random->nextBoundedInt($this->targetChance) !== 0) {
			return false;
		} else {
			$d0 = $this->getTargetDistance();
			$list = $this->mob->level->getCollidingEntities($this->mob->getBoundingBox()->expandedCopy($d0, 4.0, $d0), $this->mob);
			$filters = [
				$this->targetEntitySelector,
				function (Entity $entity) : bool {
					if ($entity instanceof Player) {
						if ($entity->isCreative()) {
							return false;
						}
					}
					return get_class($entity) === $this->targetClass;
				}
			];

			// Filter
			foreach ($list as $i => $entity) {
				foreach ($filters as $filter) {
					if (!$filter($entity)) {
						unset($list[$i]);
					}
				}
			}

			// Sort
			$nearest = null;
			$lastDistanceSq = PHP_INT_MAX;
			foreach ($list as $entity) {
				$dSq = $entity->distanceSquared($this->mob);
				if ($dSq < $lastDistanceSq) {
					$lastDistanceSq = $dSq;
					$nearest = $entity;
				}
			}

			if ($nearest !== null) {
				$this->targetEntity = $nearest;

				return true;
			}

			return false;
		}
	}

	public function onStart() : void
	{
		$this->mob->setTargetEntity($this->targetEntity);

		parent::onStart();
	}
}
