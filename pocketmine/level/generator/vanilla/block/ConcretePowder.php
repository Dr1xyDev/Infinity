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
use pocketmine\level\generator\vanilla\block\utils\ColorBlockMetaHelper;
use pocketmine\level\generator\vanilla\math\Facing;

class ConcretePowder extends Fallable
{
	protected $id = self::CONCRETE_POWDER;

	public function __construct(int $meta = 0)
	{
		$this->meta = $meta;
	}

	public function getName() : string
	{
		return ColorBlockMetaHelper::getColorFromMeta($this->getVariant()) . " Concrete Powder";
	}

	public function getHardness() : float{
		return 0.5;
	}

	public function getToolType() : int{
		return BlockToolType::TYPE_SHOVEL;
	}

	public function onNearbyBlockChange() : void{
		if(($water = $this->getAdjacentWater()) !== null){
			BlockEventHelper::form($this, BlockFactory::get(BlockIds::CONCRETE, $this->meta), $water);
		}else{
			parent::onNearbyBlockChange();
		}
	}

	public function tickFalling() : ?Block{
		if($this->getAdjacentWater() === null){
			return null;
		}
		return BlockFactory::get(BlockIds::CONCRETE, $this->meta);
	}

	private function getAdjacentWater() : ?Water{
		foreach(Facing::ALL as $i){
			if($i === Facing::DOWN){
				continue;
			}
			$block = $this->getSide($i);
			if($block instanceof Water){
				return $block;
			}
		}

		return null;
	}
}
