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

class CutCopperDoubleSlab extends DoubleSlab implements CopperMaterial
{
	protected int $slabId;

	public function __construct(int $id, int $meta, int $slabId)
	{
		$this->id = $id;
		$this->meta = $meta;
		$this->slabId = $slabId;
	}

	public function getSlabId() : int
	{
		return $this->slabId;
	}

	public function getOxidation() : int{
		return match ($this->id) {
			BlockIds::WAXED_OXIDIZED_DOUBLE_CUT_COPPER_SLAB, BlockIds::OXIDIZED_DOUBLE_CUT_COPPER_SLAB => CopperOxidation::OXIDIZED,
			BlockIds::WAXED_WEATHERED_DOUBLE_CUT_COPPER_SLAB, BlockIds::WEATHERED_DOUBLE_CUT_COPPER_SLAB => CopperOxidation::WEATHERED,
			BlockIds::WAXED_EXPOSED_DOUBLE_CUT_COPPER_SLAB, BlockIds::EXPOSED_DOUBLE_CUT_COPPER_SLAB => CopperOxidation::EXPOSED,
			default => CopperOxidation::NONE
		};
	}

	public function getPreviousOxidationId() : ?int
	{
		return match ($this->id) {
			BlockIds::OXIDIZED_DOUBLE_CUT_COPPER_SLAB => BlockIds::WEATHERED_DOUBLE_CUT_COPPER_SLAB,
			BlockIds::WEATHERED_DOUBLE_CUT_COPPER_SLAB => BlockIds::EXPOSED_DOUBLE_CUT_COPPER_SLAB,
			BlockIds::EXPOSED_DOUBLE_CUT_COPPER_SLAB => BlockIds::DOUBLE_CUT_COPPER_SLAB,
			default => null
		};
	}

	public function isWaxed() : bool
	{
		return match ($this->id) {
			BlockIds::WAXED_DOUBLE_CUT_COPPER_SLAB, BlockIds::WAXED_EXPOSED_DOUBLE_CUT_COPPER_SLAB, BlockIds::WAXED_WEATHERED_DOUBLE_CUT_COPPER_SLAB, BlockIds::WAXED_OXIDIZED_DOUBLE_CUT_COPPER_SLAB => true,
			default => false
		};
	}

	public function getWaxedId() : int{
		return match ($this->id) {
			BlockIds::OXIDIZED_DOUBLE_CUT_COPPER_SLAB => BlockIds::WAXED_OXIDIZED_DOUBLE_CUT_COPPER_SLAB,
			BlockIds::WEATHERED_DOUBLE_CUT_COPPER_SLAB => BlockIds::WAXED_WEATHERED_DOUBLE_CUT_COPPER_SLAB,
			BlockIds::EXPOSED_DOUBLE_CUT_COPPER_SLAB => BlockIds::WAXED_EXPOSED_DOUBLE_CUT_COPPER_SLAB,
			default => BlockIds::WAXED_DOUBLE_CUT_COPPER_SLAB
		};
	}

	public function getNonWaxedId() : int{
		return match ($this->id) {
			BlockIds::WAXED_OXIDIZED_DOUBLE_CUT_COPPER_SLAB => BlockIds::OXIDIZED_DOUBLE_CUT_COPPER_SLAB,
			BlockIds::WAXED_WEATHERED_DOUBLE_CUT_COPPER_SLAB => BlockIds::WEATHERED_DOUBLE_CUT_COPPER_SLAB,
			BlockIds::WAXED_EXPOSED_DOUBLE_CUT_COPPER_SLAB => BlockIds::EXPOSED_DOUBLE_CUT_COPPER_SLAB,
			default => BlockIds::DOUBLE_CUT_COPPER_SLAB
		};
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
