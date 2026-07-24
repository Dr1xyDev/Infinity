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


use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\item\Item as ItemItem;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\network\Network;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\Player;
use pocketmine\math\Vector3;
use pocketmine\event\entity\EntityDamageEvent;

class Zombie extends Monster{
	const NETWORK_ID = 32;

	public $width = 0.6;
	public $length = 0.6;
	public $height = 1.8;

	public $dropExp = [5, 5];
	
	public $drag = 0.2;
	public $gravity = 0.3;
	
	private $moveDirection = null; //移动方向
	private $moveSpeed = 0.2; //移动速度
	private $hated = false; //仇恨的玩家
	private $tempTicker = 0;
	private $tempTicking = false; //走出困境计时器
	private $moveTicker = 0; //运动计时器
	private $hate_r = 16; //仇恨半径
	private $attack_r = 1.5; //攻击半径
	private $fire_r = 1.3; //点燃半径
	private $hateTicker = 0; //仇恨计时器

	public function getName() : string{
		return "Zombie";
	}
	
	public function initEntity(){
		$this->setMaxHealth(20);
		parent::initEntity();
	}
	
	public function attack($damage, EntityDamageEvent $source){
		parent::attack($damage, $source);
		
		if($source instanceof EntityDamageByEntityEvent){
			$e = $source->getDamager();
			
			$deltaX = $this->x - $e->x;
			$deltaZ = $this->z - $e->z;
			$this->knockBack($e, $damage, $deltaX / 100, $deltaZ / 100, $source->getKnockBack());
		}
	}

	
	private function generateRandomDirection(){
		return new Vector3(mt_rand(-1000, 1000) / 1000, 0, mt_rand(-1000, 1000) / 1000);
	}
	
	/*
	 * 返回一个一位小数
	*/
	private function toFloat($num){
		while(((abs($num) > 1) or (abs($num) < 0.1)) and (abs($num) > 0)) {
			if(abs($num) > 1) $num /= 10;
			if(abs($num) < 0.1) $num *= 10;
		}
		return $num;
	}
	
	private function generateDirection(Vector3 $pos){
		return new Vector3($this->toFloat($pos->x - $this->x), 0, $this->toFloat($pos->z - $this->z));
	}
	
	private function getNearestPlayer(){
		$dis = PHP_INT_MAX;
		$player = false;
		foreach($this->getViewers() as $p){
			if($p->distance($this) < $dis){
				$dis = $p->distance($this);
				$player = $p;
			}
		}
		return (($dis <= $this->hate_r) ? $p : false);
	}
	
	private function getVelY(){
		$expectedPos = (new Vector3($this->x + $this->moveDirection->x * $this->moveSpeed, $this->y + $this->motionY, $this->z + $this->moveDirection->z * $this->moveSpeed))->round();
		$block0 = $this->getLevel()->getBlock($expectedPos);
		$block1 = $this->getLevel()->getBlock($expectedPos->add(0, 1, 0));
		if($block1->getId() != 0) return 1.2;
		return 0;
	}
	
	public function onUpdate($currentTick){
		if($this->closed !== false){
			return false;
		}
		
		return parent::onUpdate($currentTick);
		
		$this->lastUpdate = $currentTick;

		$this->timings->startTiming();

		$hasUpdate = parent::onUpdate($currentTick);

		if($this->isAlive()){
			/* Don't use time directly
			 * Instead, get remainder of current time divided by 24,000
			 * This tells us the time of day, which is what we really need
			 */
			$timeOfDay = abs($this->getLevel()->getTime() % 24000);
			if(0 < $timeOfDay and $timeOfDay < 13000) $this->setOnFire(2); //僵尸起火
			
			$p = $this->getNearestPlayer();//找到最近的可以被仇恨的玩家
			if(!$p) {
				$this->hated = false;
				if(++$this->moveTicker >= 100) {
					$this->moveDirection = $this->generateRandomDirection();
					$this->moveTicker = 0;
				}
			}else{
				$this->hated = $p;
				if($p->distance($this) <= $this->fire_r) $p->setOnFire(2); //点燃玩家
				if(!$this->tempTicking){
					if(++$this->hateTicker >= 10 or $this->moveDirection == null) { //每0.5秒获取僵尸前进的新方向
						$this->moveDirection = $this->generateDirection($p);
						$this->hateTicker = 0;
					}
				}
			}
			
			
			if($this->tempTicking) { //帮助僵尸寻找新的方向走出困境
				if(++$this->tempTicker >= 20) {
					$this->tempTicking = false;
					$this->tempTicker = 0;
				}
			}
			
			if($this->hated instanceof Player){ //攻击玩家
				if($this->hated->distance($this) < $this->attack_r) $this->hated->attack(2, new EntityDamageByEntityEvent($this, $this->hated, EntityDamageEvent::CAUSE_ENTITY_ATTACK, 2));
			}
			
			if($this->moveDirection != null){
				if($this->motionX ** 2 + $this->motionZ ** 2 <= $this->moveDirection->lengthSquared()){
					$motionY = $this->getVelY(); //僵尸运动计算
					if($motionY >= 0){
						$this->motionX = $this->moveDirection->x * $this->moveSpeed;
						$this->motionZ = $this->moveDirection->z * $this->moveSpeed;
						$this->motionY = $motionY;
					}else{
						$this->moveDirection = $this->generateRandomDirection(); //生成随机运动方向
						$this->moveTicker = 0;
						$this->tempTicking = true;
					}
				}
			}else{
				$this->moveDirection = $this->generateRandomDirection();
				$this->moveTicker = 0;
			}
			
			//var_dump($this->moveDirection,$this->motionX,$this->motionZ);
			
			$expectedPos = new Vector3($this->x + $this->motionX, $this->y + $this->motionY, $this->z + $this->motionZ);
			
			if($this->motionY == 0) $this->motionY -= $this->gravity; //重力计算

			$this->move($this->motionX, $this->motionY, $this->motionZ);

			if($expectedPos->distanceSquared($this) > 0){
				$this->moveDirection = $this->generateRandomDirection();
			}
			
			$friction = 1 - $this->drag;

			$this->motionX *= $friction;
			//$this->motionY *= 1 - $this->drag;
			$this->motionZ *= $friction;

			$f = sqrt(($this->motionX ** 2) + ($this->motionZ ** 2));
			$this->yaw = (-atan2($this->motionX, $this->motionZ) * 180 / M_PI); //视角计算
			//$this->pitch = (-atan2($f, $this->motionY) * 180 / M_PI);
			
			$this->updateMovement();
		}
		
		$this->timings->stopTiming();

		return $hasUpdate or !$this->onGround or abs($this->motionX) > 0.00001 or abs($this->motionY) > 0.00001 or abs($this->motionZ) > 0.00001;
	}

	public function spawnTo(Player $player){
		$pk = new AddEntityPacket();
		$pk->eid = $this->getId();
		$pk->type = Zombie::NETWORK_ID;
		$pk->x = $this->x;
		$pk->y = $this->y;
		$pk->z = $this->z;
		$pk->speedX = $this->motionX;
		$pk->speedY = $this->motionY;
		$pk->speedZ = $this->motionZ;
		$pk->yaw = $this->yaw;
		$pk->pitch = $this->pitch;
		$pk->metadata = $this->dataProperties;
		$player->dataPacket($pk);

		parent::spawnTo($player);
	}

	public function getDrops(){
		$lootingL = 0;
		$cause = $this->lastDamageCause;
		$drops = [];
		if($cause instanceof EntityDamageByEntityEvent and $cause->getDamager() instanceof Player){
			$lootingL = $cause->getDamager()->getItemInHand()->getEnchantmentLevel(Enchantment::TYPE_WEAPON_LOOTING);
			if(mt_rand(0, 199) < (5 + 2 * $lootingL)){
				switch(mt_rand(0, 3)){
					case 0:
						$drops[] = ItemItem::get(ItemItem::IRON_INGOT, 0, 1);
						break;
					case 1:
						$drops[] = ItemItem::get(ItemItem::CARROT, 0, 1);
						break;
					case 2:
						$drops[] = ItemItem::get(ItemItem::POTATO, 0, 1);
						break;
				}
			}
			$count = mt_rand(0, 2 + $lootingL);
			if($count > 0){
				$drops[] = ItemItem::get(ItemItem::ROTTEN_FLESH, 0, $count);
			}
		}

		return $drops;
	}
}
