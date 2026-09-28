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
use pocketmine\level\generator\vanilla\item\enchantment\Enchantment;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\Player;

class Ice extends Transparent
{
	protected $id = self::ICE;

	public function __construct(int $meta = 0)
	{
		$this->meta = $meta;
	}

	public function getName() : string
	{
		return "Ice";
	}

	public function getHardness() : float
	{
		return 0.5;
	}

	public function getLightFilter() : int
	{
		return 2;
	}

	public function getFrictionFactor() : float
	{
		return 0.98;
	}

	public function getToolType() : int
	{
		return BlockToolType::TYPE_PICKAXE;
	}

	public function onBreak(Item $item, ?Player $player = null) : bool
	{
		if (($player === null || $player->isSurvival()) && !$item->hasEnchantment(Enchantment::SILK_TOUCH)) {
			$this->getLevel()->setBlock($this, BlockFactory::get(Block::WATER), true);
			return true;
		}
		return parent::onBreak($item, $player);
	}

	public function ticksRandomly() : bool
	{
		return true;
	}

	public function onRandomTick() : void{
		$level = $this->level;
		if($level->getHighestAdjacentBlockLight($this->x, $this->y, $this->z) >= 12){
			BlockEventHelper::melt($this, BlockFactory::get(BlockIds::WATER));
		}
	}

	public function getDropsForCompatibleTool(Item $item) : array
	{
		return [];
	}

	public function isAffectedBySilkTouch() : bool
	{
		return true;
	}
}
