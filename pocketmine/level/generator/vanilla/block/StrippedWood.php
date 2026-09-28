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

use pocketmine\level\generator\vanilla\block\utils\PillarRotationHelper;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\Player;

class StrippedWood extends Wood {
	public function getName() : string{
		return $this->fallbackName;
	}

	public function getVariantBitmask() : int{
		return 0;
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		$this->meta = PillarRotationHelper::getMetaFromFace($this->meta, $face, true);
		return Block::place($item, $blockReplace, $blockClicked, $face, $clickVector, $player);
	}

	public function getFlameEncouragement() : int{
		if ($this->id === BlockIds::STRIPPED_CRIMSON_HYPHAE || $this->id === BlockIds::STRIPPED_WARPED_HYPHAE){
			return 0;
		}

		return parent::getFlameEncouragement();
	}

	public function getFlammability() : int{
		if ($this->id === BlockIds::STRIPPED_CRIMSON_HYPHAE || $this->id === BlockIds::STRIPPED_WARPED_HYPHAE){
			return 0;
		}

		return parent::getFlammability();
	}
}
