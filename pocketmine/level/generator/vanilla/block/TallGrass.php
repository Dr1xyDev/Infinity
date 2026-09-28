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
use pocketmine\level\generator\vanilla\block\utils\BushTrait;
use pocketmine\level\generator\vanilla\block\utils\FortuneDropHelper;
use pocketmine\level\generator\vanilla\block\utils\StaticSupportTrait;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\item\ItemFactory;
use pocketmine\level\generator\vanilla\item\ItemIds;
use pocketmine\level\generator\vanilla\math\Facing;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\Player;
use pocketmine\level\generator\vanilla\utils\Random;

class TallGrass extends Flowable implements Growable
{
	use BushTrait;
	use StaticSupportTrait;

	public const TYPE_DEAD_SHRUB = 0;
	public const TYPE_TALL_GRASS = 1;
	public const TYPE_FERN = 2;

	protected $id = self::TALL_GRASS;

	public function __construct(int $meta = 1){
		$this->meta = $meta;
	}

	public function getName() : string
	{
		static $names = [
			self::TYPE_DEAD_SHRUB => "Dead Shrub",
			self::TYPE_TALL_GRASS => "Tall Grass",
			self::TYPE_FERN => "Fern"
		];
		return $names[$this->getVariant()] ?? "Unknown";
	}

	protected function canBeSupportedAt(Block $block) : bool{
		$supportBlock = $block->getSide(Facing::DOWN);
		return
			$supportBlock instanceof Grass ||
			$supportBlock instanceof Mycelium ||
			$supportBlock instanceof Podzol ||
			$supportBlock instanceof Dirt ||
			$supportBlock instanceof DirtWithRoots ||
			$supportBlock instanceof Farmland ||
			$supportBlock instanceof Mud ||
			$supportBlock instanceof Moss ||
			$supportBlock instanceof PaleMoss; //TODO: MUDDY_MANGROVE_ROOTS
	}

	public function canGrow(Random $random, ?Player $player) : bool{
		return $this->getVariant() !== self::DEAD_BUSH;
	}

	public function canUseBonemeal(Random $random, ?Player $player) : bool{
		return true;
	}

	public function grow(Random $random, ?Player $player) : void{
		if (BlockEventHelper::grow($this, $this, $player)) {
			$doublePlantType = DoublePlant::TYPE_DOUBLE_TALLGRASS;
			if ($this->getVariant() === self::TYPE_FERN) {
				$doublePlantType = DoublePlant::TYPE_LARGE_FERN;
			}

			$doublePlant = BlockFactory::get(BlockIds::DOUBLE_PLANT, $doublePlantType, $this);
			$doublePlant->place(ItemFactory::air(), $this, $this->getSide(Facing::DOWN), Facing::DOWN, Vector3::zero());
		}
	}

	public function getDropsForIncompatibleTool(Item $item) : array{
		if (FortuneDropHelper::bonusChanceDivisor($item, 8, 2)) {
			return [
				ItemFactory::get(ItemIds::WHEAT_SEEDS)
			];
		}

		return [];
	}
}
