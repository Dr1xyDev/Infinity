<?php

/*
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.0
 *               InfinityProject By @Dr1xyDev
 *   YT:         @Dr1xyDev
 *   GitHub:     github.com/Dr1xyDev/Infinity
*/

namespace pocketmine\level\generator\object;

use pocketmine\block\Block;
use pocketmine\level\ChunkManager;
use pocketmine\math\Vector3;
use pocketmine\utils\Random;

abstract class HugeTree extends CustomTree{

	/** @var int Base height of the tree */
	protected $baseHeight;

	/** @var int Extra random height added to base */
	protected $extraRandomHeight;

	/** @var Block Wood block type with metadata */
	protected $woodMetadata;

	/** @var Block Leaves block type with metadata */
	protected $leavesMetadata;

	/**
	 * @param int $baseHeightIn Base height
	 * @param int $extraRandomHeight Extra random height
	 * @param Block $woodMetadata Wood block
	 * @param Block $leavesMetadata Leaves block
	 */
	public function __construct($baseHeightIn, $extraRandomHeight, Block $woodMetadata, Block $leavesMetadata){
		$this->baseHeight = $baseHeightIn;
		$this->extraRandomHeight = $extraRandomHeight;
		$this->woodMetadata = $woodMetadata;
		$this->leavesMetadata = $leavesMetadata;
	}

	/**
	 * Calculates the final height of the tree.
	 *
	 * @param Random $rand Random number generator
	 * @return int The calculated height
	 */
	protected function getHeight(Random $rand) : int{
		return $this->baseHeight + $rand->nextBoundedInt($this->extraRandomHeight);
	}

	/**
	 * Checks if there is enough space for the tree to grow.
	 *
	 * @param ChunkManager $level The world
	 * @param Random $rand Random number generator
	 * @param Vector3 $position Base position
	 * @param int $height The intended tree height
	 * @return bool True if the tree can grow
	 */
	protected function ensureGrowable(ChunkManager $level, Random $rand, Vector3 $position, int $height) : bool{
		// Check blocks above the base position for clear space
		for($y = $position->getY(); $y <= $position->getY() + 1 + $height; ++$y){
			$radius = 1;
			if($y === $position->getY()){
				$radius = 0;
			}
			if($y >= $position->getY() + 1 + $height - 2){
				$radius = 2;
			}

			for($x = $position->getX() - $radius; $x <= $position->getX() + $radius; ++$x){
				for($z = $position->getZ() - $radius; $z <= $position->getZ() + $radius; ++$z){
					if($y < 0 || $y >= 256){
						return false;
					}
					$block = Block::get($level->getBlockIdAt($x, $y, $z));
					if(!$this->canOverride($block)){
						return false;
					}
				}
			}
		}

		// Check ground block
		$down = $position->getSide(Vector3::SIDE_DOWN);
		$groundBlock = $level->getBlockIdAt($down->getFloorX(), $down->getFloorY(), $down->getFloorZ());
		if($groundBlock !== Block::GRASS && $groundBlock !== Block::DIRT && $groundBlock !== Block::FARMLAND){
			return false;
		}

		return true;
	}

	/**
	 * Creates the crown (top leaves) of the tree.
	 *
	 * @param ChunkManager $level The world
	 * @param Vector3 $pos The top of the trunk position
	 * @param int $radius The radius of the crown
	 */
	protected function createCrown(ChunkManager $level, Vector3 $pos, int $radius){
		for($y = -2; $y <= 0; ++$y){
			$this->growLeavesLayerStrict($level, $pos->getSide(Vector3::SIDE_UP, $y), $radius + 1 - $y);
		}
	}

	/**
	 * Grows a layer of leaves with rounded edges.
	 *
	 * @param ChunkManager $level The world
	 * @param Vector3 $pos Center position of the layer
	 * @param int $radius Radius of the leaves layer
	 */
	protected function growLeavesLayer(ChunkManager $level, Vector3 $pos, int $radius){
		for($x = $pos->getX() - $radius; $x <= $pos->getX() + $radius; ++$x){
			for($z = $pos->getZ() - $radius; $z <= $pos->getZ() + $radius; ++$z){
				$xOff = abs($x - $pos->getX());
				$zOff = abs($z - $pos->getZ());
				if($xOff + $zOff <= $radius){
					$blockPos = new Vector3($x, $pos->getY(), $z);
					$id = $level->getBlockIdAt($blockPos->getFloorX(), $blockPos->getFloorY(), $blockPos->getFloorZ());
					if($id === Block::AIR || $id === Block::LEAVES){
						$this->setBlockAndNotifyAdequately($level, $blockPos, $this->leavesMetadata);
					}
				}
			}
		}
	}

	/**
	 * Grows a strict (square) layer of leaves.
	 *
	 * @param ChunkManager $level The world
	 * @param Vector3 $pos Center position of the layer
	 * @param int $radius Radius of the leaves layer
	 */
	protected function growLeavesLayerStrict(ChunkManager $level, Vector3 $pos, int $radius){
		for($x = $pos->getX() - $radius; $x <= $pos->getX() + $radius; ++$x){
			for($z = $pos->getZ() - $radius; $z <= $pos->getZ() + $radius; ++$z){
				$blockPos = new Vector3($x, $pos->getY(), $z);
				$id = $level->getBlockIdAt($blockPos->getFloorX(), $blockPos->getFloorY(), $blockPos->getFloorZ());
				if($id === Block::AIR || $id === Block::LEAVES){
					$this->setBlockAndNotifyAdequately($level, $blockPos, $this->leavesMetadata);
				}
			}
		}
	}
}
