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

use pocketmine\block\Block;
use pocketmine\block\RedMushroomBlock;
use pocketmine\level\ChunkManager;
use pocketmine\math\Vector3;
use pocketmine\utils\Random;

class HugeRedMushroom{

	
	const CAP_BLOCK = Block::RED_MUSHROOM_BLOCK; 

	
	const META_STEM        = RedMushroomBlock::STEM; 
	const META_CAP_FULL    = RedMushroomBlock::RED;  
	const META_CAP_CENTER  = 1;  
	const META_PORES       = 0;  

	
	public function generate(ChunkManager $level, Random $rand, Vector3 $position): bool{
		
		$height = $rand->nextBoundedInt(3) + 4;
		if($rand->nextBoundedInt(12) === 0){
			$height *= 2;
		}

		$baseX = $position->getFloorX();
		$baseY = $position->getFloorY();
		$baseZ = $position->getFloorZ();

		
		if($baseY < 1 || $baseY + $height + 1 > 127){
			return false;
		}

		
		$groundBlock = $level->getBlockIdAt($baseX, $baseY - 1, $baseZ);
		if($groundBlock !== Block::DIRT && $groundBlock !== Block::GRASS && $groundBlock !== Block::MYCELIUM){
			return false;
		}

		
		for($y = 0; $y < $height; ++$y){
			$checkRadius = 0;
			if($y < $height - 3){
				
				$checkRadius = 2;
			}elseif($y === $height){
				
				$checkRadius = 1;
			}

			for($dx = -$checkRadius; $dx <= $checkRadius; ++$dx){
				for($dz = -$checkRadius; $dz <= $checkRadius; ++$dz){
					$blockId = $level->getBlockIdAt($baseX + $dx, $baseY + $y, $baseZ + $dz);
					if($blockId !== Block::AIR && $blockId !== Block::LEAVES && $blockId !== Block::LEAVES2
						&& $blockId !== Block::SNOW_LAYER && $blockId !== Block::TALL_GRASS
						&& $blockId !== Block::SAPLING && $blockId !== Block::VINE){
						return false;
					}
				}
			}
		}

		
		for($y = 0; $y < $height; ++$y){
			$existingBlock = $level->getBlockIdAt($baseX, $baseY + $y, $baseZ);
			
			if(!Block::$solid[$existingBlock]){
				$level->setBlockIdAt($baseX, $baseY + $y, $baseZ, self::CAP_BLOCK);
				$level->setBlockDataAt($baseX, $baseY + $y, $baseZ, self::META_STEM);
			}
		}

		
		
		$capRadius = ($height > 7) ? 2 : 1;

		
		for($layer = $height - 3; $layer <= $height; ++$layer){
			
			$layerRadius = $capRadius;
			if($layer === $height){
				
				$layerRadius = max(0, $capRadius - 1);
			}

			
			

			for($dx = -$layerRadius; $dx <= $layerRadius; ++$dx){
				for($dz = -$layerRadius; $dz <= $layerRadius; ++$dz){
					
					$atEdgeX = abs($dx) === $layerRadius && $layerRadius > 0;
					$atEdgeZ = abs($dz) === $layerRadius && $layerRadius > 0;
					$isCenter = $dx === 0 && $dz === 0;

					
					
					
					
					$shouldPlace = ($layer === $height) || ($atEdgeX && $atEdgeZ) || $isCenter;

					if(!$shouldPlace){
						continue;
					}

					$bx = $baseX + $dx;
					$by = $baseY + $layer;
					$bz = $baseZ + $dz;

					
					$existingBlock = $level->getBlockIdAt($bx, $by, $bz);
					if(Block::$solid[$existingBlock]){
						continue;
					}

					
					
					
					
					
					
					
					$meta = $this->computeCapMeta($dx < 0, $dx > 0, $dz < 0, $dz > 0);

					$level->setBlockIdAt($bx, $by, $bz, self::CAP_BLOCK);
					$level->setBlockDataAt($bx, $by, $bz, $meta);
				}
			}
		}

		
		
		
		$topY = $baseY + $height;
		$level->setBlockDataAt($baseX, $topY, $baseZ, self::META_CAP_CENTER);

		return true;
	}

	
	private function computeCapMeta(bool $west, bool $east, bool $north, bool $south): int{
		
		
		if(!$west && !$east && !$north && !$south){
			return self::META_CAP_CENTER; 
		}

		
		if($west && $east && $north && $south){
			return self::META_CAP_FULL; 
		}

		
		
		
		
		
		
		
		
		
		
		
		
		

		
		if($west && !$east && !$north && !$south){
			return 6; 
		}
		if(!$west && $east && !$north && !$south){
			
			
			return self::META_CAP_FULL; 
		}
		if(!$west && !$east && $north && !$south){
			return 3; 
		}
		if(!$west && !$east && !$north && $south){
			return 4; 
		}

		
		if(!$west && !$east && $north && $south){
			return 5; 
		}
		if($west && !$east && $north && !$south){
			return 7; 
		}
		if($west && !$east && !$north && $south){
			return 8; 
		}
		if(!$west && $east && $north && !$south){
			
			return self::META_CAP_FULL;
		}
		if(!$west && $east && !$north && $south){
			
			return self::META_CAP_FULL;
		}
		if($west && $east && !$north && !$south){
			
			return self::META_CAP_FULL;
		}

		
		if($west && !$east && $north && $south){
			return 9; 
		}
		if(!$west && $east && $north && $south){
			
			return self::META_CAP_FULL;
		}
		if($west && $east && $north && !$south){
			
			return self::META_CAP_FULL;
		}
		if($west && $east && !$north && $south){
			
			return self::META_CAP_FULL;
		}

		
		return self::META_CAP_FULL;
	}
}
