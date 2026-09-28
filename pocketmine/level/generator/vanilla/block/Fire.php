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
use pocketmine\level\generator\vanilla\event\block\BlockBurnEvent;
use pocketmine\level\generator\vanilla\level\format\Chunk;
use pocketmine\level\generator\vanilla\level\Level;
use pocketmine\level\generator\vanilla\math\Facing;

use function intdiv;
use function max;
use function min;
use function mt_rand;

class Fire extends BaseFire
{
	public const MAX_AGE = 15;

	protected $id = self::FIRE;

	public function __construct(int $meta = 0){
		$this->meta = $meta;
	}

	public function getName() : string{
		return "Fire Block";
	}

	public function getLightLevel() : int{
		return 15;
	}

	protected function getFireDamage() : int {
		return 1;
	}

	private function canBeSupportedBy(Block $block) : bool{
		return $block->isSolid();
	}

	public function onNearbyBlockChange() : void{
		$level = $this->level;
		$down = $this->getSide(Facing::DOWN);
		if(SoulFire::canBeSupportedBy($down)){
			$level->setBlock($this, BlockFactory::get(BlockIds::SOUL_FIRE));
		}elseif(!$this->canBeSupportedBy($this->getSide(Facing::DOWN)) && !$this->hasAdjacentFlammableBlocks()){
			$level->setBlock($this, BlockFactory::get(BlockIds::AIR));
		}else{
			$level->scheduleDelayedBlockUpdate($this, mt_rand(30, 40));
		}
	}

	public function ticksRandomly() : bool{
		return true;
	}

	public function onRandomTick() : void{
		$down = $this->getSide(Facing::DOWN);

		$result = null;
		if($this->meta < self::MAX_AGE && mt_rand(0, 2) === 0){
			$this->meta++;
			$result = $this;
		}
		$canSpread = true;

		if(!$down->burnsForever()){
			//TODO: check rain
			if($this->meta === self::MAX_AGE){
				if(!$down->isFlammable() && mt_rand(0, 3) === 3){ //1/4 chance to extinguish
					$canSpread = false;
					$result = BlockFactory::get(BlockIds::AIR);
				}
			}elseif(!$this->hasAdjacentFlammableBlocks()){
				$canSpread = false;
				if($down->isTransparent() || $this->meta > 3){
					$result = BlockFactory::get(BlockIds::AIR);
				}
			}
		}

		$level = $this->level;
		if($result !== null){
			$level->setBlock($this, $result);
		}

		$level->scheduleDelayedBlockUpdate($this, mt_rand(30, 40));

		if($canSpread){
			$this->burnBlocksAround();
			$this->spreadFire();
		}
	}

	public function onScheduledUpdate() : void{
		$this->onRandomTick();
	}

	private function hasAdjacentFlammableBlocks() : bool{
		foreach(Facing::ALL as $face){
			if($this->getSide($face)->isFlammable()){
				return true;
			}
		}

		return false;
	}

	private function burnBlocksAround() : void{
		//TODO: raise upper bound for chance in humid biomes

		foreach($this->getHorizontalSides() as $side){
			$this->burnBlock($side, 300);
		}

		//vanilla uses a 250 upper bound here, but I don't think they intended to increase the chance of incineration
		$this->burnBlock($this->getSide(Facing::UP), 350);
		$this->burnBlock($this->getSide(Facing::DOWN), 350);
	}

	private function burnBlock(Block $block, int $chanceBound) : void{
		if(mt_rand(0, $chanceBound) < $block->getFlammability()){
			$ev = new BlockBurnEvent($block, $this);
			$ev->call();
			if(!$ev->isCancelled()){
				$block->onIncinerate();

				$level = $this->level;
				if($level->getBlock($block)->isSameState($block)){
					$spreadedFire = false;
					if(mt_rand(0, $this->meta + 9) < 5){ //TODO: check rain
						$fire = clone $this;
						$fire->meta = min(self::MAX_AGE, $fire->meta + (mt_rand(0, 4) >> 2));
						$spreadedFire = $this->spreadBlock($block, $fire);
					}
					if(!$spreadedFire){
						$level->setBlock($block, BlockFactory::get(BlockIds::AIR));
					}
				}
			}
		}
	}

	private function spreadFire() : void{
		$level = $this->level;
		$difficultyChanceIncrease = $level->getDifficulty() * 7;
		$ageDivisor = $this->meta + 30;

		for($y = -1; $y <= 4; ++$y){
			$targetY = $y + (int) $this->y;
			if($targetY < Level::Y_MIN || $targetY >= Level::Y_MAX){
				continue;
			}
			//Higher blocks have a lower chance of catching fire
			$randomBound = 100 + ($y > 1 ? ($y - 1) * 100 : 0);

			for($z = -1; $z <= 1; ++$z){
				$targetZ = $z + (int) $this->z;
				for($x = -1; $x <= 1; ++$x){
					if($x === 0 && $y === 0 && $z === 0){
						continue;
					}
					$targetX = $x + (int) $this->x;
					if(!$level->isInWorld($targetX, $targetY, $targetZ)){
						continue;
					}

					if(!$level->isChunkLoaded($targetX >> Chunk::COORD_BIT_SIZE, $targetZ >> Chunk::COORD_BIT_SIZE)){
						continue;
					}
					$block = $level->getBlockAt($targetX, $targetY, $targetZ);
					if($block->getId() !== BlockIds::AIR){
						continue;
					}

					//TODO: fire can't spread if it's raining in any horizontally adjacent block, or the current one

					$encouragement = 0;
					foreach($block->sides() as $vector3){
						if($level->isInWorld($vector3->x, $vector3->y, $vector3->z)){
							$encouragement = max($encouragement, $level->getBlockAt($vector3->x, $vector3->y, $vector3->z)->getFlameEncouragement());
						}
					}

					if($encouragement <= 0){
						continue;
					}

					$maxChance = intdiv($encouragement + 40 + $difficultyChanceIncrease, $ageDivisor);
					//TODO: max chance is lowered by half in humid biomes

					if($maxChance > 0 && mt_rand(0, $randomBound - 1) <= $maxChance){
						$new = clone $this;
						$new->meta = min(self::MAX_AGE, $this->meta + (mt_rand(0, 4) >> 2));
						$this->spreadBlock($block, $new);
					}
				}
			}
		}
	}

	private function spreadBlock(Block $block, Block $newState) : bool{
		return BlockEventHelper::spread($block, $newState, $this);
	}
}
