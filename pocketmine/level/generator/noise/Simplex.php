<?php
/*    
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█    
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░    
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.1
 *               InfinityProject By @Dr1xyDev    
 *   YT:         @Dr1xyDev    
 *   GitHub:     github.com/Dr1xyDev/Infinity    
*/

namespace pocketmine\level\generator\noise;

use pocketmine\utils\Random;

class Simplex extends Perlin{
	protected static $SQRT_3;
	protected static $SQRT_5;
	protected static $F2;
	protected static $G2;
	protected static $G22;
	protected static $F3;
	protected static $G3;
	protected static $F4;
	protected static $G4;
	protected static $G42;
	protected static $G43;
	protected static $G44;
	protected static $grad4 = [[0, 1, 1, 1], [0, 1, 1, -1], [0, 1, -1, 1], [0, 1, -1, -1],
		[0, -1, 1, 1], [0, -1, 1, -1], [0, -1, -1, 1], [0, -1, -1, -1],
		[1, 0, 1, 1], [1, 0, 1, -1], [1, 0, -1, 1], [1, 0, -1, -1],
		[-1, 0, 1, 1], [-1, 0, 1, -1], [-1, 0, -1, 1], [-1, 0, -1, -1],
		[1, 1, 0, 1], [1, 1, 0, -1], [1, -1, 0, 1], [1, -1, 0, -1],
		[-1, 1, 0, 1], [-1, 1, 0, -1], [-1, -1, 0, 1], [-1, -1, 0, -1],
		[1, 1, 1, 0], [1, 1, -1, 0], [1, -1, 1, 0], [1, -1, -1, 0],
		[-1, 1, 1, 0], [-1, 1, -1, 0], [-1, -1, 1, 0], [-1, -1, -1, 0]];
	protected static $simplex = [
		[0, 1, 2, 3], [0, 1, 3, 2], [0, 0, 0, 0], [0, 2, 3, 1], [0, 0, 0, 0], [0, 0, 0, 0], [0, 0, 0, 0], [1, 2, 3, 0],
		[0, 2, 1, 3], [0, 0, 0, 0], [0, 3, 1, 2], [0, 3, 2, 1], [0, 0, 0, 0], [0, 0, 0, 0], [0, 0, 0, 0], [1, 3, 2, 0],
		[0, 0, 0, 0], [0, 0, 0, 0], [0, 0, 0, 0], [0, 0, 0, 0], [0, 0, 0, 0], [0, 0, 0, 0], [0, 0, 0, 0], [0, 0, 0, 0],
		[1, 2, 0, 3], [0, 0, 0, 0], [1, 3, 0, 2], [0, 0, 0, 0], [0, 0, 0, 0], [0, 0, 0, 0], [2, 3, 0, 1], [2, 3, 1, 0],
		[1, 0, 2, 3], [1, 0, 3, 2], [0, 0, 0, 0], [0, 0, 0, 0], [0, 0, 0, 0], [2, 0, 3, 1], [0, 0, 0, 0], [2, 1, 3, 0],
		[0, 0, 0, 0], [0, 0, 0, 0], [0, 0, 0, 0], [0, 0, 0, 0], [0, 0, 0, 0], [0, 0, 0, 0], [0, 0, 0, 0], [0, 0, 0, 0],
		[2, 0, 1, 3], [0, 0, 0, 0], [0, 0, 0, 0], [0, 0, 0, 0], [3, 0, 1, 2], [3, 0, 2, 1], [0, 0, 0, 0], [3, 1, 2, 0],
		[2, 1, 0, 3], [0, 0, 0, 0], [0, 0, 0, 0], [0, 0, 0, 0], [3, 1, 0, 2], [0, 0, 0, 0], [3, 2, 0, 1], [3, 2, 1, 0]];
	protected $offsetW;

	public function __construct(Random $random, $octaves, $persistence, $expansion = 1){
		parent::__construct($random, $octaves, $persistence, $expansion);
		$this->offsetW = $random->nextFloat() * 256;
		self::$SQRT_3 = sqrt(3);
		self::$SQRT_5 = sqrt(5);
		self::$F2 = 0.5 * (self::$SQRT_3 - 1);
		self::$G2 = (3 - self::$SQRT_3) / 6;
		self::$G22 = self::$G2 * 2.0 - 1;
		self::$F3 = 1.0 / 3.0;
		self::$G3 = 1.0 / 6.0;
		self::$F4 = (self::$SQRT_5 - 1.0) / 4.0;
		self::$G4 = (5.0 - self::$SQRT_5) / 20.0;
		self::$G42 = self::$G4 * 2.0;
		self::$G43 = self::$G4 * 3.0;
		self::$G44 = self::$G4 * 4.0 - 1.0;
	}

	protected static function dot2D($g, $x, $y){
		return $g[0] * $x + $g[1] * $y;
	}

	protected static function dot3D($g, $x, $y, $z){
		return $g[0] * $x + $g[1] * $y + $g[2] * $z;
	}

	protected static function dot4D($g, $x, $y, $z, $w){
		return $g[0] * $x + $g[1] * $y + $g[2] * $z + $g[3] * $w;
	}

	public function getNoise3D($x, $y, $z){
		$x += $this->offsetX;
		$y += $this->offsetY;
		$z += $this->offsetZ;

		
		$s = ($x + $y + $z) * self::$F3; 
		$i = (int) ($x + $s);
		$j = (int) ($y + $s);
		$k = (int) ($z + $s);
		$t = ($i + $j + $k) * self::$G3;
		
		$x0 = $x - ($i - $t); 
		$y0 = $y - ($j - $t);
		$z0 = $z - ($k - $t);

		

		
		if($x0 >= $y0){
			if($y0 >= $z0){
				$i1 = 1;
				$j1 = 0;
				$k1 = 0;
				$i2 = 1;
				$j2 = 1;
				$k2 = 0;
			} 
			elseif($x0 >= $z0){
				$i1 = 1;
				$j1 = 0;
				$k1 = 0;
				$i2 = 1;
				$j2 = 0;
				$k2 = 1;
			} 
			else{
				$i1 = 0;
				$j1 = 0;
				$k1 = 1;
				$i2 = 1;
				$j2 = 0;
				$k2 = 1;
			}
			
		}else{ 
			if($y0 < $z0){
				$i1 = 0;
				$j1 = 0;
				$k1 = 1;
				$i2 = 0;
				$j2 = 1;
				$k2 = 1;
			} 
			elseif($x0 < $z0){
				$i1 = 0;
				$j1 = 1;
				$k1 = 0;
				$i2 = 0;
				$j2 = 1;
				$k2 = 1;
			} 
			else{
				$i1 = 0;
				$j1 = 1;
				$k1 = 0;
				$i2 = 1;
				$j2 = 1;
				$k2 = 0;
			}
			
		}

		
		
		
		
		$x1 = $x0 - $i1 + self::$G3; 
		$y1 = $y0 - $j1 + self::$G3;
		$z1 = $z0 - $k1 + self::$G3;
		$x2 = $x0 - $i2 + 2.0 * self::$G3; 
		$y2 = $y0 - $j2 + 2.0 * self::$G3;
		$z2 = $z0 - $k2 + 2.0 * self::$G3;
		$x3 = $x0 - 1.0 + 3.0 * self::$G3; 
		$y3 = $y0 - 1.0 + 3.0 * self::$G3;
		$z3 = $z0 - 1.0 + 3.0 * self::$G3;

		
		$ii = $i & 255;
		$jj = $j & 255;
		$kk = $k & 255;

		$n = 0;

		
		$t0 = 0.6 - $x0 * $x0 - $y0 * $y0 - $z0 * $z0;
		if($t0 > 0){
			$gi0 = self::$grad3[$this->perm[$ii + $this->perm[$jj + $this->perm[$kk]]] % 12];
			$n += $t0 * $t0 * $t0 * $t0 * ($gi0[0] * $x0 + $gi0[1] * $y0 + $gi0[2] * $z0);
		}

		$t1 = 0.6 - $x1 * $x1 - $y1 * $y1 - $z1 * $z1;
		if($t1 > 0){
			$gi1 = self::$grad3[$this->perm[$ii + $i1 + $this->perm[$jj + $j1 + $this->perm[$kk + $k1]]] % 12];
			$n += $t1 * $t1 * $t1 * $t1 * ($gi1[0] * $x1 + $gi1[1] * $y1 + $gi1[2] * $z1);
		}

		$t2 = 0.6 - $x2 * $x2 - $y2 * $y2 - $z2 * $z2;
		if($t2 > 0){
			$gi2 = self::$grad3[$this->perm[$ii + $i2 + $this->perm[$jj + $j2 + $this->perm[$kk + $k2]]] % 12];
			$n += $t2 * $t2 * $t2 * $t2 * ($gi2[0] * $x2 + $gi2[1] * $y2 + $gi2[2] * $z2);
		}

		$t3 = 0.6 - $x3 * $x3 - $y3 * $y3 - $z3 * $z3;
		if($t3 > 0){
			$gi3 = self::$grad3[$this->perm[$ii + 1 + $this->perm[$jj + 1 + $this->perm[$kk + 1]]] % 12];
			$n += $t3 * $t3 * $t3 * $t3 * ($gi3[0] * $x3 + $gi3[1] * $y3 + $gi3[2] * $z3);
		}

		
		
		return 32.0 * $n;
	}

	public function getNoise2D($x, $y){
		$x += $this->offsetX;
		$y += $this->offsetY;

		
		$s = ($x + $y) * self::$F2; 
		$i = (int) ($x + $s);
		$j = (int) ($y + $s);
		$t = ($i + $j) * self::$G2;
		
		$x0 = $x - ($i - $t); 
		$y0 = $y - ($j - $t);

		

		
		if($x0 > $y0){
			$i1 = 1;
			$j1 = 0;
		} 
		else{
			$i1 = 0;
			$j1 = 1;
		}
		

		
		
		

		$x1 = $x0 - $i1 + self::$G2; 
		$y1 = $y0 - $j1 + self::$G2;
		$x2 = $x0 + self::$G22; 
		$y2 = $y0 + self::$G22;

		
		$ii = $i & 255;
		$jj = $j & 255;

		$n = 0;

		
		$t0 = 0.5 - $x0 * $x0 - $y0 * $y0;
		if($t0 > 0){
			$gi0 = self::$grad3[$this->perm[$ii + $this->perm[$jj]] % 12];
			$n += $t0 * $t0 * $t0 * $t0 * ($gi0[0] * $x0 + $gi0[1] * $y0); 
		}

		$t1 = 0.5 - $x1 * $x1 - $y1 * $y1;
		if($t1 > 0){
			$gi1 = self::$grad3[$this->perm[$ii + $i1 + $this->perm[$jj + $j1]] % 12];
			$n += $t1 * $t1 * $t1 * $t1 * ($gi1[0] * $x1 + $gi1[1] * $y1);
		}

		$t2 = 0.5 - $x2 * $x2 - $y2 * $y2;
		if($t2 > 0){
			$gi2 = self::$grad3[$this->perm[$ii + 1 + $this->perm[$jj + 1]] % 12];
			$n += $t2 * $t2 * $t2 * $t2 * ($gi2[0] * $x2 + $gi2[1] * $y2);
		}

		
		
		return 70.0 * $n;
	}

	
	
}