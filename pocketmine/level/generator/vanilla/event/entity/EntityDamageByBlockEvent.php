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

/**
 * Called when an entity takes damage from a block.
 */
class EntityDamageByBlockEvent extends EntityDamageEvent
{
	/** @var Block */
	private $damager;

	/**
	 * @param float[] $modifiers
	 */
	public function __construct(Block $damager, Entity $entity, int $cause, float $damage, array $modifiers = [])
	{
		$this->damager = $damager;
		parent::__construct($entity, $cause, $damage, $modifiers);
	}

	public function getDamager() : Block
	{
		return $this->damager;
	}
}
