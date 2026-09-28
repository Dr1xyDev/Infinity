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
use pocketmine\level\generator\vanilla\entity\Entity;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\item\ItemFactory;
use pocketmine\level\generator\vanilla\item\TieredTool;
use pocketmine\level\generator\vanilla\math\Facing;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\Player;

class SnowLayer extends Flowable
{
	protected $id = self::SNOW_LAYER;

	public function __construct(int $meta = 0)
	{
		$this->meta = $meta;
	}

	public function getName() : string
	{
		return "Snow Layer";
	}

	public function getVariantBitmask() : int
	{
		return 0b111;
	}

	public function canBeReplaced() : bool
	{
		return $this->meta < 7; //8 snow layers
	}

	public function getHardness() : float
	{
		return 0.1;
	}

	public function getToolType() : int
	{
		return BlockToolType::TYPE_SHOVEL;
	}

	public function getToolHarvestLevel() : int
	{
		return TieredTool::TIER_WOODEN;
	}

	private function canBeSupportedBy(Block $b) : bool
	{
		return $b->isSolid() || ($b->getId() === $this->getId() && $b->getDamage() === 7);
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool
	{
		if ($blockReplace->getId() === $this->getId() && $blockReplace->getDamage() < 7) {
			$this->setDamage($blockReplace->getDamage() + 1);
		}
		if ($this->canBeSupportedBy($blockReplace->getSide(Facing::DOWN))) {
			$this->getLevel()->setBlock($blockReplace, $this, true);

			return true;
		}

		return false;
	}

	public function onNearbyBlockChange() : void
	{
		$vec3 = $this->asVector3();
		if (!$this->canBeSupportedBy($this->getSide(Facing::DOWN))) {
			$this->getLevel()->setBlock($this, BlockFactory::get(Block::AIR), false, false);
			$nbt = Entity::createBaseNBT($vec3->add(0.5, 0, 0.5));
			$nbt->setInt("TileID", $this->getId());
			$nbt->setByte("Data", $this->getDamage());

			$fall = Entity::createEntity("FallingSand", $this->getLevel(), $nbt);

			if ($fall !== null) {
				$fall->spawnToAll();
			}
		}
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
		return [
			ItemFactory::get(Item::SNOWBALL) //TODO: check layer count
		];
	}

	public function isAffectedBySilkTouch() : bool
	{
		return false;
	}
}
