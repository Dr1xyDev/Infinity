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
use pocketmine\level\generator\vanilla\block\Growable;
use pocketmine\level\generator\vanilla\level\particle\BoneMealParticle;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\Player;

class Fertilizer extends Item {

	public function onActivate(Player $player, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector) : bool{
		if ($blockClicked instanceof Growable) {
			$random = $player->level->random;
			if ($blockClicked->canGrow($random, $player)) {
				if ($blockClicked->canUseBonemeal($random, $player)) {
					$blockClicked->grow($random, $player);
				}

				$this->pop();
				$player->level->addParticle(new BoneMealParticle($blockClicked));
				return true;
			}
		}

		return parent::onActivate($player, $blockReplace, $blockClicked, $face, $clickVector);
	}
}
