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

namespace pocketmine\level\generator\vanilla\network\mcpe\cache;

use pocketmine\level\generator\vanilla\item\Item;

/**
 * Info for an item group in the creative inventory menu.
 */
final class CreativeGroup{
	/**
	 * @param string $name Tooltip shown to the player on hover
	 * @param Item   $icon Item shown when the group is collapsed
	 */
	public function __construct(
		private readonly string $name,
		private readonly Item $icon
	){}

	public function getName() : string{ return $this->name; }

	public function getIcon() : Item{ return clone $this->icon; }
}
