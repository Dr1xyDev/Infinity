<?php
/*    
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█    
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░    
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.1
 *               InfinityProject By @Dr1xyDev    
 *   YT:         @Dr1xyDev    
 *   GitHub:     github.com/Dr1xyDev/Infinity    
*/

namespace pocketmine\entity;

use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\level\Level;
use pocketmine\math\Vector3;

abstract class Creature extends Living{
	public $attackingTick = 0;

	public function onUpdate($tick){
		if(!$this instanceof Human){
			if($this->attackingTick > 0){
				$this->attackingTick--;
			}
			if(!$this->isAlive() and $this->hasSpawned){
				++$this->deadTicks;
				if($this->deadTicks >= 20){
					$this->despawnFromAll();
				}
				return true;
			}
			if($this->isAlive()){

				$this->motionY -= $this->gravity;

				$this->move($this->motionX, $this->motionY, $this->motionZ);

				$friction = 1 - $this->drag;

				if($this->onGround and (abs($this->motionX) > 0.00001 or abs($this->motionZ) > 0.00001)){
					$friction = $this->getLevel()->getBlock($this->temporalVector->setComponents((int) floor($this->x), (int) floor($this->y - 1), (int) floor($this->z) - 1))->getFrictionFactor() * $friction;
				}

				$this->motionX *= $friction;
				$this->motionY *= 1 - $this->drag;
				$this->motionZ *= $friction;

				if($this->onGround){
					$this->motionY *= -0.5;
				}

				$this->updateMovement();
			}
		}
		parent::entityBaseTick();
		return parent::onUpdate($tick);
	}

	public function willMove($distance = 36){
		foreach($this->getViewers() as $viewer){
			if($this->distance($viewer->getLocation()) <= $distance) return true;
		}
		return false;
	}

	public function attack($damage, EntityDamageEvent $source){
		parent::attack($damage, $source);
		if(!$source->isCancelled() and $source->getCause() == EntityDamageEvent::CAUSE_ENTITY_ATTACK){
			$this->attackingTick = 20;
		}
	}

	
	public function ifjump(Level $level, Vector3 $v3, $hate = false, $reason = false){  
		$x = floor($v3->getX());
		$y = floor($v3->getY());
		$z = floor($v3->getZ());

		
		if($this->whatBlock($level, new Vector3($x, $y, $z)) == "air"){
			
			if($this->whatBlock($level, new Vector3($x, $y - 1, $z)) == "block" or new Vector3($x, $y - 1, $z) == "climb"){  
				
				if($this->whatBlock($level, new Vector3($x, $y + 1, $z)) == "block" or $this->whatBlock($level, new Vector3($x, $y + 1, $z)) == "half" or $this->whatBlock($level, new Vector3($x, $y + 1, $z)) == "high"){  
					
					if($reason) return 'up!';
					return false;  
				}else{
					
					if($reason) return 'GO';
					return $y;  
				}
			}elseif($this->whatBlock($level, new Vector3($x, $y - 1, $z)) == "water"){  
				
				if($reason) return 'swim';
				return $y - 1;  
			}elseif($this->whatBlock($level, new Vector3($x, $y - 1, $z)) == "half"){  
				
				if($reason) return 'half';
				return $y - 0.5;  
			}elseif($this->whatBlock($level, new Vector3($x, $y - 1, $z)) == "lava"){  
				
				if($reason) return 'lava';
				return false;  
			}elseif($this->whatBlock($level, new Vector3($x, $y - 1, $z)) == "air"){  
				
				if($this->whatBlock($level, new Vector3($x, $y - 2, $z)) == "block"){
					
					if($reason) return 'down';
					return $y - 1;  
				}else{ 
					
					if($reason) return 'fall';
					if($hate === false){
						return false;
					}else{
						return $y - 1;  
					}
				}
			}
		}elseif($this->whatBlock($level, new Vector3($x, $y, $z)) == "water"){  
			
			if($this->whatBlock($level, new Vector3($x, $y + 1, $z)) == "water"){  
				
				if($reason) return 'inwater';
				return $y + 1;  
			}elseif($this->whatBlock($level, new Vector3($x, $y + 1, $z)) == "block" or $this->whatBlock($level, new Vector3($x, $y + 1, $z)) == "half"){  
				if($this->whatBlock($level, new Vector3($x, $y - 1, $z)) == "block" or $this->whatBlock($level, new Vector3($x, $y - 1, $z)) == "half"){  
					
					if($reason) return 'up!_down!';
					return false;  
				}else{
					
					if($reason) return 'up!';
					return $y - 1;  
				}
			}else{
				
				if($reason) return 'swim...';
				return $y;  
			}
		}elseif($this->whatBlock($level, new Vector3($x, $y, $z)) == "half"){  
			
			if($this->whatBlock($level, new Vector3($x, $y + 1, $z)) == "block" or $this->whatBlock($level, new Vector3($x, $y + 1, $z)) == "half" or $this->whatBlock($level, new Vector3($x, $y + 1, $z)) == "high"){  
				
			}else{
				if($reason) return 'halfGO';
				return $y + 0.5;
			}

		}elseif($this->whatBlock($level, new Vector3($x, $y, $z)) == "lava"){  
			
			if($reason) return 'lava';
			return false;
		}elseif($this->whatBlock($level, new Vector3($x, $y, $z)) == "high"){  
			
			if($reason) return 'high';
			return false;
		}elseif($this->whatBlock($level, new Vector3($x, $y, $z)) == "climb"){  
			
			
			if($reason) return 'climb';
			if($hate){
				return $y + 0.7;
			}else{
				return $y + 0.5;
			}
		}else{  
			
			if($this->whatBlock($level, new Vector3($x, $y + 1, $z)) != "air"){  
				
				if($reason) return 'wall';
				return false;
			}else{
				if($this->whatBlock($level, new Vector3($x, $y + 2, $z)) == "block" or $this->whatBlock($level, new Vector3($x, $y + 2, $z)) == "half" or $this->whatBlock($level, new Vector3($x, $y + 2, $z)) == "high"){  
					
					if($reason) return 'up2!';
					return false;
				}else{
					
					if($reason) return 'upGO';
					return $y + 1;  
				}
			}
		}
		return false;
	}

	public function whatBlock(Level $level, $v3){  
		$id = $level->getBlockIdAt($v3->x, $v3->y, $v3->z);
		$damage = $level->getBlockDataAt($v3->x, $v3->y, $v3->z);
		switch($id){
			case 0:
			case 6:
			case 27:
			case 30:
			case 31:
			case 37:
			case 38:
			case 39:
			case 40:
			case 50:
			case 51:
			case 63:
			case 66:
			case 68:
			case 78:
			case 111:
			case 141:
			case 142:
			case 171:
			case 175:
			case 244:
			case 323:
				
				return "air";
				break;
			case 8:
			case 9:
				
				return "water";
				break;
			case 10:
			case 11:
				
				return "lava";
				break;
			case 44:
			case 158:
				
				if($damage >= 8){
					return "block";
				}else{
					return "half";
				}
				break;
			case 64:
				
				
				
				if(($damage & 0x08) === 0x08){
					return "air";
				}else{
					return "block";
				}
				break;
			case 85:
			case 107:
			case 139:
				
				return "high";
				break;
			case 65:
			case 106:
				
				return "climb";
				break;
			default:
				
				return "block";
				break;
		}
	}

	
	public function getMyYaw($mx, $mz){  
		
		if($mz == 0){  
			if($mx < 0){
				$yaw = -90;
			}else{
				$yaw = 90;
			}
		}else{  
			if($mx >= 0 and $mz > 0){  
				$atan = atan($mx / $mz);
				$yaw = rad2deg($atan);
			}elseif($mx >= 0 and $mz < 0){  
				$atan = atan($mx / abs($mz));
				$yaw = 180 - rad2deg($atan);
			}elseif($mx < 0 and $mz < 0){  
				$atan = atan($mx / $mz);
				$yaw = -(180 - rad2deg($atan));
			}elseif($mx < 0 and $mz > 0){  
				$atan = atan(abs($mx) / $mz);
				$yaw = -(rad2deg($atan));
			}else{
				$yaw = 0;
			}
		}

		$yaw = -$yaw;
		return $yaw;
	}

	
	public function getMyPitch(Vector3 $from, Vector3 $to){
		$distance = $from->distance($to);
		$height = $to->y - $from->y;
		if($height > 0){
			return -rad2deg(asin($height / $distance));
		}elseif($height < 0){
			return rad2deg(asin(-$height / $distance));
		}else{
			return 0;
		}
	}
}