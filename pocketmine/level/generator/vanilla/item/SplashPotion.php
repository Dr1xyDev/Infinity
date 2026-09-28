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

namespace pocketmine\level\generator\vanilla\item;

use pocketmine\level\generator\vanilla\nbt\tag\CompoundTag;

class SplashPotion extends ProjectileItem
{
	public function __construct(int $meta = 0)
	{
		parent::__construct(self::SPLASH_POTION, $meta, "Splash Potion");
	}

	public function getMaxStackSize() : int
	{
		return 1;
	}

	public function getProjectileEntityType() : string
	{
		return "ThrownPotion";
	}

	public function getThrowForce() : float
	{
		return 0.5;
	}

	public function getPitchOffset() : float
	{
		return -15;
	}

	protected function addExtraTags(CompoundTag $tag) : void
	{
		$tag->setShort("PotionId", $this->meta);
	}
}
