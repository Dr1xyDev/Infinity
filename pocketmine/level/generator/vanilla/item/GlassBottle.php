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
use pocketmine\level\generator\vanilla\block\Water;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\Player;

class GlassBottle extends Item
{
	public function __construct(int $meta = 0)
	{
		parent::__construct(self::GLASS_BOTTLE, $meta, "Glass Bottle");
	}

	public function onActivate(Player $player, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector) : bool
	{
		if ($blockClicked instanceof Water) {
			$waterPotion = ItemFactory::get(Item::POTION, 0, 1);
			$stack = clone $this;

			if ($player->hasFiniteResources() && $this->getCount() === 0) {
				$player->getInventory()->setItemInHand($waterPotion);
				return true;
			}

			$this->pop();

			foreach ($player->getInventory()->addItem($waterPotion) as $remains) {
				if (!$player->dropItem($remains)) {
					$player->getInventory()->setItemInHand($stack);
					return false;
				}
			}

			return true;
		}

		return false;
	}
}
