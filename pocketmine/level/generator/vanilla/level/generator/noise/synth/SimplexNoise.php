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

namespace pocketmine\level\generator\vanilla\level\generator\noise\synth;

use pocketmine\level\generator\vanilla\utils\Random;
use function array_fill;

class SimplexNoise {
	private const SQRT3 = 1.7320508075688772;
	private const F2 = 0.5 * (self::SQRT3 - 1.0);
	private const G2 = (3.0 - self::SQRT3) / 6.0;
	private const F3 = 1.0 / 3.0;
	private const G3 = 1.0 / 6.0;

	private const GRAD3 = [
		[1, 1, 0], [-1, 1, 0], [1, -1, 0], [-1, -1, 0],
		[1, 0, 1], [-1, 0, 1], [1, 0, -1], [-1, 0, -1],
		[0, 1, 1], [0, -1, 1], [0, 1, -1], [0, -1, -1]
	];

	/** @var float[] */
	protected array $mOrigin = [];

	/** @var int[] */
	protected array $mNoiseMap = [];

	public function __construct(Random $random){
		$originX = $random->nextFloat() * 256.0;
		$originY = $random->nextFloat() * 256.0;
		$originZ = $random->nextFloat() * 256.0;

		$this->mOrigin = [$originX, $originY, $originZ];

		$this->mNoiseMap = array_fill(0, 512, 0);

		for ($i = 0; $i < 256; $i++) {
			$this->mNoiseMap[$i] = $i;
		}

		for ($i = 0; $i < 256; $i++) {
			$j = $random->nextBoundedInt(256 - $i) + $i;
			$tmp = $this->mNoiseMap[$i];

			$this->mNoiseMap[$i] = $this->mNoiseMap[$j];
			$this->mNoiseMap[$j] = $tmp;

			$this->mNoiseMap[$i + 256] = $this->mNoiseMap[$i];
		}
	}

	protected function _fastFloor(float $x) : int{
		$xi = (int) $x;
		return $x < $xi ? $xi - 1 : $xi;
	}

	public function getValue2D(float $xin, float $yin) : float{
		$s = ($xin + $yin) * self::F2;
		$i = $this->_fastFloor($xin + $s);
		$j = $this->_fastFloor($yin + $s);

		$t = ($i + $j) * self::G2;

		$X0 = $i - $t;
		$Y0 = $j - $t;

		$x0 = $xin - $X0;
		$y0 = $yin - $Y0;

		if ($x0 > $y0) {
			$i1 = 1; $j1 = 0;
		} else {
			$i1 = 0; $j1 = 1;
		}

		$x1 = $x0 - $i1 + self::G2;
		$y1 = $y0 - $j1 + self::G2;

		$x2 = $x0 - 1.0 + 2.0 * self::G2;
		$y2 = $y0 - 1.0 + 2.0 * self::G2;

		$ii = $i & 255;
		$jj = $j & 255;

		$gi0 = $this->mNoiseMap[$ii + $this->mNoiseMap[$jj]] % 12;
		$gi1 = $this->mNoiseMap[$ii + $i1 + $this->mNoiseMap[$jj + $j1]] % 12;
		$gi2 = $this->mNoiseMap[$ii + 1 + $this->mNoiseMap[$jj + 1]] % 12;

		$t0 = 0.5 - $x0 * $x0 - $y0 * $y0;
		if ($t0 < 0) {
			$n0 = 0.0;
		} else {
			$t0 *= $t0;
			$n0 = $t0 * $t0 * (self::GRAD3[$gi0][0] * $x0 + self::GRAD3[$gi0][1] * $y0);
		}

		$t1 = 0.5 - $x1 * $x1 - $y1 * $y1;
		if ($t1 < 0) {
			$n1 = 0.0;
		} else {
			$t1 *= $t1;
			$n1 = $t1 * $t1 * (self::GRAD3[$gi1][0] * $x1 + self::GRAD3[$gi1][1] * $y1);
		}

		$t2 = 0.5 - $x2 * $x2 - $y2 * $y2;
		if ($t2 < 0) {
			$n2 = 0.0;
		} else {
			$t2 *= $t2;
			$n2 = $t2 * $t2 * (self::GRAD3[$gi2][0] * $x2 + self::GRAD3[$gi2][1] * $y2);
		}

		return 70.0 * ($n0 + $n1 + $n2);
	}

	public function getValue3D(float $xin, float $yin, float $zin) : float{
		$s = ($xin + $yin + $zin) * self::F3;
		$i = $this->_fastFloor($xin + $s);
		$j = $this->_fastFloor($yin + $s);
		$k = $this->_fastFloor($zin + $s);

		$t = ($i + $j + $k) * self::G3;

		$X0 = $i - $t;
		$Y0 = $j - $t;
		$Z0 = $k - $t;

		$x0 = $xin - $X0;
		$y0 = $yin - $Y0;
		$z0 = $zin - $Z0;

		if ($x0 >= $y0) {
			if ($y0 >= $z0) {
				$i1 = 1; $j1 = 0; $k1 = 0; $i2 = 1; $j2 = 1; $k2 = 0;
			} elseif ($x0 >= $z0) {
				$i1 = 1; $j1 = 0; $k1 = 0; $i2 = 1; $j2 = 0; $k2 = 1;
			} else {
				$i1 = 0; $j1 = 0; $k1 = 1; $i2 = 1; $j2 = 0; $k2 = 1;
			}
		} else {
			if ($y0 < $z0) {
				$i1 = 0; $j1 = 0; $k1 = 1; $i2 = 0; $j2 = 1; $k2 = 1;
			} elseif ($x0 < $z0) {
				$i1 = 0; $j1 = 1; $k1 = 0; $i2 = 0; $j2 = 1; $k2 = 1;
			} else {
				$i1 = 0; $j1 = 1; $k1 = 0; $i2 = 1; $j2 = 1; $k2 = 0;
			}
		}

		$x1 = $x0 - $i1 + self::G3;
		$y1 = $y0 - $j1 + self::G3;
		$z1 = $z0 - $k1 + self::G3;

		$x2 = $x0 - $i2 + 2.0 * self::G3;
		$y2 = $y0 - $j2 + 2.0 * self::G3;
		$z2 = $z0 - $k2 + 2.0 * self::G3;

		$x3 = $x0 - 1.0 + 3.0 * self::G3;
		$y3 = $y0 - 1.0 + 3.0 * self::G3;
		$z3 = $z0 - 1.0 + 3.0 * self::G3;

		$ii = $i & 255;
		$jj = $j & 255;
		$kk = $k & 255;

		$gi0 = $this->mNoiseMap[$ii + $this->mNoiseMap[$jj + $this->mNoiseMap[$kk]]] % 12;
		$gi1 = $this->mNoiseMap[$ii + $i1 + $this->mNoiseMap[$jj + $j1 + $this->mNoiseMap[$kk + $k1]]] % 12;
		$gi2 = $this->mNoiseMap[$ii + $i2 + $this->mNoiseMap[$jj + $j2 + $this->mNoiseMap[$kk + $k2]]] % 12;
		$gi3 = $this->mNoiseMap[$ii + 1 + $this->mNoiseMap[$jj + 1 + $this->mNoiseMap[$kk + 1]]] % 12;

		$t0 = 0.6 - $x0 * $x0 - $y0 * $y0 - $z0 * $z0;
		if ($t0 < 0) {
			$n0 = 0.0;
		} else {
			$t0 *= $t0;
			$n0 = $t0 * $t0 * (self::GRAD3[$gi0][0] * $x0 + self::GRAD3[$gi0][1] * $y0 + self::GRAD3[$gi0][2] * $z0);
		}

		$t1 = 0.6 - $x1 * $x1 - $y1 * $y1 - $z1 * $z1;
		if ($t1 < 0) {
			$n1 = 0.0;
		} else {
			$t1 *= $t1;
			$n1 = $t1 * $t1 * (self::GRAD3[$gi1][0] * $x1 + self::GRAD3[$gi1][1] * $y1 + self::GRAD3[$gi1][2] * $z1);
		}

		$t2 = 0.6 - $x2 * $x2 - $y2 * $y2 - $z2 * $z2;
		if ($t2 < 0) {
			$n2 = 0.0;
		} else {
			$t2 *= $t2;
			$n2 = $t2 * $t2 * (self::GRAD3[$gi2][0] * $x2 + self::GRAD3[$gi2][1] * $y2 + self::GRAD3[$gi2][2] * $z2);
		}

		$t3 = 0.6 - $x3 * $x3 - $y3 * $y3 - $z3 * $z3;
		if ($t3 < 0) {
			$n3 = 0.0;
		} else {
			$t3 *= $t3;
			$n3 = $t3 * $t3 * (self::GRAD3[$gi3][0] * $x3 + self::GRAD3[$gi3][1] * $y3 + self::GRAD3[$gi3][2] * $z3);
		}

		return 32.0 * ($n0 + $n1 + $n2 + $n3);
	}

	public function add2D(array &$buffer, float $_x, float $_y, int $xSize, int $ySize, float $xs, float $ys, float $pow) : void{
		$pp = 0;
		$originX = $this->mOrigin[0];
		$originY = $this->mOrigin[1];

		for ($yy = 0; $yy < $ySize; $yy++) {
			$yin = ($_y + $yy) * $ys + $originY;

			for ($xx = 0; $xx < $xSize; $xx++) {
				$xin = ($_x + $xx) * $xs + $originX;
				$buffer[$pp++] += $this->getValue2D($xin, $yin) * $pow;
			}
		}
	}

	public function add3D(array &$buffer, float $vinX, float $vinY, float $vinZ, int $xSize, int $ySize, int $zSize, float $scaleX, float $scaleY, float $scaleZ, float $pow) : void{
		$pp = 0;
		$originX = $this->mOrigin[0];
		$originY = $this->mOrigin[1];
		$originZ = $this->mOrigin[2];

		for ($xx = 0; $xx < $xSize; $xx++) {
			$xin = ($vinX + $xx) * $scaleX + $originX;

			for ($zz = 0; $zz < $zSize; $zz++) {
				$zin = ($vinZ + $zz) * $scaleZ + $originZ;

				for ($yy = 0; $yy < $ySize; $yy++) {
					$yin = ($vinY + $yy) * $scaleY + $originY;
					$buffer[$pp++] += $this->getValue3D($xin, $yin, $zin) * $pow;
				}
			}
		}
	}
}
