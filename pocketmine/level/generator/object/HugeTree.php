<?php
/*    
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█    
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░    
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.1
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

	
	protected $baseHeight;

	
	protected $extraRandomHeight;

	
	protected $woodMetadata;

	
	protected $leavesMetadata;

	
	public function __construct($baseHeightIn, $extraRandomHeight, Block $woodMetadata, Block $leavesMetadata){
		$this->baseHeight = $baseHeightIn;
		$this->extraRandomHeight = $extraRandomHeight;
		$this->woodMetadata = $woodMetadata;
		$this->leavesMetadata = $leavesMetadata;
	}

	
	protected function getHeight(Random $rand) : int{
		return $this->baseHeight + $rand->nextBoundedInt($this->extraRandomHeight);
	}

	
	protected function ensureGrowable(ChunkManager $level, Random $rand, Vector3 $position, int $height) : bool{
		
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

		
		$down = $position->getSide(Vector3::SIDE_DOWN);
		$groundBlock = $level->getBlockIdAt($down->getFloorX(), $down->getFloorY(), $down->getFloorZ());
		if($groundBlock !== Block::GRASS && $groundBlock !== Block::DIRT && $groundBlock !== Block::FARMLAND){
			return false;
		}

		return true;
	}

	
	protected function createCrown(ChunkManager $level, Vector3 $pos, int $radius){
		for($y = -2; $y <= 0; ++$y){
			$this->growLeavesLayerStrict($level, $pos->getSide(Vector3::SIDE_UP, $y), $radius + 1 - $y);
		}
	}

	
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
