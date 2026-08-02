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

use pocketmine\item\Item;
use pocketmine\item\Tool;
use pocketmine\math\Vector3;

class ActiveRedstoneLamp extends Solid implements ElectricalAppliance, SolidLight{
	protected $id = self::ACTIVE_REDSTONE_LAMP;

	public function __construct($meta = 0){
		$this->meta = (int) $meta;
	}

	public function getName() : string{
		return "Active Redstone Lamp";
	}

	public function getHardness() {
		return 0.3;
	}

	public function getToolType(){
		return Tool::TYPE_PICKAXE;
	}

	public function getLightLevel(){
		return 15;
	}

	public function getDrops(Item $item) : array {
		return [
			[Item::INACTIVE_REDSTONE_LAMP, 0 ,1],
		];
	}

	public function isLightedByAround(){
		return ($this->meta == 1);
	}

	protected function checkPower(array $ignore = []){
		if($this->isLightedByAround()){
			$sides = [Vector3::SIDE_EAST, Vector3::SIDE_WEST, Vector3::SIDE_SOUTH, Vector3::SIDE_NORTH, Vector3::SIDE_UP, Vector3::SIDE_DOWN];
			foreach($sides as $side){
				if(!in_array($side, $ignore)){
					
					$block = $this->getSide($side);
					if($block->getId() == $this->id){
						if(!$block->isLightedByAround()) return true;
					}
				}
			}
		}
		return false;
	}

	public function lightAround(){
		$sides = [Vector3::SIDE_EAST, Vector3::SIDE_WEST, Vector3::SIDE_SOUTH, Vector3::SIDE_NORTH, Vector3::SIDE_UP, Vector3::SIDE_DOWN];
		foreach($sides as $side){
			
			$block = $this->getSide($side);
			if($block->getId() == self::INACTIVE_REDSTONE_LAMP){
				$block->turnOn();
			}
		}
	}

	protected function turnAroundOff(array $ignore = []){
		if(!$this->isLightedByAround()){
			$sides = [Vector3::SIDE_EAST, Vector3::SIDE_WEST, Vector3::SIDE_SOUTH, Vector3::SIDE_NORTH, Vector3::SIDE_UP, Vector3::SIDE_DOWN];

			foreach($sides as $side){
				if(!in_array($side, $ignore)){
					
					$block = $this->getSide($side);
					if($block->getId() == $this->id){
						if($block->isLightedByAround()){
							if(!$block->checkPower([$this->getOppositeSide($side)])) $block->turnOff();
						}
					}
				}
			}
		}
	}

	public function turnOn(){
		
		$this->meta = 0;
		$this->getLevel()->setBlock($this, $this, true, false);
		return true;
	}

	public function turnOff(){
		$this->getLevel()->setBlock($this, new InactiveRedstoneLamp(), true, true);
		
		return true;
	}
}