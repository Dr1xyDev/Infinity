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
use pocketmine\level\generator\vanilla\item\ItemFactory;
use pocketmine\level\generator\vanilla\math\AxisAlignedBB;
use pocketmine\level\generator\vanilla\math\Facing;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\Player;
use pocketmine\level\generator\vanilla\tile\Banner as TileBanner;
use pocketmine\level\generator\vanilla\tile\Tile;

use function floor;

class StandingBanner extends Transparent
{
	protected $id = self::STANDING_BANNER;

	protected $itemId = Item::BANNER;

	public function __construct(int $meta = 0)
	{
		$this->meta = $meta;
	}

	public function getHardness() : float
	{
		return 1;
	}

	public function isSolid() : bool
	{
		return false;
	}

	public function getName() : string
	{
		return "Standing Banner";
	}

	/**
	 * @return AxisAlignedBB[]
	 */
	protected function recalculateCollisionBoxes() : array
	{
		return [];
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool
	{
		if ($face !== Facing::DOWN) {
			if ($face === Facing::UP && $player !== null) {
				$this->meta = floor((($player->yaw + 180) * 16 / 360) + 0.5) & 0x0f;
				$this->getLevel()->setBlock($blockReplace, $this, true);
			} else {
				$this->meta = $face;
				$this->getLevel()->setBlock($blockReplace, BlockFactory::get(Block::WALL_BANNER, $this->meta), true);
			}

			Tile::createTile(Tile::BANNER, $this->getLevel(), TileBanner::createNBT($this, $face, $item, $player));
			return true;
		}

		return false;
	}

	public function onNearbyBlockChange() : void
	{
		if ($this->getSide(Facing::DOWN)->getId() === self::AIR) {
			$this->getLevel()->useBreakOn($this);
		}
	}

	public function getToolType() : int
	{
		return BlockToolType::TYPE_AXE;
	}

	public function getVariantBitmask() : int
	{
		return 0;
	}

	public function getDropsForCompatibleTool(Item $item) : array
	{
		$tile = $this->level->getTile($this);

		$drop = ItemFactory::get(Item::BANNER, ($tile instanceof TileBanner ? $tile->getBaseColor() : 0));
		if ($tile instanceof TileBanner && !($patterns = $tile->getPatterns())->empty()) {
			$drop->setNamedTagEntry(clone $patterns);
		}

		return [$drop];
	}

	public function isAffectedBySilkTouch() : bool
	{
		return false;
	}
}
