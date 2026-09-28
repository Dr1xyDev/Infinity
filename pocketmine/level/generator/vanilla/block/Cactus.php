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
use pocketmine\level\generator\vanilla\block\utils\StaticSupportTrait;
use pocketmine\level\generator\vanilla\entity\Entity;
use pocketmine\level\generator\vanilla\event\entity\EntityDamageByBlockEvent;
use pocketmine\level\generator\vanilla\event\entity\EntityDamageEvent;
use pocketmine\level\generator\vanilla\math\AxisAlignedBB;
use pocketmine\level\generator\vanilla\math\Facing;
use function mt_rand;

class Cactus extends Transparent
{
	use StaticSupportTrait;

	public const int MAX_AGE = 15;
	public const int MAX_HEIGHT = 3;

	protected $id = self::CACTUS;

	public function __construct(int $meta = 0)
	{
		$this->meta = $meta;
	}

	public function getHardness() : float
	{
		return 0.4;
	}

	public function hasEntityCollision() : bool
	{
		return true;
	}

	public function getName() : string
	{
		return "Cactus";
	}

	public function getVariantBitmask() : int
	{
		return 0;
	}

	protected function recalculateBoundingBox() : ?AxisAlignedBB
	{

		return new AxisAlignedBB(
			$this->x + 0.0625,
			$this->y + 0.0625,
			$this->z + 0.0625,
			$this->x + 0.9375,
			$this->y + 0.9375,
			$this->z + 0.9375
		);
	}

	public function onEntityCollide(Entity $entity) : void
	{
		$ev = new EntityDamageByBlockEvent($this, $entity, EntityDamageEvent::CAUSE_CONTACT, 1);
		$entity->attack($ev);
	}

	protected function canBeSupportedAt(Block $block) : bool{
		$supportBlock = $block->getSide(Facing::DOWN);
		if(!$supportBlock->isSameType($this) && !$supportBlock instanceof Sand){
			return false;
		}
		foreach(Facing::HORIZONTAL as $side){
			if($block->getSide($side)->isSolid()){
				return false;
			}
		}

		return true;
	}

	public function ticksRandomly() : bool
	{
		return true;
	}

	public function onRandomTick() : void
	{
		$up = $this->getSide(Facing::UP);
		if($up->getId() !== BlockIds::AIR){
			return;
		}

		$level = $this->level;

		if(!$level->isInWorld($up->x, $up->y, $up->z)){
			return;
		}

		$height = 1;
		while($height < self::MAX_HEIGHT && $this->getSide(Facing::DOWN, $height)->isSameType($this)){
			$height++;
		}

		if($this->meta === 9){
			$canGrowFlower = true;
			foreach(Facing::HORIZONTAL as $side){
				if($up->getSide($side)->isSolid()){
					$canGrowFlower = false;
					break;
				}
			}

			if($canGrowFlower){
				$chance = $height >= self::MAX_HEIGHT ? 25 : 10;
				if(mt_rand(1, 100) <= $chance){
					if(BlockEventHelper::grow($up, BlockFactory::get(BlockIds::CACTUS_FLOWER), null)){
						$this->meta = 0;
						$level->setBlock($this, $this, update: false);
					}
					return;
				}
			}
		}

		if($this->meta === self::MAX_AGE){
			$this->meta = 0;

			if($height < self::MAX_HEIGHT){
				BlockEventHelper::grow($up, BlockFactory::get(BlockIds::CACTUS), null);
			}
		}else{
			++$this->meta;
		}
		$level->setBlock($this, $this, update: false);
	}
}
