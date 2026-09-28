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

namespace pocketmine\level\generator\vanilla\level\generator\feature;

use pocketmine\level\generator\vanilla\block\BlockFactory;
use pocketmine\level\generator\vanilla\level\ChunkManager;
use pocketmine\level\generator\vanilla\level\format\Chunk;
use pocketmine\level\generator\vanilla\level\generator\feature\configurations\OreConfiguration;
use pocketmine\level\generator\vanilla\level\generator\MathHelper;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\serializer\BitSet;
use pocketmine\level\generator\vanilla\utils\Random;
use function ceil;
use function cos;
use function floor;
use function max;
use function sin;
use const M_PI;

class OreFeature extends Feature {

	public function __construct(
		public OreConfiguration $config
	){}

	public function place(FeaturePlaceContext $context) : bool{
		$origin = $context->origin();
		$level = $context->level();
		$random = $context->random();
		$config = $this->config;

		$dir = $random->nextFloat() * M_PI;
		$spreadXY = $config->size / 8.0;
		$maxRadius = ceil(($config->size / 16.0 * 2.0 + 1.0) / 2.0);
		$x0 = $origin->getX() + sin($dir) * $spreadXY;
		$x1 = $origin->getX() - sin($dir) * $spreadXY;
		$z0 = $origin->getZ() + cos($dir) * $spreadXY;
		$z1 = $origin->getZ() - cos($dir) * $spreadXY;
		$y0 = $origin->getY() + $random->nextBoundedInt(3) - 2;
		$y1 = $origin->getY() + $random->nextBoundedInt(3) - 2;
		$xStart = (int) ($origin->getX() - ceil($spreadXY) - $maxRadius);
		$yStart = (int) ($origin->getY() - 2 - $maxRadius);
		$zStart = (int) ($origin->getZ() - ceil($spreadXY) - $maxRadius);
		$sizeXZ = (int) (2 * (ceil($spreadXY) + $maxRadius));
		$sizeY = (int) (2 * (2 + $maxRadius));

		$canPlace = false;
		$lastCX = -1e9;
		$lastCZ = -1e9;
		$chunk = null;
		for ($xprobe = $xStart; $xprobe <= $xStart + $sizeXZ; $xprobe++) {
			$cx = $xprobe >> Chunk::COORD_BIT_SIZE;
			for ($zprobe = $zStart; $zprobe <= $zStart + $sizeXZ; $zprobe++) {
				$cz = $zprobe >> Chunk::COORD_BIT_SIZE;
				if ($cx !== $lastCX || $cz !== $lastCZ) {
					$chunk = $level->getChunk($cx, $cz);
					$lastCX = $cx;
					$lastCZ = $cz;
				}
				if ($chunk !== null && $yStart <= $this->getHighestWorkableBlock($chunk, $xprobe & Chunk::COORD_MASK, $zprobe & Chunk::COORD_MASK)) {
					$canPlace = true;
					break 2;
				}
			}
		}

		if ($canPlace) {
			return $this->doPlace($level, $random, $config, $x0, $x1, $z0, $z1, $y0, $y1, $xStart, $yStart, $zStart, $sizeXZ, $sizeY);
		}

		return false;
	}

	protected function doPlace(
		ChunkManager $level,
		Random $random,
		OreConfiguration $config,
		float $x0,
		float $x1,
		float $z0,
		float $z1,
		float $y0,
		float $y1,
		int $xStart,
		int $yStart,
		int $zStart,
		int $sizeXZ,
		int $sizeY
	) : bool
	{
		$placed = 0;
		$size = $config->size;
		$tested = new BitSet($sizeXZ * $sizeY * $sizeXZ);
		$data = [];

		for ($i = 0; $i < $size; $i++) {
			$step = (float) $i / $size;
			$xx = MathHelper::lerp($step, $x0, $x1);
			$yy = MathHelper::lerp($step, $y0, $y1);
			$zz = MathHelper::lerp($step, $z0, $z1);
			$ss = $random->nextFloat() * $size / 16.0;
			$r = ((sin(M_PI * $step) + 1.0) * $ss + 1.0) / 2.0;
			$data[] = $xx;
			$data[] = $yy;
			$data[] = $zz;
			$data[] = $r;
		}

		for ($i1 = 0; $i1 < $size - 1; $i1++) {
			$idx1 = $i1 << 2;
			if ($data[$idx1 + 3] <= 0.0) continue;
			for ($i2 = $i1 + 1; $i2 < $size; $i2++) {
				$idx2 = $i2 << 2;
				if ($data[$idx2 + 3] <= 0.0) continue;
				$dx = $data[$idx1] - $data[$idx2];
				$dy = $data[$idx1 + 1] - $data[$idx2 + 1];
				$dz = $data[$idx1 + 2] - $data[$idx2 + 2];
				$dr = $data[$idx1 + 3] - $data[$idx2 + 3];
				if ($dr * $dr > $dx * $dx + $dy * $dy + $dz * $dz) {
					if ($dr > 0.0) {
						$data[$idx2 + 3] = -1.0;
					} else {
						$data[$idx1 + 3] = -1.0;
						break;
					}
				}
			}
		}

		$target = $config->target;
		$state = $config->state;
		$chunkCache = [];

		for ($i = 0; $i < $size; $i++) {
			$idx = $i << 2;
			$r = $data[$idx + 3];
			if ($r < 0.0) continue;

			$xx = $data[$idx];
			$yy = $data[$idx + 1];
			$zz = $data[$idx + 2];
			$xMin = (int) max(floor($xx - $r), $xStart);
			$yMin = (int) max(floor($yy - $r), $yStart);
			$zMin = (int) max(floor($zz - $r), $zStart);
			$xMax = (int) max(floor($xx + $r), $xMin);
			$yMax = (int) max(floor($yy + $r), $yMin);
			$zMax = (int) max(floor($zz + $r), $zMin);

			for ($x = $xMin; $x <= $xMax; $x++) {
				$xd = ($x + 0.5 - $xx) / $r;
				$xd2 = $xd * $xd;
				if ($xd2 >= 1.0) continue;

				$cx = $x >> Chunk::COORD_BIT_SIZE;
				$rx = $x & Chunk::COORD_MASK;

				for ($y = $yMin; $y <= $yMax; $y++) {
					$yd = ($y + 0.5 - $yy) / $r;
					$xyd2 = $xd2 + $yd * $yd;
					if ($xyd2 >= 1.0) continue;

					for ($z = $zMin; $z <= $zMax; $z++) {
						$zd = ($z + 0.5 - $zz) / $r;
						if ($xyd2 + $zd * $zd >= 1.0) continue;

						$bitSetIndex = ($x - $xStart) + ($y - $yStart) * $sizeXZ + ($z - $zStart) * $sizeXZ * $sizeY;
						if ($tested->get($bitSetIndex)) continue;
						$tested->set($bitSetIndex, true);

						$cz = $z >> Chunk::COORD_BIT_SIZE;
						$rz = $z & Chunk::COORD_MASK;

						$chunkIdx = ($cx << 32) | $cz;
						$chunk = $chunkCache[$chunkIdx] ??= $level->getChunk($cx, $cz);
						if ($chunk === null) continue;

						$fullId = $chunk->getFullBlock($rx, $y, $rz);
						$block = BlockFactory::fromFullBlock($fullId);
						if ($target->test($block, $random)) {
							$chunk->setFullBlock($rx, $y, $rz, $state->getFullId());
							++$placed;
						}
					}
				}
			}
		}

		return $placed > 0;
	}

	private function getHighestWorkableBlock(Chunk $chunk, int $rx, int $rz) : int{
		$highestBlock = $chunk->getHighestBlockAt($rx, $rz);
		if($highestBlock === -1){
			return -1;
		}

		for($y = $highestBlock; $y >= 0; --$y){
			$fullId = $chunk->getFullBlock($rx, $y, $rz);
			if (BlockFactory::$lightFilter[$fullId] > 1) { //solid
				return $y + 1;
			}
		}

		return -1;
	}
}
