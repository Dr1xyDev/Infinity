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

use pocketmine\level\generator\vanilla\entity\Mob;
use pocketmine\level\generator\vanilla\event\entity\EntityDamageByEntityEvent;
use pocketmine\level\generator\vanilla\event\entity\EntityDamageEvent;
use pocketmine\level\generator\vanilla\math\Vector3;

use function max;

class MeleeAttackBehavior extends Behavior
{
	/** @var float */
	protected $speedMultiplier = 0;

	/** @var int */
	protected $attackCooldown = 0;
	/** @var int */
	protected $delay = 0;
	/** @var Vector3 */
	protected $lastPlayerPos;

	protected $path;

	public function __construct(Mob $mob, float $speedMultiplier)
	{
		parent::__construct($mob);

		$this->speedMultiplier = $speedMultiplier;
		$this->mutexBits = 3;
	}

	public function canStart() : bool
	{
		$target = $this->mob->getTargetEntity();
		if ($target === null) {
			return false;
		}

		$this->lastPlayerPos = $target->asVector3();

		$this->path = $this->mob->getNavigator()->findPath($target);
		return $this->path->havePath();
	}

	public function onStart() : void
	{
		$this->delay = 0;
		$this->mob->getNavigator()->setPath($this->path);
		$this->mob->getNavigator()->setSpeedMultiplier($this->speedMultiplier);
	}

	public function canContinue() : bool
	{
		return $this->mob->getTargetEntity() !== null;
	}

	public function onTick() : void
	{
		if (!$this->mob->isAlive()) {
			return;
		}

		$target = $this->mob->getTargetEntity();

		if ($target === null) {
			return;
		} elseif (!$target->isAlive()) {
			$this->mob->setTargetEntity(null);
			return;
		}

		$distanceToPlayer = $this->mob->distanceSquared($target);

		--$this->delay;

		$deltaDistance = $this->lastPlayerPos->distanceSquared($target);

		if ($this->delay <= 0 && $this->mob->canSeeEntity($target) && ($deltaDistance > 1 || $this->random->nextFloat() < 0.05)) {
			$this->lastPlayerPos = $target->asVector3();

			$this->delay = 4 + $this->random->nextBoundedInt(7);

			if ($distanceToPlayer > 1024) {
				$this->delay += 10;
			} elseif ($distanceToPlayer > 256) {
				$this->delay += 5;
			}

			if (!$this->mob->getNavigator()->tryMoveTo($target, $this->speedMultiplier)) {
				$this->delay += 15;
			}
		}

		$this->mob->getLookHelper()->setLookPositionWithEntity($target, 30, 30);

		$this->attackCooldown = max($this->attackCooldown - 1, 0);
		if ($this->attackCooldown <= 0 && $distanceToPlayer < $this->getAttackReach()) {
			$damage = $this->mob->getAttackDamage();
			$target->attack($event = new EntityDamageByEntityEvent($this->mob, $target, EntityDamageEvent::CAUSE_ENTITY_ATTACK, $damage));
			$this->mob->onAttack($event);
			$this->attackCooldown = 20;
		}
	}

	public function getAttackReach() : float
	{
		return $this->mob->width * 2.0 + $this->mob->getTargetEntity()->width;
	}

	public function onEnd() : void
	{
		$this->mob->pitch = 0;
		$this->attackCooldown = $this->delay = 0;
		$this->path = null;
		$this->mob->getNavigator()->clearPath();
	}

}
