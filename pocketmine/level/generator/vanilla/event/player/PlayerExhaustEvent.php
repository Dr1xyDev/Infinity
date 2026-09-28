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

namespace pocketmine\level\generator\vanilla\event\player;

use pocketmine\level\generator\vanilla\entity\Human;
use pocketmine\level\generator\vanilla\event\Cancellable;
use pocketmine\level\generator\vanilla\event\entity\EntityEvent;

/**
 * @phpstan-extends EntityEvent<Human>
 */
class PlayerExhaustEvent extends EntityEvent implements Cancellable
{
	public const CAUSE_ATTACK = 1;
	public const CAUSE_DAMAGE = 2;
	public const CAUSE_MINING = 3;
	public const CAUSE_HEALTH_REGEN = 4;
	public const CAUSE_POTION = 5;
	public const CAUSE_WALKING = 6;
	public const CAUSE_SPRINTING = 7;
	public const CAUSE_SWIMMING = 8;
	public const CAUSE_JUMPING = 9;
	public const CAUSE_SPRINT_JUMPING = 10;
	public const CAUSE_CUSTOM = 11;

	/** @var float */
	private $amount;
	/** @var int */
	private $cause;

	/** @var Human */
	protected $player;

	public function __construct(Human $human, float $amount, int $cause)
	{
		$this->entity = $human;
		$this->player = $human;
		$this->amount = $amount;
		$this->cause = $cause;
	}

	/**
	 * @return Human
	 */
	public function getPlayer()
	{
		return $this->player;
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
	 * Returns an int cause of the exhaustion - one of the constants at the top of this class.
	 */
	public function getCause() : int
	{
		return $this->cause;
	}
}
