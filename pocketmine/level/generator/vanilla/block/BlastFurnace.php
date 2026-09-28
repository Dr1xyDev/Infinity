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
use pocketmine\level\generator\vanilla\item\ItemIds;
use pocketmine\level\generator\vanilla\item\TieredTool;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ProtocolInfo;
use pocketmine\Player;
use pocketmine\level\generator\vanilla\tile\BlastFurnace as TileBlastFurnace;
use pocketmine\level\generator\vanilla\tile\Tile;

use function mt_rand;

class BlastFurnace extends Solid
{
	protected $id = self::BLAST_FURNACE;
	protected $itemId = ItemIds::BLAST_FURNACE;

	public function __construct(int $meta = 0)
	{
		$this->meta = $meta;
	}

	public function getName() : string
	{
		return "Blast Furnace";
	}

	public function getHardness() : float
	{
		return 3.5;
	}

	public function getToolType() : int
	{
		return BlockToolType::TYPE_PICKAXE;
	}

	public function getToolHarvestLevel() : int
	{
		return TieredTool::TIER_WOODEN;
	}

	public function getLightLevel() : int
	{
		return 0;
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool
	{
		$this->meta = Block::getMetaFace($player instanceof Player ? $player->getDirection() : 0);
		$this->getLevel()->setBlock($blockReplace, $this, true, true);

		Tile::createTile(Tile::BLAST_FURNACE, $this->getLevel(), TileBlastFurnace::createNBT($this, $face, $item, $player));

		return true;
	}

	public function onActivate(Item $item, ?Player $player = null) : bool
	{
		if($player instanceof Player && $player->getProtocolVersion() >= ProtocolInfo::PROTOCOL_407){
			$furnace = $this->getLevel()->getTile($this);
			if(!($furnace instanceof TileBlastFurnace)){
				$furnace = Tile::createTile(Tile::BLAST_FURNACE, $this->getLevel(), TileBlastFurnace::createNBT($this));
				if(!($furnace instanceof TileBlastFurnace)){
					return true;
				}
			}

			if(!$furnace->canOpenWith($item->getCustomName())){
				return true;
			}

			$player->addWindow($furnace->getInventory());
		}

		return true;
	}

	public function getVariantBitmask() : int
	{
		return 0;
	}

	public function onScheduledUpdate() : void
	{
		$level = $this->getLevel();
		$furnace = $level->getTile($this);
		if($furnace instanceof TileBlastFurnace && $furnace->onUpdate()){
			if(mt_rand(1, 60) === 1){
				$level->addSound($furnace->getFurnaceType()->getCookSound($this));
			}
			$level->scheduleDelayedBlockUpdate($this, 1);
		}
	}
}
