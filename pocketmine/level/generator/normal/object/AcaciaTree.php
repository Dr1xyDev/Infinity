<?php
/*    
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█    
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░    
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.1
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

	
	public function generate(ChunkManager $worldIn, Random $rand, Vector3 $position) : bool
	{
		$x = $position->getFloorX();
		$y = $position->getFloorY();
		$z = $position->getFloorZ();

		
		$height = 5 + $rand->nextBoundedInt(2) + $rand->nextBoundedInt(3);

		
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

			
			$groundBlock = $worldIn->getBlockIdAt($x, $y - 1, $z);
			if(($groundBlock !== Block::GRASS && $groundBlock !== Block::DIRT) || $y >= 256 - $height - 1){
				return false;
			}

			
			$worldIn->setBlockIdAt($x, $y - 1, $z, Block::DIRT);

			
			
			$this->placeLogAt($worldIn, new Vector3($x, $y, $z));

			
			$bendDir = FacingHelper::HORIZONTAL[$rand->nextBoundedInt(4)];
			
			$bendStart = 1 + $rand->nextBoundedInt(3);
			
			$bendLength = 1 + $rand->nextBoundedInt(2);

			
			
			$curX = $x;
			$curZ = $z;
			$topY = $y;

			
			$trunkPositions = [];
			$trunkPositions[0] = [$x, $y, $z]; 

			for($trunkPos = 1; $trunkPos < $height; ++$trunkPos){
				$curY = $y + $trunkPos;

				
				if($trunkPos >= $bendStart && $bendLength > 0){
					$curX += FacingHelper::xOffset($bendDir);
					$curZ += FacingHelper::zOffset($bendDir);
					--$bendLength;
				}

				$this->placeLogAt($worldIn, new Vector3($curX, $curY, $curZ));
				$topY = $curY;
				$trunkPositions[$trunkPos] = [$curX, $curY, $curZ];
			}

			
			$canopyPos = new Vector3($curX, $topY, $curZ);
			$this->placeCanopy($worldIn, $canopyPos, $rand, false);

			
			
			$bendDir2 = FacingHelper::HORIZONTAL[$rand->nextBoundedInt(4)];

			if($bendDir2 !== $bendDir && count($trunkPositions) > 3){
				
				
				$forkStartIdx = max(1, $bendStart - 1 - $rand->nextBoundedInt(2));

				if(isset($trunkPositions[$forkStartIdx])){
					$forkBase = $trunkPositions[$forkStartIdx];
					$forkX = $forkBase[0];
					$forkZ = $forkBase[2];
					$forkLength = 1 + $rand->nextBoundedInt(3); 
					$forkTopY = $forkBase[1];

					
					for($i = 1; $i <= $forkLength; ++$i){
						$forkY = $forkBase[1] + $i;
						$forkX += FacingHelper::xOffset($bendDir2);
						$forkZ += FacingHelper::zOffset($bendDir2);
						$this->placeLogAt($worldIn, new Vector3($forkX, $forkY, $forkZ));
						$forkTopY = $forkY;
					}

					
					if($forkTopY > $forkBase[1]){
						$forkCanopyPos = new Vector3($forkX, $forkTopY, $forkZ);
						$this->placeCanopy($worldIn, $forkCanopyPos, $rand, true);
					}
				}
			}

			
			
			
			$branchCount = 1 + $rand->nextBoundedInt(2); 

			for($b = 0; $b < $branchCount; ++$b){
				
				$branchStartIdx = $bendStart + $rand->nextBoundedInt(max(1, $height - $bendStart));

				if(isset($trunkPositions[$branchStartIdx])){
					$branchBase = $trunkPositions[$branchStartIdx];
					$branchDir = FacingHelper::HORIZONTAL[$rand->nextBoundedInt(4)];
					$branchLen = 1 + $rand->nextBoundedInt(2); 

					$bx = $branchBase[0];
					$bz = $branchBase[2];
					$by = $branchBase[1];

					
					for($i = 0; $i < $branchLen; ++$i){
						$bx += FacingHelper::xOffset($branchDir);
						$bz += FacingHelper::zOffset($branchDir);
						$by += 1; 
						$this->placeLogAt($worldIn, new Vector3($bx, $by, $bz));
					}

					
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

	
	private function placeCanopy(ChunkManager $worldIn, Vector3 $pos, Random $rand, bool $isFork = false)
	{
		$radius = $isFork ? 2 : 3;

		
		for($dx = -$radius; $dx <= $radius; ++$dx){
			for($dz = -$radius; $dz <= $radius; ++$dz){
				
				if(abs($dx) === $radius && abs($dz) === $radius){
					continue;
				}
				$this->placeLeafAt($worldIn, $pos->add($dx, 0, $dz));
			}
		}

		
		
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

		
		
		
		if(!$isFork){
			$this->placeLeafAt($worldIn, $topPos->getSide(Vector3::SIDE_EAST, 2));
			$this->placeLeafAt($worldIn, $topPos->getSide(Vector3::SIDE_WEST, 2));
			$this->placeLeafAt($worldIn, $topPos->getSide(Vector3::SIDE_SOUTH, 2));
			$this->placeLeafAt($worldIn, $topPos->getSide(Vector3::SIDE_NORTH, 2));
		}
	}

	
	private function placeLogAt(ChunkManager $level, Vector3 $pos)
	{
		$level->setBlockIdAt($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ(), $this->trunkBlock);
		$level->setBlockDataAt($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ(), $this->blockMeta);
	}

	
	private function placeLeafAt(ChunkManager $worldIn, Vector3 $pos)
	{
		$blockId = $worldIn->getBlockIdAt($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ());

		
		if($blockId === Block::AIR || $blockId === Block::LEAVES || $blockId === Block::LEAVES2
			|| $blockId === Block::SAPLING || $blockId === Block::VINE
			|| $blockId === Block::SNOW_LAYER || $blockId === Block::GRASS){
			$worldIn->setBlockIdAt($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ(), $this->leafBlock);
			$worldIn->setBlockDataAt($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ(), $this->blockMeta);
		}
	}

}
