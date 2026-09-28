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

namespace pocketmine\level\generator\vanilla\block;

use pocketmine\level\generator\vanilla\block\utils\BlockEventHelper;
use pocketmine\level\generator\vanilla\level\generator\feature\FeatureFactory;
use pocketmine\level\generator\vanilla\level\generator\feature\FeaturePlaceContext;
use pocketmine\level\generator\vanilla\level\generator\feature\TreeFeatures;
use pocketmine\Player;
use pocketmine\level\generator\vanilla\utils\Random;

class BrownMushroom extends RedMushroom
{
	protected $id = self::BROWN_MUSHROOM;

	public function getName() : string
	{
		return "Brown Mushroom";
	}

	public function getLightLevel() : int
	{
		return 1;
	}

	public function grow(Random $random, ?Player $player) : void{
		$feature = FeatureFactory::getInstance()->get(TreeFeatures::HUGE_BROWN_MUSHROOM);
		if ($feature !== null && BlockEventHelper::grow($this, BlockFactory::get(BlockIds::AIR), $player)) {
			$feature->place(new FeaturePlaceContext($this->level, $random, $this));
		}
	}
}
