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

use pocketmine\level\generator\vanilla\entity\Entity;
use pocketmine\level\generator\vanilla\entity\projectile\FishingHook;
use pocketmine\level\generator\vanilla\event\Cancellable;
use pocketmine\Player;

class PlayerFishEvent extends PlayerEvent implements Cancellable
{
	public const STATE_FISHING = 0;
	public const STATE_CAUGHT_FISH = 1;
	public const STATE_CAUGHT_ENTITY = 2;

	/** @var FishingHook */
	protected $hook;
	/** @var int */
	protected $xpDropAmount = 0;
	/** @var int */
	protected $state = 0;

	protected $result;

	protected $name = null;

	protected $lore = null;

	public function __construct(Player $fisher, FishingHook $hook, int $state, $result, $name, $lore, int $xpDropAmount = 0)
	{
		$this->player = $fisher;
		$this->hook = $hook;
		$this->state = $state;
		$this->xpDropAmount = $xpDropAmount;
		$this->result = $result;
		$this->name = $name;
		$this->lore = $lore;
	}

	public function getResult()
	{
		return $this->result;
	}

	public function setResult($result)
	{
		$this->result = $result;
	}

	public function getName()
	{
		return $this->name;
	}

	public function setName($name)
	{
		$this->name = $name;
	}

	public function getLore()
	{
		return $this->lore;
	}

	public function setLore($lore)
	{
		$this->lore = $lore;
	}

	public function getCaughtEntity() : ?Entity
	{
		return $this->hook->getRidingEntity();
	}

	public function getHook() : FishingHook
	{
		return $this->hook;
	}

	public function getXpDropAmount() : int
	{
		return $this->xpDropAmount;
	}

	public function setXpDropAmount(int $xpDropAmount) : void
	{
		$this->xpDropAmount = $xpDropAmount;
	}

	public function getState() : int
	{
		return $this->state;
	}
}
