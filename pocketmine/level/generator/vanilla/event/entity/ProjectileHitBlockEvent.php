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
use pocketmine\level\generator\vanilla\entity\projectile\Projectile;
use pocketmine\level\generator\vanilla\math\RayTraceResult;

class ProjectileHitBlockEvent extends ProjectileHitEvent
{
	/** @var Block */
	private $blockHit;

	public function __construct(Projectile $entity, RayTraceResult $rayTraceResult, Block $blockHit)
	{
		parent::__construct($entity, $rayTraceResult);
		$this->blockHit = $blockHit;
	}

	/**
	 * Returns the Block struck by the projectile.
	 * Hint: to get the block face hit, look at the RayTraceResult.
	 */
	public function getBlockHit() : Block
	{
		return $this->blockHit;
	}
}
