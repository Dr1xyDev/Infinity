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

use pocketmine\level\generator\vanilla\block\Block;
use pocketmine\level\generator\vanilla\entity\EffectInstance;
use pocketmine\level\generator\vanilla\entity\Living;

/**
 * Interface implemented by objects that can be consumed by mobs.
 */
interface Consumable extends Releasable
{
	/**
	 * Returns the leftover that this Consumable produces when it is consumed. For Items, this is usually air, but could
	 * be an Item to add to a Player's inventory afterwards (such as a bowl).
	 *
	 * @return Item|Block|mixed
	 */
	public function getResidue();

	/**
	 * @return EffectInstance[]
	 */
	public function getAdditionalEffects() : array;

	/**
	 * Called when this Consumable is consumed by mob, after standard resulting effects have been applied.
	 *
	 * @return void
	 */
	public function onConsume(Living $consumer);
}
