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

use pocketmine\level\generator\vanilla\block\utils\CopperMaterial;
use pocketmine\level\generator\vanilla\block\utils\CopperOxidation;
use pocketmine\level\generator\vanilla\item\TieredTool;

class CutCopperSlab extends Slab implements CopperMaterial
{
	protected int $doubleSlabId;

	public function __construct(int $id, int $meta, string $name, int $doubleSlabId)
	{
		$this->id = $id;
		$this->meta = $meta;
		$this->fallbackName = $name;
		$this->doubleSlabId = $doubleSlabId;
	}

	public function getDoubleSlabId() : int
	{
		return $this->doubleSlabId;
	}

	public function getVariantBitmask() : int
	{
		return 0x00;
	}

	public function getTopBitmask() : int
	{
		return 0x01;
	}

	public function getOxidation() : int{
		return match ($this->id) {
			BlockIds::WAXED_OXIDIZED_CUT_COPPER_SLAB, BlockIds::OXIDIZED_CUT_COPPER_SLAB => CopperOxidation::OXIDIZED,
			BlockIds::WAXED_WEATHERED_CUT_COPPER_SLAB, BlockIds::WEATHERED_CUT_COPPER_SLAB => CopperOxidation::WEATHERED,
			BlockIds::WAXED_EXPOSED_CUT_COPPER_SLAB, BlockIds::EXPOSED_CUT_COPPER_SLAB => CopperOxidation::EXPOSED,
			default => CopperOxidation::NONE
		};
	}

	public function getPreviousOxidationId() : ?int
	{
		return match ($this->id) {
			BlockIds::OXIDIZED_CUT_COPPER_SLAB => BlockIds::WEATHERED_CUT_COPPER_SLAB,
			BlockIds::WEATHERED_CUT_COPPER_SLAB => BlockIds::EXPOSED_CUT_COPPER_SLAB,
			BlockIds::EXPOSED_CUT_COPPER_SLAB => BlockIds::CUT_COPPER_SLAB,
			default => null
		};
	}

	public function isWaxed() : bool
	{
		return match ($this->id) {
			BlockIds::WAXED_CUT_COPPER_SLAB, BlockIds::WAXED_EXPOSED_CUT_COPPER_SLAB, BlockIds::WAXED_WEATHERED_CUT_COPPER_SLAB, BlockIds::WAXED_OXIDIZED_CUT_COPPER_SLAB => true,
			default => false
		};
	}

	public function getWaxedId() : int{
		return match ($this->id) {
			BlockIds::OXIDIZED_CUT_COPPER_SLAB => BlockIds::WAXED_OXIDIZED_CUT_COPPER_SLAB,
			BlockIds::WEATHERED_CUT_COPPER_SLAB => BlockIds::WAXED_WEATHERED_CUT_COPPER_SLAB,
			BlockIds::EXPOSED_CUT_COPPER_SLAB => BlockIds::WAXED_EXPOSED_CUT_COPPER_SLAB,
			default => BlockIds::WAXED_CUT_COPPER_SLAB
		};
	}

	public function getNonWaxedId() : int{
		return match ($this->id) {
			BlockIds::WAXED_OXIDIZED_CUT_COPPER_SLAB => BlockIds::OXIDIZED_CUT_COPPER_SLAB,
			BlockIds::WAXED_WEATHERED_CUT_COPPER_SLAB => BlockIds::WEATHERED_CUT_COPPER_SLAB,
			BlockIds::WAXED_EXPOSED_CUT_COPPER_SLAB => BlockIds::EXPOSED_CUT_COPPER_SLAB,
			default => BlockIds::CUT_COPPER_SLAB
		};
	}

	public function getName() : string
	{
		return ($this->isTop() ? "Upper " : "") . $this->fallbackName;
	}

	public function getHardness() : float
	{
		return 3;
	}

	public function getToolType() : int
	{
		return BlockToolType::TYPE_PICKAXE;
	}

	public function getToolHarvestLevel() : int
	{
		return TieredTool::TIER_STONE;
	}
}
