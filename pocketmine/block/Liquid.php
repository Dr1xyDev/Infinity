<?php
/*    
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█    
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░    
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.1
 *               InfinityProject By @Dr1xyDev    
 *   YT:         @Dr1xyDev    
 *   GitHub:     github.com/Dr1xyDev/Infinity    
*/

namespace pocketmine\block;

use pocketmine\entity\Entity;
use pocketmine\item\Item;
use pocketmine\level\Level;
use pocketmine\level\particle\SmokeParticle;
use pocketmine\level\sound\FizzSound;
use pocketmine\math\Vector3;

abstract class Liquid extends Transparent{

	
	private $temporalVector = null;

	public $adjacentSources = 0;
	public $isOptimalFlowDirection = [0, 0, 0, 0];
	public $flowCost = [0, 0, 0, 0];

	public function hasEntityCollision(){ return true; }
	public function isBreakable(Item $item){ return false; }
	public function canBeReplaced(){ return true; }
	public function isSolid(){ return false; }
	public function getHardness(){ return 100; }
	public function getBoundingBox(){ return null; }
	public function getDrops(Item $item) : array{ return []; }

	public function getFluidHeightPercent(){
		$d = $this->meta;
		if($d >= 8) $d = 0;
		return ($d + 1) / 9;
	}

	protected function getFlowDecay(Vector3 $pos){
		if(!($pos instanceof Block)){
			$pos = $this->getLevel()->getBlock($pos);
		}
		if($pos->getId() !== $this->getId()) return -1;
		return $pos->getDamage();
	}

	protected function getEffectiveFlowDecay(Vector3 $pos){
		if(!($pos instanceof Block)){
			$pos = $this->getLevel()->getBlock($pos);
		}
		if($pos->getId() !== $this->getId()) return -1;
		$decay = $pos->getDamage();
		if($decay >= 8) $decay = 0;
		return $decay;
	}

	public function getFlowVector(){
		$vector = new Vector3(0, 0, 0);
		if($this->temporalVector === null) $this->temporalVector = new Vector3(0, 0, 0);

		$decay = $this->getEffectiveFlowDecay($this);

		for($j = 0; $j < 4; ++$j){
			$x = $this->x; $y = $this->y; $z = $this->z;
			if($j === 0) --$x;
			elseif($j === 1) ++$x;
			elseif($j === 2) --$z;
			elseif($j === 3) ++$z;

			$sideBlock  = $this->getLevel()->getBlock($this->temporalVector->setComponents($x, $y, $z));
			$blockDecay = $this->getEffectiveFlowDecay($sideBlock);

			if($blockDecay < 0){
				if(!$sideBlock->canBeFlowedInto()) continue;
				$blockDecay = $this->getEffectiveFlowDecay(
					$this->getLevel()->getBlock($this->temporalVector->setComponents($x, $y - 1, $z)));
				if($blockDecay >= 0){
					$realDecay = $blockDecay - ($decay - 8);
					$vector->x += ($sideBlock->x - $this->x) * $realDecay;
					$vector->y += ($sideBlock->y - $this->y) * $realDecay;
					$vector->z += ($sideBlock->z - $this->z) * $realDecay;
				}
				continue;
			}
			$realDecay = $blockDecay - $decay;
			$vector->x += ($sideBlock->x - $this->x) * $realDecay;
			$vector->y += ($sideBlock->y - $this->y) * $realDecay;
			$vector->z += ($sideBlock->z - $this->z) * $realDecay;
		}

		if($this->getDamage() >= 8){
			$falling = false;
			if(!$this->getLevel()->getBlock($this->temporalVector->setComponents($this->x,     $this->y, $this->z - 1))->canBeFlowedInto()) $falling = true;
			elseif(!$this->getLevel()->getBlock($this->temporalVector->setComponents($this->x,     $this->y, $this->z + 1))->canBeFlowedInto()) $falling = true;
			elseif(!$this->getLevel()->getBlock($this->temporalVector->setComponents($this->x - 1, $this->y, $this->z    ))->canBeFlowedInto()) $falling = true;
			elseif(!$this->getLevel()->getBlock($this->temporalVector->setComponents($this->x + 1, $this->y, $this->z    ))->canBeFlowedInto()) $falling = true;
			elseif(!$this->getLevel()->getBlock($this->temporalVector->setComponents($this->x,     $this->y + 1, $this->z - 1))->canBeFlowedInto()) $falling = true;
			elseif(!$this->getLevel()->getBlock($this->temporalVector->setComponents($this->x,     $this->y + 1, $this->z + 1))->canBeFlowedInto()) $falling = true;
			elseif(!$this->getLevel()->getBlock($this->temporalVector->setComponents($this->x - 1, $this->y + 1, $this->z    ))->canBeFlowedInto()) $falling = true;
			elseif(!$this->getLevel()->getBlock($this->temporalVector->setComponents($this->x + 1, $this->y + 1, $this->z    ))->canBeFlowedInto()) $falling = true;

			if($falling) $vector = $vector->normalize()->add(0, -6, 0);
		}

		return $vector->normalize();
	}

	public function addVelocityToEntity(Entity $entity, Vector3 $vector){
		$flow = $this->getFlowVector();
		$vector->x += $flow->x;
		$vector->y += $flow->y;
		$vector->z += $flow->z;
	}

	
	public function tickRate() : int{
		if($this instanceof Water) return 2;
		if($this instanceof Lava)  return 30;
		return 0;
	}

	public function onUpdate($type){
		if($type === Level::BLOCK_UPDATE_NORMAL){
			$this->checkForHarden();
			$this->getLevel()->scheduleUpdate($this, $this->tickRate());
			return;
		}

		if($type !== Level::BLOCK_UPDATE_SCHEDULED) return;

		if($this->temporalVector === null) $this->temporalVector = new Vector3(0, 0, 0);

		$decay      = $this->getFlowDecay($this);
		$multiplier = $this instanceof Lava ? 2 : 1;

		
		if($decay > 0){
			$smallest          = -100;
			$this->adjacentSources = 0;

			$smallest = $this->getSmallestFlowDecay($this->level->getBlock($this->temporalVector->setComponents($this->x,     $this->y, $this->z - 1)), $smallest);
			$smallest = $this->getSmallestFlowDecay($this->level->getBlock($this->temporalVector->setComponents($this->x,     $this->y, $this->z + 1)), $smallest);
			$smallest = $this->getSmallestFlowDecay($this->level->getBlock($this->temporalVector->setComponents($this->x - 1, $this->y, $this->z    )), $smallest);
			$smallest = $this->getSmallestFlowDecay($this->level->getBlock($this->temporalVector->setComponents($this->x + 1, $this->y, $this->z    )), $smallest);

			$newDecay = $smallest + $multiplier;
			if($newDecay >= 8 or $smallest < 0) $newDecay = -1;

			
			$topDecay = $this->getFlowDecay(
				$this->level->getBlock($this->temporalVector->setComponents($this->x, $this->y + 1, $this->z)));
			if($topDecay >= 0){
				$newDecay = $topDecay >= 8 ? $topDecay : ($topDecay | 0x08);
			}

			
			if($this->adjacentSources >= 2 and $this instanceof Water){
				$below = $this->level->getBlock($this->temporalVector->setComponents($this->x, $this->y - 1, $this->z));
				if($below->isSolid() or ($below instanceof Water and $below->getDamage() === 0)){
					$newDecay = 0;
				}
			}

			
			if($this instanceof Lava and $decay < 8 and $newDecay < 8 and $newDecay > 1 and mt_rand(0, 4) !== 0){
				$newDecay = $decay;
			}

			if($newDecay !== $decay){
				if($newDecay < 0){
					
					$this->getLevel()->setBlock($this, new Air(), true);
					
					$this->scheduleNeighbors();
				}else{
					$this->getLevel()->setBlock($this, Block::get($this->id, $newDecay), true);
					$this->getLevel()->scheduleUpdate($this, $this->tickRate());
					$this->scheduleNeighbors();
				}
				$decay = $newDecay;
			}
		}

		
		$below = $this->level->getBlock($this->temporalVector->setComponents($this->x, $this->y - 1, $this->z));

		if($below->canBeFlowedInto() or ($below instanceof Liquid and !($below instanceof Water and $below->getDamage() === 0))){
			if($this instanceof Lava and $below instanceof Water){
				$this->getLevel()->setBlock($below, Block::get(Item::STONE), true);
				$this->triggerLavaMixEffects($below);
				return;
			}
			$this->flowIntoBlock($below, $decay >= 8 ? $decay : ($decay | 0x08));
		}elseif($decay >= 0 and ($decay === 0 or !$below->canBeFlowedInto())){
			
			$flags        = $this->getOptimalFlowDirections();
			$lateralDecay = ($decay >= 8) ? 1 : $decay + $multiplier;

			if($lateralDecay < 8){
				if($flags[0]) $this->flowIntoBlock($this->level->getBlock($this->temporalVector->setComponents($this->x - 1, $this->y, $this->z    )), $lateralDecay);
				if($flags[1]) $this->flowIntoBlock($this->level->getBlock($this->temporalVector->setComponents($this->x + 1, $this->y, $this->z    )), $lateralDecay);
				if($flags[2]) $this->flowIntoBlock($this->level->getBlock($this->temporalVector->setComponents($this->x,     $this->y, $this->z - 1)), $lateralDecay);
				if($flags[3]) $this->flowIntoBlock($this->level->getBlock($this->temporalVector->setComponents($this->x,     $this->y, $this->z + 1)), $lateralDecay);
			}
		}

		$this->checkForHarden();
	}

	
	private function scheduleNeighbors(){
		static $offsets = [[-1,0,0],[1,0,0],[0,0,-1],[0,0,1],[0,-1,0],[0,1,0]];
		foreach($offsets as [$dx, $dy, $dz]){
			$nb = $this->level->getBlock(
				$this->temporalVector->setComponents($this->x + $dx, $this->y + $dy, $this->z + $dz));
			if($nb instanceof Liquid){
				$this->level->scheduleUpdate($nb, $this->tickRate());
			}
		}
	}

	private function flowIntoBlock(Block $block, $newFlowDecay){
		if($block->canBeFlowedInto()){
			if($block instanceof Lava){
				$this->triggerLavaMixEffects($block);
			}elseif($block->getId() > 0){
				$this->getLevel()->useBreakOn($block);
			}
			$this->getLevel()->setBlock($block, Block::get($this->getId(), $newFlowDecay), true);
			$this->getLevel()->scheduleUpdate($block, $this->tickRate());
		}
	}

	private function calculateFlowCost(Block $block, $accumulatedCost, $previousDirection){
		$cost = 1000;

		for($j = 0; $j < 4; ++$j){
			
			if(($j === 0 and $previousDirection === 1) or
			   ($j === 1 and $previousDirection === 0) or
			   ($j === 2 and $previousDirection === 3) or
			   ($j === 3 and $previousDirection === 2)) continue;

			$x = $block->x; $y = $block->y; $z = $block->z;
			if($j === 0)     --$x;
			elseif($j === 1) ++$x;
			elseif($j === 2) --$z;
			elseif($j === 3) ++$z;

			$side = $this->getLevel()->getBlock($this->temporalVector->setComponents($x, $y, $z));

			if(!$side->canBeFlowedInto() and !($side instanceof Liquid)) continue;
			if($side instanceof Liquid and $side->getDamage() === 0) continue;

			
			if($this->getLevel()->getBlock($this->temporalVector->setComponents($x, $y - 1, $z))->canBeFlowedInto()){
				return $accumulatedCost;
			}

			if($accumulatedCost < 4){
				$realCost = $this->calculateFlowCost($side, $accumulatedCost + 1, $j);
				if($realCost < $cost) $cost = $realCost;
			}
		}

		return $cost;
	}

	private function getOptimalFlowDirections(){
		if($this->temporalVector === null) $this->temporalVector = new Vector3(0, 0, 0);

		for($j = 0; $j < 4; ++$j){
			$this->flowCost[$j] = 1000;
			$x = $this->x; $y = $this->y; $z = $this->z;
			if($j === 0)     --$x;
			elseif($j === 1) ++$x;
			elseif($j === 2) --$z;
			elseif($j === 3) ++$z;

			$block = $this->getLevel()->getBlock($this->temporalVector->setComponents($x, $y, $z));

			if(!$block->canBeFlowedInto() and !($block instanceof Liquid)) continue;
			if($block instanceof Liquid and $block->getDamage() === 0) continue;

			if($this->getLevel()->getBlock($this->temporalVector->setComponents($x, $y - 1, $z))->canBeFlowedInto()){
				$this->flowCost[$j] = 0;
			}else{
				$this->flowCost[$j] = $this->calculateFlowCost($block, 1, $j);
			}
		}

		$minCost = min($this->flowCost);
		for($i = 0; $i < 4; ++$i){
			$this->isOptimalFlowDirection[$i] = ($this->flowCost[$i] === $minCost);
		}

		return $this->isOptimalFlowDirection;
	}

	private function getSmallestFlowDecay(Vector3 $pos, $decay){
		$blockDecay = $this->getFlowDecay($pos);
		if($blockDecay < 0) return $decay;
		elseif($blockDecay === 0) ++$this->adjacentSources;
		elseif($blockDecay >= 8) $blockDecay = 0;
		return ($decay >= 0 and $blockDecay >= $decay) ? $decay : $blockDecay;
	}

	private function checkForHarden(){
		if($this instanceof Lava){
			$colliding = false;
			for($side = 0; $side <= 5 and !$colliding; ++$side){
				$colliding = $this->getSide($side) instanceof Water;
			}
			if($colliding){
				if($this->getDamage() === 0){
					$this->getLevel()->setBlock($this, Block::get(Item::OBSIDIAN), true);
				}elseif($this->getDamage() <= 4){
					$this->getLevel()->setBlock($this, Block::get(Item::COBBLESTONE), true);
				}
				$this->triggerLavaMixEffects($this);
			}
		}
	}

	protected function triggerLavaMixEffects(Vector3 $pos){
		$this->getLevel()->addSound(new FizzSound($pos->add(0.5, 0.5, 0.5), 2.5 + mt_rand(0, 1000) / 1000 * 0.8));
		for($i = 0; $i < 8; ++$i){
			$this->getLevel()->addParticle(new SmokeParticle($pos->add(mt_rand(0, 80) / 100, 0.5, mt_rand(0, 80) / 100)));
		}
	}
}
