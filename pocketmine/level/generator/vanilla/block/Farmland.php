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

use pocketmine\level\generator\vanilla\entity\Entity;
use pocketmine\level\generator\vanilla\entity\Living;
use pocketmine\level\generator\vanilla\event\entity\EntityTrampleFarmlandEvent;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\item\ItemFactory;
use pocketmine\level\generator\vanilla\item\ItemIds;
use pocketmine\level\generator\vanilla\math\AxisAlignedBB;
use pocketmine\level\generator\vanilla\math\Facing;
use pocketmine\level\generator\vanilla\utils\Utils;

class Farmland extends Transparent
{

	protected $id = self::FARMLAND;

	public function __construct(int $meta = 0)
	{
		$this->meta = $meta;
	}

	public function getName() : string
	{
		return "Farmland";
	}

	public function getHardness() : float
	{
		return 0.6;
	}

	public function getToolType() : int
	{
		return BlockToolType::TYPE_SHOVEL;
	}

	protected function recalculateBoundingBox() : ?AxisAlignedBB
	{
		return new AxisAlignedBB(
			$this->x,
			$this->y,
			$this->z,
			$this->x + 1,
			$this->y + 1, //TODO: this should be 0.9375, but MCPE currently treats them as a full block (https://bugs.mojang.com/browse/MCPE-12109)
			$this->z + 1
		);
	}

	public function onNearbyBlockChange() : void{
		if($this->getSide(Facing::UP)->isSolid()){
			$this->level->setBlock($this, BlockFactory::get(BlockIds::DIRT));
		}
	}

	public function getMoisture() : int{
		return $this->meta;
	}

	public function ticksRandomly() : bool{
		return true;
	}

	public function onRandomTick() : void{
		$moisture = $this->getMoisture();
		if (!$this->hasWater()) { //TODO: check rain
			if ($moisture > 0) {
				$this->meta = $moisture - 1;
				$this->level->setBlock($this, $this);
			} elseif (!$this->hasCrops()) {
				$this->level->setBlock($this, BlockFactory::get(BlockIds::DIRT));
			}
		} elseif ($moisture < 7) {
			$this->meta = 7;
			$this->level->setBlock($this, $this);
		}
	}

	public function onEntityFallenUpon(Entity $entity, float $fallDistance) : void{
		if($entity instanceof Living && Utils::getRandomFloat() < $fallDistance - 0.5){
			$ev = new EntityTrampleFarmlandEvent($entity, $this);
			$ev->call();
			if(!$ev->isCancelled()){
				$this->level->setBlock($this, BlockFactory::get(BlockIds::DIRT));
			}
		}
	}

	protected function hasWater() : bool {
		for ($dx = -4; $dx <= 4; $dx++) {
			for ($dy = 0; $dy <= 1; $dy++) {
				for ($dz = -4; $dz <= 4; $dz++) {
					$checkPos = $this->add($dx, $dy, $dz);
					$block = $this->level->getBlock($checkPos);
					if ($block instanceof Water) {
						return true;
					}
				}
			}
		}

		return false;
	}

	protected function hasCrops() : bool {
		return $this->getSide(Facing::DOWN) instanceof Crops;
	}

	public function getDropsForCompatibleTool(Item $item) : array{
		return [
			ItemFactory::get(ItemIds::DIRT)
		];
	}

	public function getPickedItem(bool $addUserData = false) : Item{
		return ItemFactory::get(ItemIds::DIRT);
	}
}
