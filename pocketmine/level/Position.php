<?php
/*    
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█    
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░    
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.1
 *               InfinityProject By @Dr1xyDev    
 *   YT:         @Dr1xyDev    
 *   GitHub:     github.com/Dr1xyDev/Infinity    
*/

namespace pocketmine\level;

use pocketmine\math\Vector3;
use pocketmine\utils\LevelException;

class Position extends Vector3{

	
	public $level = null;

	
	public function __construct($x = 0, $y = 0, $z = 0, Level $level = null){
		$this->x = $x;
		$this->y = $y;
		$this->z = $z;
		$this->level = $level;
	}

	public static function fromObject(Vector3 $pos, Level $level = null){
		return new Position($pos->x, $pos->y, $pos->z, $level);
	}
	
	public function add($x, $y = 0, $z = 0){
		if($x instanceof Vector3){
			return new Position($this->x + $x->x, $this->y + $x->y, $this->z + $x->z, $this->level);
		}else{
			return new Position($this->x + $x, $this->y + $y, $this->z + $z, $this->level);
		}
	}

	
	public function getLevel(){
		return $this->level;
	}

	public function setLevel(Level $level){
		$this->level = $level;
		return $this;
	}

	
	public function isValid(){
		return $this->level !== null;
	}

	
	public function getSide($side, $step = 1){
		if(!$this->isValid()){
			throw new LevelException("Undefined Level reference");
		}

		return Position::fromObject(parent::getSide($side, $step), $this->level);
	}

	public function __toString(){
		return "Position(level=" . ($this->isValid() ? $this->getLevel()->getName() : "null") . ",x=" . $this->x . ",y=" . $this->y . ",z=" . $this->z . ")";
	}

	
	public function setComponents($x, $y, $z){
		$this->x = $x;
		$this->y = $y;
		$this->z = $z;
		return $this;
	}

	public function fromObjectAdd(Vector3 $pos, $x, $y, $z){
		if($pos instanceof Position){
			$this->level = $pos->level;
		}
		parent::fromObjectAdd($pos, $x, $y, $z);
		return $this;
	}

}
