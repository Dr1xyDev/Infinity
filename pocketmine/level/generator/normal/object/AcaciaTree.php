<?php

/*
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.0
 *               InfinityProject By @Dr1xyDev
 *   YT:         @Dr1xyDev
 *   GitHub:     github.com/Dr1xyDev/Infinity
*/

namespace pocketmine\level\generator\normal\object;

use pocketmine\level\generator\normal\math\FacingHelper;
use pocketmine\level\generator\object\CustomTree;
use pocketmine\block\Block;
use pocketmine\block\Leaves2;
use pocketmine\block\Wood2;
use pocketmine\level\ChunkManager;
use pocketmine\math\Vector3;
use pocketmine\utils\Random;

class AcaciaTree extends CustomTree
{

	public $trunkBlock = Block::WOOD2;
	public $leafBlock = Block::LEAVES2;
	public $blockMeta = Wood2::ACACIA;

	/**
	 * Generates a vanilla-like acacia tree with a crooked diagonal trunk,
	 * small branches extending into the canopy (like BigJungleTree),
	 * and proper sapling replacement.
	 *
	 * Vanilla acacia tree structure:
	 * - Straight trunk for the first few blocks from sapling position
	 * - Then bends diagonally in one horizontal direction for 1-3 blocks
	 * - Sometimes a second fork in a different direction near the top
	 * - Flat canopy layers around each bend/fork tip
	 * - Small branch logs (like BigJungleTree branches) extending 1-2
	 *   blocks outward from the trunk into the canopy
	 * - The sapling is ALWAYS replaced by the trunk at the base
	 *
	 * @param ChunkManager $worldIn The world
	 * @param Random $rand Random number generator
	 * @param Vector3 $position The base position (where the sapling is)
	 * @return bool Whether the tree was successfully generated
	 */
	public function generate(ChunkManager $worldIn, Random $rand, Vector3 $position) : bool
	{
		$x = $position->getFloorX();
		$y = $position->getFloorY();
		$z = $position->getFloorZ();

		// Tree height: 5 to 9 (vanilla range)
		$height = 5 + $rand->nextBoundedInt(2) + $rand->nextBoundedInt(3);

		// Check if there is enough space above
		if($y >= 1 && $y + $height + 1 <= 256){
			$flag = true;

			for($j = $y; $j <= $y + 1 + $height; ++$j){
				$radius = 1;

				if($j === $y){
					$radius = 0;
				}

				if($j >= $y + 1 + $height - 2){
					$radius = 2;
				}

				for($l = $x - $radius; $l <= $x + $radius && $flag; ++$l){
					for($i1 = $z - $radius; $i1 <= $z + $radius && $flag; ++$i1){
						if($j >= 0 && $j < 256){
							if(!$this->canOverride(Block::get($worldIn->getBlockIdAt($l, $j, $i1)))){
								$flag = false;
							}
						}else{
							$flag = false;
						}
					}
				}
			}

			if(!$flag){
				return false;
			}

			// Check ground block
			$groundBlock = $worldIn->getBlockIdAt($x, $y - 1, $z);
			if(($groundBlock !== Block::GRASS && $groundBlock !== Block::DIRT) || $y >= 256 - $height - 1){
				return false;
			}

			// Turn ground block into dirt (vanilla behavior)
			$worldIn->setBlockIdAt($x, $y - 1, $z, Block::DIRT);

			// FORCE replace the sapling block at the base position with trunk.
			// This ensures the sapling "border" never stays underneath.
			$this->placeLogAt($worldIn, new Vector3($x, $y, $z));

			// Choose the first bend direction
			$bendDir = FacingHelper::HORIZONTAL[$rand->nextBoundedInt(4)];
			// Height where the first bend starts (1-3 blocks above base)
			$bendStart = 1 + $rand->nextBoundedInt(3);
			// Number of diagonal steps (1-3)
			$bendLength = 1 + $rand->nextBoundedInt(2);

			// Build the trunk: straight part + diagonal bend
			// Track trunk positions so branches/fork can start from the correct trunk spot
			$curX = $x;
			$curZ = $z;
			$topY = $y;

			// Store trunk positions for branch and fork placement
			$trunkPositions = [];
			$trunkPositions[0] = [$x, $y, $z]; // base position

			for($trunkPos = 1; $trunkPos < $height; ++$trunkPos){
				$curY = $y + $trunkPos;

				// Apply diagonal offset during the bend phase
				if($trunkPos >= $bendStart && $bendLength > 0){
					$curX += FacingHelper::xOffset($bendDir);
					$curZ += FacingHelper::zOffset($bendDir);
					--$bendLength;
				}

				$this->placeLogAt($worldIn, new Vector3($curX, $curY, $curZ));
				$topY = $curY;
				$trunkPositions[$trunkPos] = [$curX, $curY, $curZ];
			}

			// Place main canopy at the top of the trunk
			$canopyPos = new Vector3($curX, $topY, $curZ);
			$this->placeCanopy($worldIn, $canopyPos, $rand, false);

			// Possibly add a second fork (top canopy) in a different direction
			// Vanilla acacia often has a second diagonal fork near the top
			$bendDir2 = FacingHelper::HORIZONTAL[$rand->nextBoundedInt(4)];

			if($bendDir2 !== $bendDir && count($trunkPositions) > 3){
				// Fork starts from a trunk position BEFORE the main bend,
				// ensuring the fork and main canopy are connected to the same trunk
				$forkStartIdx = max(1, $bendStart - 1 - $rand->nextBoundedInt(2));

				if(isset($trunkPositions[$forkStartIdx])){
					$forkBase = $trunkPositions[$forkStartIdx];
					$forkX = $forkBase[0];
					$forkZ = $forkBase[2];
					$forkLength = 1 + $rand->nextBoundedInt(3); // 1-3 blocks extending outward + upward
					$forkTopY = $forkBase[1];

					// Build the fork: each step goes 1 block up AND 1 block horizontally
					for($i = 1; $i <= $forkLength; ++$i){
						$forkY = $forkBase[1] + $i;
						$forkX += FacingHelper::xOffset($bendDir2);
						$forkZ += FacingHelper::zOffset($bendDir2);
						$this->placeLogAt($worldIn, new Vector3($forkX, $forkY, $forkZ));
						$forkTopY = $forkY;
					}

					// Place second canopy at the fork tip
					if($forkTopY > $forkBase[1]){
						$forkCanopyPos = new Vector3($forkX, $forkTopY, $forkZ);
						$this->placeCanopy($worldIn, $forkCanopyPos, $rand, true);
					}
				}
			}

			// Add small branches extending from trunk into canopy
			// Similar to BigJungleTree branches: small log segments going
			// diagonally outward + upward from the trunk, with leaf clusters at tips
			$branchCount = 1 + $rand->nextBoundedInt(2); // 1-2 branches

			for($b = 0; $b < $branchCount; ++$b){
				// Pick a trunk position near the canopy area
				$branchStartIdx = $bendStart + $rand->nextBoundedInt(max(1, $height - $bendStart));

				if(isset($trunkPositions[$branchStartIdx])){
					$branchBase = $trunkPositions[$branchStartIdx];
					$branchDir = FacingHelper::HORIZONTAL[$rand->nextBoundedInt(4)];
					$branchLen = 1 + $rand->nextBoundedInt(2); // 1-2 blocks outward

					$bx = $branchBase[0];
					$bz = $branchBase[2];
					$by = $branchBase[1];

					// Build branch: extending outward and slightly upward
					for($i = 0; $i < $branchLen; ++$i){
						$bx += FacingHelper::xOffset($branchDir);
						$bz += FacingHelper::zOffset($branchDir);
						$by += 1; // branch goes upward alongside outward
						$this->placeLogAt($worldIn, new Vector3($bx, $by, $bz));
					}

					// Small leaf cluster around branch tip (like BigJungleTree)
					$this->placeLeafAt($worldIn, new Vector3($bx, $by + 1, $bz));
					$this->placeLeafAt($worldIn, new Vector3($bx + 1, $by, $bz));
					$this->placeLeafAt($worldIn, new Vector3($bx - 1, $by, $bz));
					$this->placeLeafAt($worldIn, new Vector3($bx, $by, $bz + 1));
					$this->placeLeafAt($worldIn, new Vector3($bx, $by, $bz - 1));
				}
			}

			return true;
		}

		return false;
	}

	/**
	 * Places a canopy (leaves + small branch logs) at the given position.
	 * Vanilla acacia canopy: flat-ish layer of leaves with rounded corners,
	 * a smaller layer 1 block above, and leaf tips extending 2 blocks outward
	 * using Vector3::getSide() (the correct API for this PocketMine version).
	 *
	 * @param ChunkManager $worldIn The world
	 * @param Vector3 $pos Center position (top of trunk)
	 * @param Random $rand Random number generator
	 * @param bool $isFork Whether this is the smaller fork canopy (smaller radius)
	 */
	private function placeCanopy(ChunkManager $worldIn, Vector3 $pos, Random $rand, bool $isFork = false)
	{
		$radius = $isFork ? 2 : 3;

		// Bottom canopy layer (larger)
		for($dx = -$radius; $dx <= $radius; ++$dx){
			for($dz = -$radius; $dz <= $radius; ++$dz){
				// Round corners: skip if both offsets are at max radius
				if(abs($dx) === $radius && abs($dz) === $radius){
					continue;
				}
				$this->placeLeafAt($worldIn, $pos->add($dx, 0, $dz));
			}
		}

		// Place small branch logs at canopy edges (vanilla-like + BigJungleTree style)
		// These extend 1 block outward from the trunk at canopy level
		if(!$isFork){
			foreach(FacingHelper::HORIZONTAL as $face){
				if($rand->nextBoundedInt(3) === 0){
					$branchX = $pos->getFloorX() + FacingHelper::xOffset($face);
					$branchZ = $pos->getFloorZ() + FacingHelper::zOffset($face);
					$branchPos = new Vector3($branchX, $pos->getFloorY(), $branchZ);
					$this->placeLogAt($worldIn, $branchPos);
				}
			}
		}

		// Top canopy layer (smaller, +1 above)
		$topRadius = $isFork ? 1 : 2;
		$topPos = $pos->getSide(Vector3::SIDE_UP);

		for($dx = -$topRadius; $dx <= $topRadius; ++$dx){
			for($dz = -$topRadius; $dz <= $topRadius; ++$dz){
				if(abs($dx) === $topRadius && abs($dz) === $topRadius){
					continue;
				}
				$this->placeLeafAt($worldIn, $topPos->add($dx, 0, $dz));
			}
		}

		// Extra leaf tips extending 2 blocks from top center in each direction.
		// FIXED: use Vector3::getSide() instead of east()/west()/north()/south()
		// which don't exist in this PocketMine version's Vector3 class.
		if(!$isFork){
			$this->placeLeafAt($worldIn, $topPos->getSide(Vector3::SIDE_EAST, 2));
			$this->placeLeafAt($worldIn, $topPos->getSide(Vector3::SIDE_WEST, 2));
			$this->placeLeafAt($worldIn, $topPos->getSide(Vector3::SIDE_SOUTH, 2));
			$this->placeLeafAt($worldIn, $topPos->getSide(Vector3::SIDE_NORTH, 2));
		}
	}

	/**
	 * Places a trunk log at the given position.
	 * Always places the log regardless of existing block,
	 * because trunk should override everything (sapling, air, grass, etc).
	 */
	private function placeLogAt(ChunkManager $level, Vector3 $pos)
	{
		$level->setBlockIdAt($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ(), $this->trunkBlock);
		$level->setBlockDataAt($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ(), $this->blockMeta);
	}

	/**
	 * Places a leaf block at the given position.
	 * Only places if the existing block is overridable (air, leaves, sapling, etc).
	 */
	private function placeLeafAt(ChunkManager $worldIn, Vector3 $pos)
	{
		$blockId = $worldIn->getBlockIdAt($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ());

		// Leaves can replace: air, leaves (all types), sapling, vine, snow, grass
		if($blockId === Block::AIR || $blockId === Block::LEAVES || $blockId === Block::LEAVES2
			|| $blockId === Block::SAPLING || $blockId === Block::VINE
			|| $blockId === Block::SNOW_LAYER || $blockId === Block::GRASS){
			$worldIn->setBlockIdAt($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ(), $this->leafBlock);
			$worldIn->setBlockDataAt($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ(), $this->blockMeta);
		}
	}

}
