<?php

/*
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.0
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

	/**
	 * @param Level   $level
	 * @param Vector3 $v3
	 * @param bool    $hate
	 * @param bool    $reason
	 * @return bool|float|string
	 * 判断某坐标是否可以行走
	 * 并给出原因
	 */
	public function ifjump(Level $level, Vector3 $v3, $hate = false, $reason = false){  //boybook Y轴算法核心函数
		$x = floor($v3->getX());
		$y = floor($v3->getY());
		$z = floor($v3->getZ());

		//echo ($y." ");
		if($this->whatBlock($level, new Vector3($x, $y, $z)) == "air"){
			//echo "前方空气 ";
			if($this->whatBlock($level, new Vector3($x, $y - 1, $z)) == "block" or new Vector3($x, $y - 1, $z) == "climb"){  //方块
				//echo "考虑向前 ";
				if($this->whatBlock($level, new Vector3($x, $y + 1, $z)) == "block" or $this->whatBlock($level, new Vector3($x, $y + 1, $z)) == "half" or $this->whatBlock($level, new Vector3($x, $y + 1, $z)) == "high"){  //上方一格被堵住了
					//echo "上方卡住 \n";
					if($reason) return 'up!';
					return false;  //上方卡住
				}else{
					//echo "GO向前走 \n";
					if($reason) return 'GO';
					return $y;  //向前走
				}
			}elseif($this->whatBlock($level, new Vector3($x, $y - 1, $z)) == "water"){  //水
				//echo "下水游泳 \n";
				if($reason) return 'swim';
				return $y - 1;  //降低一格向前走（下水游泳）
			}elseif($this->whatBlock($level, new Vector3($x, $y - 1, $z)) == "half"){  //半砖
				//echo "下到半砖 \n";
				if($reason) return 'half';
				return $y - 0.5;  //向下跳0.5格
			}elseif($this->whatBlock($level, new Vector3($x, $y - 1, $z)) == "lava"){  //岩浆
				//echo "前方岩浆 \n";
				if($reason) return 'lava';
				return false;  //前方岩浆
			}elseif($this->whatBlock($level, new Vector3($x, $y - 1, $z)) == "air"){  //空气
				//echo "考虑向下跳 ";
				if($this->whatBlock($level, new Vector3($x, $y - 2, $z)) == "block"){
					//echo "GO向下跳 \n";
					if($reason) return 'down';
					return $y - 1;  //向下跳
				}else{ //前方悬崖
					//echo "前方悬崖 \n";
					if($reason) return 'fall';
					if($hate === false){
						return false;
					}else{
						return $y - 1;  //向下跳
					}
				}
			}
		}elseif($this->whatBlock($level, new Vector3($x, $y, $z)) == "water"){  //水
			//echo "正在水中";
			if($this->whatBlock($level, new Vector3($x, $y + 1, $z)) == "water"){  //上面还是水
				//echo "向上游 \n";
				if($reason) return 'inwater';
				return $y + 1;  //向上游，防溺水
			}elseif($this->whatBlock($level, new Vector3($x, $y + 1, $z)) == "block" or $this->whatBlock($level, new Vector3($x, $y + 1, $z)) == "half"){  //上方一格被堵住了
				if($this->whatBlock($level, new Vector3($x, $y - 1, $z)) == "block" or $this->whatBlock($level, new Vector3($x, $y - 1, $z)) == "half"){  //下方一格被也堵住了
					//echo "上下都被卡住 \n";
					if($reason) return 'up!_down!';
					return false;  //上下都被卡住
				}else{
					//echo "向下游 \n";
					if($reason) return 'up!';
					return $y - 1;  //向下游，防卡住
				}
			}else{
				//echo "游泳ing... \n";
				if($reason) return 'swim...';
				return $y;  //向前游
			}
		}elseif($this->whatBlock($level, new Vector3($x, $y, $z)) == "half"){  //半砖
			//echo "前方半砖 \n";
			if($this->whatBlock($level, new Vector3($x, $y + 1, $z)) == "block" or $this->whatBlock($level, new Vector3($x, $y + 1, $z)) == "half" or $this->whatBlock($level, new Vector3($x, $y + 1, $z)) == "high"){  //上方一格被堵住了
				//return false;  //上方卡住
			}else{
				if($reason) return 'halfGO';
				return $y + 0.5;
			}

		}elseif($this->whatBlock($level, new Vector3($x, $y, $z)) == "lava"){  //岩浆
			//echo "前方岩浆 \n";
			if($reason) return 'lava';
			return false;
		}elseif($this->whatBlock($level, new Vector3($x, $y, $z)) == "high"){  //1.5格高方块
			//echo "前方栅栏 \n";
			if($reason) return 'high';
			return false;
		}elseif($this->whatBlock($level, new Vector3($x, $y, $z)) == "climb"){  //梯子
			//echo "前方梯子 \n";
			//return $y;
			if($reason) return 'climb';
			if($hate){
				return $y + 0.7;
			}else{
				return $y + 0.5;
			}
		}else{  //考虑向上
			//echo "考虑向上 ";
			if($this->whatBlock($level, new Vector3($x, $y + 1, $z)) != "air"){  //前方是面墙
				//echo "前方是墙 \n";
				if($reason) return 'wall';
				return false;
			}else{
				if($this->whatBlock($level, new Vector3($x, $y + 2, $z)) == "block" or $this->whatBlock($level, new Vector3($x, $y + 2, $z)) == "half" or $this->whatBlock($level, new Vector3($x, $y + 2, $z)) == "high"){  //上方两格被堵住了
					//echo "2格处被堵 \n";
					if($reason) return 'up2!';
					return false;
				}else{
					//echo "GO向上跳 \n";
					if($reason) return 'upGO';
					return $y + 1;  //向上跳
				}
			}
		}
		return false;
	}

	public function whatBlock(Level $level, $v3){  //boybook的y轴判断法 核心 什么方块？
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
				//透明方块
				return "air";
				break;
			case 8:
			case 9:
				//水
				return "water";
				break;
			case 10:
			case 11:
				//岩浆
				return "lava";
				break;
			case 44:
			case 158:
				//半砖
				if($damage >= 8){
					return "block";
				}else{
					return "half";
				}
				break;
			case 64:
				//门
				//var_dump($damage." ");
				//TODO 不知如何判断门是否开启，因为以下条件永远满足
				if(($damage & 0x08) === 0x08){
					return "air";
				}else{
					return "block";
				}
				break;
			case 85:
			case 107:
			case 139:
				//1.5格高的无法跳跃物
				return "high";
				break;
			case 65:
			case 106:
				//可攀爬物
				return "climb";
				break;
			default:
				//普通方块
				return "block";
				break;
		}
	}

	/**
	 * @param $mx
	 * @param $mz
	 * @return float|int
	 * 获取yaw角度
	 */
	public function getMyYaw($mx, $mz){  //根据motion计算转向角度
		//转向计算
		if($mz == 0){  //斜率不存在
			if($mx < 0){
				$yaw = -90;
			}else{
				$yaw = 90;
			}
		}else{  //存在斜率
			if($mx >= 0 and $mz > 0){  //第一象限
				$atan = atan($mx / $mz);
				$yaw = rad2deg($atan);
			}elseif($mx >= 0 and $mz < 0){  //第二象限
				$atan = atan($mx / abs($mz));
				$yaw = 180 - rad2deg($atan);
			}elseif($mx < 0 and $mz < 0){  //第三象限
				$atan = atan($mx / $mz);
				$yaw = -(180 - rad2deg($atan));
			}elseif($mx < 0 and $mz > 0){  //第四象限
				$atan = atan(abs($mx) / $mz);
				$yaw = -(rad2deg($atan));
			}else{
				$yaw = 0;
			}
		}

		$yaw = -$yaw;
		return $yaw;
	}

	/**
	 * @param Vector3 $from
	 * @param Vector3 $to
	 * @return float|int
	 * 获取pitch角度
	 */
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