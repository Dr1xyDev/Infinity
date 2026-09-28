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

class ImprovedNoise
{
	/** @var int[] */
	protected array $mNoiseMap = [];

	protected float $originX;
	protected float $originY;
	protected float $originZ;

	public function __construct(Random $random){
		$this->originX = $random->nextFloat() * 256.0;
		$this->originY = $random->nextFloat() * 256.0;
		$this->originZ = $random->nextFloat() * 256.0;

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

	protected function lerp(float $t, float $a, float $b) : float{
		return $a + $t * ($b - $a);
	}

	protected function grad2(int $hash, float $x, float $z) : float{
		$h = $hash & 15;
		$u = (1.0 - (($h & 8) >> 3)) * $x;
		$v = ($h < 4) ? 0.0 : (($h === 12 || $h === 14) ? $x : $z);

		return (($h & 1) === 0 ? $u : -$u) + (($h & 2) === 0 ? $v : -$v);
	}

	protected function grad(int $hash, float $x, float $y, float $z) : float{
		$h = $hash & 15;
		$u = ($h < 8) ? $x : $y;
		$v = ($h < 4) ? $y : (($h === 12 || $h === 14) ? $x : $z);

		return (($h & 1) === 0 ? $u : -$u) + (($h & 2) === 0 ? $v : -$v);
	}

	protected function calcValues(float $val, int &$index, float &$lerp) : float{
		$vf = (int) $val;
		if ($val < $vf) {
			$vf--;
		}
		$index = $vf & 255;
		$val -= $vf;

		$lerp = $val * $val * $val * ($val * ($val * 6.0 - 15.0) + 10.0);

		return $val;
	}

	protected function blendCubeCorners(
		float $ox, float $oy, float $oz,
		int   $X, int $Y, int $Z,
		float $u,
		float &$vv0, float &$vv1, float &$vv2, float &$vv3
	) : void
	{
		$A = $this->mNoiseMap[$X] + $Y;
		$AA = $this->mNoiseMap[$A] + $Z;
		$AB = $this->mNoiseMap[$A + 1] + $Z;

		$B = $this->mNoiseMap[$X + 1] + $Y;
		$BA = $this->mNoiseMap[$B] + $Z;
		$BB = $this->mNoiseMap[$B + 1] + $Z;

		$vv0 = $this->lerp($u,
			$this->grad($this->mNoiseMap[$AA], $ox, $oy, $oz),
			$this->grad($this->mNoiseMap[$BA], $ox - 1.0, $oy, $oz)
		);

		$vv1 = $this->lerp($u,
			$this->grad($this->mNoiseMap[$AB], $ox, $oy - 1.0, $oz),
			$this->grad($this->mNoiseMap[$BB], $ox - 1.0, $oy - 1.0, $oz)
		);

		$vv2 = $this->lerp($u,
			$this->grad($this->mNoiseMap[$AA + 1], $ox, $oy, $oz - 1.0),
			$this->grad($this->mNoiseMap[$BA + 1], $ox - 1.0, $oy, $oz - 1.0)
		);

		$vv3 = $this->lerp($u,
			$this->grad($this->mNoiseMap[$AB + 1], $ox, $oy - 1.0, $oz - 1.0),
			$this->grad($this->mNoiseMap[$BB + 1], $ox - 1.0, $oy - 1.0, $oz - 1.0)
		);
	}

	public function getValue(float $x, float $y, float $z) : float{
		$px = $x + $this->originX;
		$py = $y + $this->originY;
		$pz = $z + $this->originZ;

		$X = 0;
		$Y = 0;
		$Z = 0;
		$u = 0.0;
		$v = 0.0;
		$w = 0.0;

		$px = $this->calcValues($px, $X, $u);
		$py = $this->calcValues($py, $Y, $v);
		$pz = $this->calcValues($pz, $Z, $w);

		$vv0 = 0.0;
		$vv1 = 0.0;
		$vv2 = 0.0;
		$vv3 = 0.0;
		$this->blendCubeCorners($px, $py, $pz, $X, $Y, $Z, $u, $vv0, $vv1, $vv2, $vv3);

		return $this->lerp($w, $this->lerp($v, $vv0, $vv1), $this->lerp($v, $vv2, $vv3));
	}

	/**
	 * @param float[] $buffer
	 */
	public function readArea(
		array &$buffer,
		float $posX, float $posY, float $posZ,
		int   $xSize, int $ySize, int $zSize,
		float $sx, float $sy, float $sz,
		float $pow
	) : void{
		$pp = 0;
		$scale = 1.0 / $pow;

		if ($ySize === 1) {
			for ($xx = 0; $xx < $xSize; $xx++) {
				$X = 0;
				$u = 0.0;
				$x = ($posX + $xx) * $sx + $this->originX;
				$x = $this->calcValues($x, $X, $u);

				for ($zz = 0; $zz < $zSize; $zz++) {
					$Z = 0;
					$w = 0.0;
					$z = ($posZ + $zz) * $sz + $this->originZ;
					$z = $this->calcValues($z, $Z, $w);

					$A = $this->mNoiseMap[$X] + 0;
					$AA = $this->mNoiseMap[$A] + $Z;

					$B = $this->mNoiseMap[$X + 1] + 0;
					$BA = $this->mNoiseMap[$B] + $Z;

					$vv0 = $this->lerp($u,
						$this->grad2($this->mNoiseMap[$AA], $x, $z),
						$this->grad($this->mNoiseMap[$BA], $x - 1.0, 0.0, $z)
					);

					$vv2 = $this->lerp($u,
						$this->grad($this->mNoiseMap[$AA + 1], $x, 0.0, $z - 1.0),
						$this->grad($this->mNoiseMap[$BA + 1], $x - 1.0, 0.0, $z - 1.0)
					);

					$val = $this->lerp($w, $vv0, $vv2);
					$buffer[$pp++] += $val * $scale;
				}
			}
			return;
		}

		$yOld = -1;
		$vv0 = 0.0;
		$vv1 = 0.0;
		$vv2 = 0.0;
		$vv3 = 0.0;

		for ($xx = 0; $xx < $xSize; $xx++) {
			$X = 0;
			$u = 0.0;
			$x = ($posX + $xx) * $sx + $this->originX;
			$x = $this->calcValues($x, $X, $u);

			for ($zz = 0; $zz < $zSize; $zz++) {
				$Z = 0;
				$w = 0.0;
				$z = ($posZ + $zz) * $sz + $this->originZ;
				$z = $this->calcValues($z, $Z, $w);

				for ($yy = 0; $yy < $ySize; $yy++) {
					$Y = 0;
					$v = 0.0;
					$y = ($posY + $yy) * $sy + $this->originY;
					$y = $this->calcValues($y, $Y, $v);

					if ($yy === 0 || $Y !== $yOld) {
						$yOld = $Y;
						$this->blendCubeCorners($x, $y, $z, $X, $Y, $Z, $u, $vv0, $vv1, $vv2, $vv3);
					}

					$v0 = $this->lerp($v, $vv0, $vv1);
					$v1 = $this->lerp($v, $vv2, $vv3);
					$val = $this->lerp($w, $v0, $v1);

					$buffer[$pp++] += $val * $scale;
				}
			}
		}
	}

	public function hashCode() : int{
		$x = 4711;

		for ($i = 0; $i < 512; ++$i) {
			$x = $x * 37 + $this->mNoiseMap[$i];
		}

		return $x;
	}
}
