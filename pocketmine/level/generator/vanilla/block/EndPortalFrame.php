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

use pocketmine\level\generator\vanilla\block\utils\pattern\BlockPattern;
use pocketmine\level\generator\vanilla\block\utils\pattern\FactoryBlockPattern;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\math\AxisAlignedBB;
use pocketmine\level\generator\vanilla\math\Facing;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\Player;

class EndPortalFrame extends Solid
{
	public const META_FACING_MASK = 0x03;
	public const META_FACING_SOUTH = 0;
	public const META_FACING_WEST = 1;
	public const META_FACING_NORTH = 2;
	public const META_FACING_EAST = 3;
	public const META_EYE = 0x04;

	protected $id = self::END_PORTAL_FRAME;

	private static ?BlockPattern $portalShape = null;

	public function __construct(int $meta = 0)
	{
		$this->meta = $meta;
	}

	/** Возвращает true если в рамку вставлен глаз эндера. */
	public function hasEye() : bool
	{
		return ($this->meta & self::META_EYE) !== 0;
	}

	public function getFacing() : int
	{
		return match($this->meta & self::META_FACING_MASK){
			self::META_FACING_WEST => Facing::WEST,
			self::META_FACING_NORTH => Facing::NORTH,
			self::META_FACING_EAST => Facing::EAST,
			default => Facing::SOUTH,
		};
	}

	public static function getOrCreatePortalShape() : BlockPattern
	{
		if(self::$portalShape === null){
			$frameId = self::END_PORTAL_FRAME;

			$frame = static function(int $facingMeta) use ($frameId) : callable{
				return static function(Block $block) use ($frameId, $facingMeta) : bool{
					return $block->getId() === $frameId
						&& ($block->getDamage() & EndPortalFrame::META_EYE) !== 0
						&& ($block->getDamage() & EndPortalFrame::META_FACING_MASK) === $facingMeta;
				};
			};

			self::$portalShape = FactoryBlockPattern::start()
				->aisle("?vvv?", ">???<", ">???<", ">???<", "?^^^?")
				->where('?', static function(Block $block) : bool{ return true; })
				->where('^', $frame(EndPortalFrame::META_FACING_SOUTH))
				->where('>', $frame(EndPortalFrame::META_FACING_WEST))
				->where('v', $frame(EndPortalFrame::META_FACING_NORTH))
				->where('<', $frame(EndPortalFrame::META_FACING_EAST))
				->build();
		}

		return self::$portalShape;
	}

	public function getLightLevel() : int
	{
		return 1;
	}

	public function getName() : string
	{
		return "End Portal Frame";
	}

	public function getHardness() : float
	{
		return -1;
	}

	public function getBlastResistance() : float
	{
		return 18000000;
	}

	public function isBreakable(Item $item) : bool
	{
		return false;
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		$faces = [
			0 => 1,
			1 => 2,
			2 => 3,
			3 => 0,
		];
		$this->meta = $faces[$player->getDirection()];
		return parent::place($item, $blockReplace, $blockClicked, $face, $clickVector, $player);
	}

	protected function recalculateBoundingBox() : ?AxisAlignedBB{
		return new AxisAlignedBB(
			$this->x,
			$this->y,
			$this->z,
			$this->x + 1,
			$this->y + (($this->getDamage() & 0x04) > 0 ? 1 : 0.8125),
			$this->z + 1
		);
	}
}
