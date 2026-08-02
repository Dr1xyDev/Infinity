<?php
/*    
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█    
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░    
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.1
 *               InfinityProject By @Dr1xyDev    
 *   YT:         @Dr1xyDev    
 *   GitHub:     github.com/Dr1xyDev/Infinity    
*/

namespace pocketmine\event\player;

use pocketmine\event\Cancellable;
use pocketmine\entity\Human;

class PlayerExperienceChangeEvent extends PlayerEvent implements Cancellable{
	
	
	const ADD_EXPERIENCE = 0;
	const SET_EXPERIENCE = 1;
	
	public static $handlerList = null;
	
	public $progress;
	public $expLevel;

	public function __construct(Human $player, int $expLevel, float $progress){
		$this->progress = $progress;
		$this->expLevel = $expLevel;
		$this->player = $player;
	}
	
	
	public function getAction(){
		return self::SET_EXPERIENCE;
	}

	public function getExpLevel(){
		return $this->expLevel;
	}

	public function setExpLevel($level){
		$this->expLevel = $level;
	}

	public function getProgress(): float{
		return $this->progress;
	}
	
	public function setProgress(float $progress){
		$this->progress = $progress; 
	}

	public function getExp(){
		return Human::getLevelXpRequirement($this->level) * $this->progress;
	}

	public function setExp($exp){
		$this->progress = $exp / Human::getLevelXpRequirement($this->level);
	}
}
