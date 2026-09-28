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

namespace pocketmine\level\generator\vanilla\block\utils\pattern;

use pocketmine\level\generator\vanilla\block\Block;
use pocketmine\level\generator\vanilla\level\Level;
use pocketmine\level\generator\vanilla\math\Facing;
use pocketmine\level\generator\vanilla\math\Vector3;

use function count;
use function max;

class BlockPattern{

	/** @var array<int, array<int, array<int, callable(Block): bool>>> */
	private array $blockMatches;

	private int $fingerLength;
	private int $thumbLength;
	private int $palmLength;

	/**
	 * @param array<int, array<int, array<int, callable(Block): bool>>> $blockMatches
	 */
	public function __construct(array $blockMatches){
		$this->blockMatches = $blockMatches;
		$this->fingerLength = count($blockMatches);

		if($this->fingerLength > 0){
			$this->thumbLength = count($blockMatches[0]);
			if($this->thumbLength > 0){
				$this->palmLength = count($blockMatches[0][0]);
			}else{
				$this->palmLength = 0;
			}
		}else{
			$this->thumbLength = 0;
			$this->palmLength = 0;
		}
	}

	public function getFingerLength() : int{
		return $this->fingerLength;
	}

	public function getThumbLength() : int{
		return $this->thumbLength;
	}

	public function getPalmLength() : int{
		return $this->palmLength;
	}

	public static function translateOffset(
		Vector3 $pos,
		int $finger,
		int $thumb,
		int $palmOffset,
		int $thumbOffset,
		int $fingerOffset
	) : Vector3{
		if($finger === $thumb || $finger === Facing::opposite($thumb)){
			throw new \InvalidArgumentException("Invalid forwards & up combination");
		}

		[$fx, $fy, $fz] = Facing::OFFSET[$finger];
		[$tx, $ty, $tz] = Facing::OFFSET[$thumb];

		$px = $fy * $tz - $fz * $ty;
		$py = $fz * $tx - $fx * $tz;
		$pz = $fx * $ty - $fy * $tx;

		return new Vector3(
			$pos->x + $tx * (-$thumbOffset) + $px * $palmOffset + $fx * $fingerOffset,
			$pos->y + $ty * (-$thumbOffset) + $py * $palmOffset + $fy * $fingerOffset,
			$pos->z + $tz * (-$thumbOffset) + $pz * $palmOffset + $fz * $fingerOffset
		);
	}

	/**
	 * @param array<string, Block> $cache
	 */
	private function checkPatternAt(
		Vector3 $pos,
		int     $finger,
		int     $thumb,
		Level   $level,
		array   &$cache
	) : ?PatternHelper{
		for($i = 0; $i < $this->palmLength; ++$i){
			for($j = 0; $j < $this->thumbLength; ++$j){
				for($k = 0; $k < $this->fingerLength; ++$k){
					$blockPos = self::translateOffset($pos, $finger, $thumb, $i, $j, $k);
					$key = (int) $blockPos->x . ':' . (int) $blockPos->y . ':' . (int) $blockPos->z;

					if(!isset($cache[$key])){
						$cache[$key] = $level->getBlockAt((int) $blockPos->x, (int) $blockPos->y, (int) $blockPos->z);
					}

					$block = $cache[$key];
					$predicate = $this->blockMatches[$k][$j][$i];

					if(!$predicate($block)){
						return null;
					}
				}
			}
		}

		return new PatternHelper($pos, $finger, $thumb, $level, $this->palmLength, $this->thumbLength, $this->fingerLength);
	}

	public function match(Level $level, Vector3 $pos) : ?PatternHelper{
		/** @var array<string, Block> $cache */
		$cache = [];

		$size = max($this->palmLength, $this->thumbLength, $this->fingerLength);

		for($dx = 0; $dx < $size; ++$dx){
			for($dy = 0; $dy < $size; ++$dy){
				for($dz = 0; $dz < $size; ++$dz){
					$checkPos = new Vector3($pos->x + $dx, $pos->y + $dy, $pos->z + $dz);

					foreach(Facing::ALL as $finger){
						foreach(Facing::ALL as $thumb){
							if($thumb === $finger || $thumb === Facing::opposite($finger)){
								continue;
							}

							$result = $this->checkPatternAt($checkPos, $finger, $thumb, $level, $cache);
							if($result !== null){
								return $result;
							}
						}
					}
				}
			}
		}

		return null;
	}
}
