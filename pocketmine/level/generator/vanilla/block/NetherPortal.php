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
use pocketmine\level\generator\vanilla\level\portal\PortalShape;
use pocketmine\Player;

class NetherPortal extends Flowable
{
	protected $id = self::PORTAL;

	public function __construct(int $meta = 0)
	{
		$this->meta = $meta;
	}

	public function getName() : string
	{
		return "Nether Portal";
	}

	public function getHardness() : float
	{
		return -1;
	}

	public function getBlastResistance() : float
	{
		return 0;
	}

	public function getLightLevel() : int
	{
		return 11;
	}

	public function isBreakable(Item $item) : bool
	{
		return false;
	}

	public function canBeFlowedInto() : bool
	{
		return false;
	}

	/**
	 * The portal disappears as soon as its frame is not complete anymore (explosions, pistons, plugins...)
	 */
	public function onNearbyBlockChange() : void
	{
		$axis = $this->meta === PortalShape::AXIS_X || $this->meta === PortalShape::AXIS_Z ? $this->meta : null;
		if (PortalShape::find($this->level, (int) $this->x, (int) $this->y, (int) $this->z, $axis) === null) {
			$this->level->setBlock($this, BlockFactory::get(Block::AIR));
		}
	}

	public function onBreak(Item $item, ?Player $player = null) : bool
	{
		$result = parent::onBreak($item, $player);

		foreach ($this->getHorizontalSides() as $side) {
			if ($side instanceof NetherPortal) {
				$side->onBreak($item, $player);
			}
		}

		return $result;
	}
}
