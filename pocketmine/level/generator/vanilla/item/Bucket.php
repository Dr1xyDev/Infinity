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
use pocketmine\level\generator\vanilla\block\BlockFactory;
use pocketmine\level\generator\vanilla\block\BlockIds;
use pocketmine\level\generator\vanilla\block\Liquid;
use pocketmine\level\generator\vanilla\event\player\PlayerBucketFillEvent;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\Player;

class Bucket extends Item
{
	public function getMaxStackSize() : int
	{
		return 16;
	}

	public function onActivate(Player $player, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector) : bool
	{
		//TODO: move this to generic placement logic

		if ($blockClicked instanceof Liquid && $blockClicked->getDamage() === 0) {
			$stack = clone $this;
			$stack->pop();

			$resultItem = ItemFactory::get(Item::BUCKET, $blockClicked->getFlowingForm()->getId());
			$ev = new PlayerBucketFillEvent($player, $blockReplace, $face, $this, $resultItem);
			$ev->call();
			if (!$ev->isCancelled()) {
				$level = $player->getLevel();
				$level->setBlock($blockClicked, BlockFactory::get(BlockIds::AIR), true, true);
				$level->broadcastLevelSoundEvent($blockClicked->add(0.5, 0.5, 0.5), $blockClicked->getBucketFillSound());
				if ($player->isSurvival()) {
					if ($stack->getCount() === 0) {
						$player->getInventory()->setItemInHand($ev->getItem());
					} else {
						$player->getInventory()->setItemInHand($stack);
						$player->getInventory()->addItem($ev->getItem());
					}
				} else {
					$player->getInventory()->addItem($ev->getItem());
				}

				return true;
			} else {
				$player->getInventory()->sendContents($player);
			}
		}

		return false;
	}
}
