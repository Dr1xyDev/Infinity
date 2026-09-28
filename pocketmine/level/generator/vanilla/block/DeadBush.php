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

use pocketmine\level\generator\vanilla\block\utils\BushTrait;
use pocketmine\level\generator\vanilla\block\utils\StaticSupportTrait;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\item\ItemFactory;
use pocketmine\level\generator\vanilla\item\ItemIds;
use pocketmine\level\generator\vanilla\math\Facing;
use function mt_rand;

class DeadBush extends Flowable {
	use BushTrait;
	use StaticSupportTrait;

	protected $id = self::DEAD_BUSH;

	public function __construct(int $meta = 0)
	{
		$this->meta = $meta;
	}

	public function getName() : string
	{
		return "Dead Bush";
	}

	public function canBeReplaced() : bool{
		return false;
	}

	public function getDropsForIncompatibleTool(Item $item) : array
	{
		return [
			ItemFactory::get(ItemIds::STICK)->setCount(mt_rand(0, 2))
		];
	}

	public function isAffectedBySilkTouch() : bool
	{
		return true;
	}

	protected function canBeSupportedAt(Block $block) : bool{
		$supportBlock = $block->getSide(Facing::DOWN);
		return
			$supportBlock instanceof Sand ||
			$supportBlock instanceof Mud ||
			match($supportBlock->getId()){
				//can't use DIRT tag here because it includes farmland
				BlockIds::PODZOL,
				BlockIds::MYCELIUM,
				BlockIds::DIRT,
				BlockIds::GRASS,
				BlockIds::HARDENED_CLAY,
				BlockIds::MOSS_BLOCK,
				BlockIds::STAINED_CLAY => true,
				default => false,
			};
	}
}
