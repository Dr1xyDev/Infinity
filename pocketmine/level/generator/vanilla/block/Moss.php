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

use pocketmine\level\generator\vanilla\item\Fertilizer;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\level\particle\BoneMealParticle;
use pocketmine\level\generator\vanilla\math\Facing;
use pocketmine\Player;

use function mt_rand;

class Moss extends Solid
{
	protected $id = self::MOSS_BLOCK;

	public function __construct(int $meta = 0)
	{
		$this->meta = $meta;
	}

	public function getHardness() : float
	{
		return 0.1;
	}

	public function getBlastResistance() : float
	{
		return 2.5;
	}

	public function getToolType() : int
	{
		return BlockToolType::TYPE_HOE;
	}

	public function getName() : string
	{
		return "Moss Block";
	}

	public function onActivate(Item $item, ?Player $player = null) : bool
	{
		$up = $this->getSide(Facing::UP);
		if (!($item instanceof Fertilizer) || $up->getId() != self::AIR) {
			return false;
		}

		$random = mt_rand(0, 13);
		$block2 = null;
		if ($random < 5) {
			$block = BlockFactory::get(BlockIds::TALL_GRASS);
		} elseif ($random < 8) {
			$block = BlockFactory::get(BlockIds::MOSS_CARPET);
		} elseif ($random < 9) {
			if ($this->getSide(Facing::UP, 2)->getId() != self::AIR) {
				return false;
			}

			$block = BlockFactory::get(BlockIds::DOUBLE_PLANT, DoublePlant::TYPE_DOUBLE_TALLGRASS);
			$block2 = BlockFactory::get(BlockIds::DOUBLE_PLANT, DoublePlant::TYPE_DOUBLE_TALLGRASS | DoublePlant::BITFLAG_TOP);
		} elseif ($random < 11) {
			$block = BlockFactory::get(BlockIds::AZALEA);
		} else {
			$block = BlockFactory::get(BlockIds::FLOWERING_AZALEA);
		}

		$this->getLevel()->setBlock($up, $block, false, true);
		if ($block2 != null) {
			$this->getLevel()->setBlock($this->getSide(Facing::UP, 2), $block2, false, true);
		}

		$this->level->addParticle(new BoneMealParticle($this));

		$item->count--;
		return true;
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
