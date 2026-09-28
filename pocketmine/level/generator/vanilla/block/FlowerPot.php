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

use pocketmine\level\generator\vanilla\block\utils\StaticSupportTrait;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\item\ItemFactory;
use pocketmine\level\generator\vanilla\item\ItemIds;
use pocketmine\level\generator\vanilla\math\AxisAlignedBB;
use pocketmine\level\generator\vanilla\math\Facing;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\Player;
use pocketmine\level\generator\vanilla\tile\FlowerPot as TileFlowerPot;
use pocketmine\level\generator\vanilla\tile\Tile;

class FlowerPot extends Flowable
{
	use StaticSupportTrait;

	public const STATE_EMPTY = 0;
	public const STATE_FULL = 1;

	protected $id = self::FLOWER_POT_BLOCK;
	protected $itemId = Item::FLOWER_POT;

	public function __construct(int $meta = 0)
	{
		$this->meta = $meta;
	}

	public function getName() : string
	{
		return "Flower Pot";
	}

	protected function recalculateBoundingBox() : ?AxisAlignedBB
	{
		return new AxisAlignedBB(
			$this->x + 0.3125,
			$this->y,
			$this->z + 0.3125,
			$this->x + 0.6875,
			$this->y + 0.375,
			$this->z + 0.6875
		);
	}

	private function isValidPlant(Block $block) : bool{
		return
			$block instanceof BrownMushroom ||
			$block instanceof Cactus ||
			$block instanceof DeadBush ||
			$block instanceof Flower ||
			$block instanceof RedMushroom ||
			($block instanceof TallGrass && $block->getDamage() === TallGrass::TYPE_FERN) ||
			$block instanceof Sapling; //TODO: bomboo, wither rose, nether roots,
	}

	protected function canBeSupportedAt(Block $block) : bool{
		return !$this->getSide(Facing::DOWN)->isTransparent();
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		Tile::createTile(Tile::FLOWER_POT, $this->getLevel(), TileFlowerPot::createNBT($this, $face, $item, $player));
		return parent::place($item, $blockReplace, $blockClicked, $face, $clickVector, $player);
	}

	public function onActivate(Item $item, ?Player $player = null) : bool{
		$level = $this->level;
		$tile = $level->getTile($this);
		if (!($tile instanceof TileFlowerPot)) {
			return false;
		}

		$tilePlant = $tile->getItem();
		$plant = $item->getBlock();
		if($tilePlant->getId() !== ItemIds::AIR){
			if($this->isValidPlant($plant)){
				//for some reason, vanilla doesn't remove the contents of the pot if the held item is plantable
				//and will also cause a new plant to be placed if clicking on the side
				return false;
			}

			$removedItems = [$tilePlant];
			if($player !== null){
				//this one just has to be a weirdo :(
				//this is the only block that directly adds items to the player inventory instead of just dropping items
				$removedItems = $player->getInventory()->addItem(...$removedItems);
			}
			foreach($removedItems as $drops){
				$level->dropItem($this->add(0.5, 0.5, 0.5), $drops);
			}

			$this->meta = self::STATE_EMPTY;
			$tile->setItem(ItemFactory::air());
			$level->setBlock($this, $this);
			return true;
		}elseif($this->isValidPlant($plant)){
			$this->meta = self::STATE_FULL;
			$tile->setItem($item->pop());
			$level->setBlock($this, $this);

			return true;
		}

		return false;
	}

	public function getVariantBitmask() : int
	{
		return 0;
	}

	public function getDropsForCompatibleTool(Item $item) : array
	{
		$items = parent::getDropsForCompatibleTool($item);

		$tile = $this->getLevel()->getTile($this);
		if ($tile instanceof TileFlowerPot) {
			$item = $tile->getItem();
			if ($item->getId() !== Item::AIR) {
				$items[] = $item;
			}
		}

		return $items;
	}

	public function getPickedItem(bool $addUserData = false) : Item
	{
		$plant = null;

		$tile = $this->getLevel()->getTile($this);
		if ($tile instanceof TileFlowerPot) {
			$item = $tile->getItem();
			if ($item->getId() !== ItemIds::AIR) {
				$plant = $item;
			}
		}

		return $plant !== null ? $plant : parent::getPickedItem($addUserData);
	}
}
