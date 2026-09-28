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
use pocketmine\level\generator\vanilla\block\utils\FortuneDropHelper;
use pocketmine\level\generator\vanilla\block\utils\StaticSupportTrait;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\math\Facing;

use function mt_rand;

class NetherWartPlant extends Flowable
{
	use StaticSupportTrait;

	public const MAX_AGE = 3;

	protected $id = Block::NETHER_WART_PLANT;

	protected $itemId = Item::NETHER_WART;

	public function __construct(int $meta = 0)
	{
		$this->meta = $meta;
	}

	public function getName() : string
	{
		return "Nether Wart";
	}

	protected function canBeSupportedAt(Block $block) : bool{
		return $block->getSide(Facing::DOWN)->getId() === BlockIds::SOUL_SAND;
	}

	public function ticksRandomly() : bool{
		return $this->meta < self::MAX_AGE;
	}

	public function onRandomTick() : void{
		if($this->meta < self::MAX_AGE && mt_rand(0, 10) === 0){ //Still growing
			$block = clone $this;
			$block->meta++;
			BlockEventHelper::grow($this, $block, null);
		}
	}

	public function getDropsForCompatibleTool(Item $item) : array{
		return [
			$this->asItem()->setCount($this->meta === self::MAX_AGE ? FortuneDropHelper::discrete($item, 2, 4) : 1)
		];
	}
}
