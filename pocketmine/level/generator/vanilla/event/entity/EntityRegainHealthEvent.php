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

use pocketmine\level\generator\vanilla\entity\Entity;
use pocketmine\level\generator\vanilla\event\Cancellable;

/**
 * @phpstan-extends EntityEvent<Entity>
 */
class EntityRegainHealthEvent extends EntityEvent implements Cancellable
{
	public const CAUSE_REGEN = 0;
	public const CAUSE_EATING = 1;
	public const CAUSE_MAGIC = 2;
	public const CAUSE_CUSTOM = 3;
	public const CAUSE_SATURATION = 4;

	/** @var float */
	private $amount;
	/** @var int */
	private $reason;

	public function __construct(Entity $entity, float $amount, int $regainReason)
	{
		$this->entity = $entity;
		$this->amount = $amount;
		$this->reason = $regainReason;
	}

	public function getAmount() : float
	{
		return $this->amount;
	}

	public function setAmount(float $amount) : void
	{
		$this->amount = $amount;
	}

	/**
	 * Returns one of the CAUSE_* constants to indicate why this regeneration occurred.
	 */
	public function getRegainReason() : int
	{
		return $this->reason;
	}
}
