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

class GoldenAppleEnchanted extends GoldenApple
{
	public function __construct(int $meta = 0)
	{
		Food::__construct(self::ENCHANTED_GOLDEN_APPLE, $meta, "Enchanted Golden Apple"); //skip parent constructor
	}

	public function getAdditionalEffects() : array
	{
		return [
			new EffectInstance(Effect::getEffect(Effect::REGENERATION), 600, 4),
			new EffectInstance(Effect::getEffect(Effect::ABSORPTION), 2400, 3),
			new EffectInstance(Effect::getEffect(Effect::RESISTANCE), 6000),
			new EffectInstance(Effect::getEffect(Effect::FIRE_RESISTANCE), 6000)
		];
	}
}
