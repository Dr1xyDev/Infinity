<?php
/*    
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█    
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░    
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.1
 *               InfinityProject By @Dr1xyDev    
 *   YT:         @Dr1xyDev    
 *   GitHub:     github.com/Dr1xyDev/Infinity    
*/

namespace pocketmine\utils;

class Random{
	const X = 123456789;
	const Y = 362436069;
	const Z = 521288629;
	const W = 88675123;

	
	private $x;

	
	private $y;

	
	private $z;

	
	private $w;

	protected $seed;

	
	public function __construct($seed = -1){
		if($seed === -1){
			$seed = time();
		}

		$this->setSeed($seed);
	}

	
	public function setSeed($seed){
		$this->seed = $seed;
		$this->x = self::X ^ $seed;
		$this->y = self::Y ^ ($seed << 17) | (($seed >> 15) & 0x7fffffff) & 0xffffffff;
		$this->z = self::Z ^ ($seed << 31) | (($seed >>  1) & 0x7fffffff) & 0xffffffff;
		$this->w = self::W ^ ($seed << 18) | (($seed >> 14) & 0x7fffffff) & 0xffffffff;
	}
	public function getSeed(){
		return $this->seed;
	}

	
	public function nextInt(){
		return $this->nextSignedInt() & 0x7fffffff;
	}

	
	public function nextSignedInt(){
		$t = ($this->x ^ ($this->x << 11)) & 0xffffffff;

		$this->x = $this->y;
		$this->y = $this->z;
		$this->z = $this->w;
		$this->w = ($this->w ^ (($this->w >> 19) & 0x7fffffff)
							 ^ ($t ^ (($t >> 8) & 0x7fffffff))) & 0xffffffff;

		return $this->w;
	}

	
	public function nextFloat(){
		return $this->nextInt() / 0x7fffffff;
	}

	
	public function nextSignedFloat(){
		return $this->nextSignedInt() / 0x7fffffff;
	}

	
	public function nextBoolean(){
		return ($this->nextSignedInt() & 0x01) === 0;
	}

	
	public function nextRange($start = 0, $end = 0x7fffffff){
		return $start + ($this->nextInt() % ($end + 1 - $start));
	}

	public function nextBoundedInt($bound){
		return $this->nextInt() % $bound;
	}

}