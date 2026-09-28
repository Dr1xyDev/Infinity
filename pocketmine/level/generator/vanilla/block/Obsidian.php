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

namespace pocketmine\level\generator\vanilla\block;

use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\item\TieredTool;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\Player;
use pocketmine\Server;
use function array_shift;
use function implode;

class Obsidian extends Solid
{
	protected $id = self::OBSIDIAN;

	public function __construct(int $meta = 0)
	{
		$this->meta = $meta;
	}

	public function getName() : string
	{
		return "Obsidian";
	}

	public function getToolType() : int
	{
		return BlockToolType::TYPE_PICKAXE;
	}

	public function getToolHarvestLevel() : int
	{
		return TieredTool::TIER_DIAMOND;
	}

	public function getHardness() : float
	{
		return 35; //50 in PC
	}

	public function getBlastResistance() : float
	{
		return 6000;
	}

	public function onBreak(Item $item, ?Player $player = null) : bool{
		parent::onBreak($item, $player);
		if(!Server::getInstance()->isAllowNether()){
			return true;
		}

		$hasAdjacentPortal = false;
		$portalBlock = null;

		for($i = 0; $i <= 5; $i++){
			$side = $this->getSide($i);
			if($side->getId() == BlockIds::PORTAL){
				$hasAdjacentPortal = true;
				$portalBlock = $side;
				break;
			}
		}

		if(!$hasAdjacentPortal){
			return true;
		}

		$level = $this->getLevel();
		$isXAxis = $level->getBlock($portalBlock->add(-1, 0, 0))->getId() == BlockIds::PORTAL || $level->getBlock($portalBlock->add(1, 0, 0))->getId() == BlockIds::PORTAL;

		$portalBlocks = [];
		$toCheck = [[$portalBlock->x, $portalBlock->y, $portalBlock->z]];
		$checked = [];

		// Maximum possible portal blocks: 21 (width) × 21 (height) = 441 blocks
		// Add buffer for safety: 500 iterations max
		$maxIterations = 500;
		$iterationCount = 0;

		while(!empty($toCheck) && $iterationCount < $maxIterations){
			$iterationCount++;

			$current = array_shift($toCheck);
			$key = implode(',', $current);

			if(isset($checked[$key])){
				continue;
			}

			$checked[$key] = true;
			$block = $level->getBlock(new Vector3($current[0], $current[1], $current[2]));

			if($block->getId() !== BlockIds::PORTAL){
				continue;
			}

			$portalBlocks[] = new Vector3($current[0], $current[1], $current[2]);

			if($isXAxis){
				$toCheck[] = [$current[0] + 1, $current[1], $current[2]];
				$toCheck[] = [$current[0] - 1, $current[1], $current[2]];
				$toCheck[] = [$current[0], $current[1] + 1, $current[2]];
				$toCheck[] = [$current[0], $current[1] - 1, $current[2]];
			}else{
				$toCheck[] = [$current[0], $current[1], $current[2] + 1];
				$toCheck[] = [$current[0], $current[1], $current[2] - 1];
				$toCheck[] = [$current[0], $current[1] + 1, $current[2]];
				$toCheck[] = [$current[0], $current[1] - 1, $current[2]];
			}
		}

		foreach($portalBlocks as $pos){
			$level->setBlock($pos, BlockFactory::get(BlockIds::AIR));
		}

		return true;
	}
}
