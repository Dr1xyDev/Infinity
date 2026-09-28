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

use pocketmine\level\generator\vanilla\entity\Effect;
use pocketmine\level\generator\vanilla\entity\EffectInstance;

class GoldenApple extends Food
{
	public function __construct(int $meta = 0)
	{
		parent::__construct(self::GOLDEN_APPLE, $meta, "Golden Apple");
	}

	public function requiresHunger() : bool
	{
		return false;
	}

	public function getFoodRestore() : int
	{
		return 4;
	}

	public function getSaturationRestore() : float
	{
		return 9.6;
	}

	public function getAdditionalEffects() : array
	{
		return [
			new EffectInstance(Effect::getEffect(Effect::REGENERATION), 100, 1),
			new EffectInstance(Effect::getEffect(Effect::ABSORPTION), 2400)
		];
	}
}
