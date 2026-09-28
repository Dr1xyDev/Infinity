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

use InvalidArgumentException;
use pocketmine\level\generator\vanilla\entity\Living;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\utils\Utils;

/**
 * @phpstan-extends EntityEvent<Living>
 */
class EntityDeathEvent extends EntityEvent
{
	/** @var Item[] */
	private $drops = [];
	/** @var int */
	private $xp;

	/**
	 * @param Item[] $drops
	 */
	public function __construct(Living $entity, array $drops = [], int $xp = 0)
	{
		$this->entity = $entity;
		$this->drops = $drops;
		$this->xp = $xp;
	}

	/**
	 * @return Living
	 */
	public function getEntity()
	{
		return $this->entity;
	}

	/**
	 * @return Item[]
	 */
	public function getDrops() : array
	{
		return $this->drops;
	}

	/**
	 * @param Item[] $drops
	 */
	public function setDrops(array $drops) : void
	{
		Utils::validateArrayValueType($drops, function (Item $_) : void { });
		$this->drops = $drops;
	}

	/**
	 * Returns how much experience is dropped due to this entity's death.
	 */
	public function getXpDropAmount() : int
	{
		return $this->xp;
	}

	/**
	 * @throws InvalidArgumentException
	 */
	public function setXpDropAmount(int $xp) : void
	{
		if ($xp < 0) {
			throw new InvalidArgumentException("XP drop amount must not be negative");
		}
		$this->xp = $xp;
	}
}
