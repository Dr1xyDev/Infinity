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

class CopperDoor extends Door implements CopperMaterial
{

	public function getOxidation() : int{
		return match ($this->id) {
			BlockIds::WAXED_OXIDIZED_COPPER_DOOR, BlockIds::OXIDIZED_COPPER_DOOR => CopperOxidation::OXIDIZED,
			BlockIds::WAXED_WEATHERED_COPPER_DOOR, BlockIds::WEATHERED_COPPER_DOOR => CopperOxidation::WEATHERED,
			BlockIds::WAXED_EXPOSED_COPPER_DOOR, BlockIds::EXPOSED_COPPER_DOOR => CopperOxidation::EXPOSED,
			default => CopperOxidation::NONE
		};
	}

	public function getPreviousOxidationId() : ?int
	{
		return match ($this->id) {
			BlockIds::OXIDIZED_COPPER_DOOR => BlockIds::WEATHERED_COPPER_DOOR,
			BlockIds::WEATHERED_COPPER_DOOR => BlockIds::EXPOSED_COPPER_DOOR,
			BlockIds::EXPOSED_COPPER_DOOR => BlockIds::COPPER_DOOR,
			default => null
		};
	}

	public function isWaxed() : bool
	{
		return match ($this->id) {
			BlockIds::WAXED_COPPER_DOOR, BlockIds::WAXED_EXPOSED_COPPER_DOOR, BlockIds::WAXED_WEATHERED_COPPER_DOOR, BlockIds::WAXED_OXIDIZED_COPPER_DOOR => true,
			default => false
		};
	}

	public function getWaxedId() : int{
		return match ($this->id) {
			BlockIds::OXIDIZED_COPPER_DOOR => BlockIds::WAXED_OXIDIZED_COPPER_DOOR,
			BlockIds::WEATHERED_COPPER_DOOR => BlockIds::WAXED_WEATHERED_COPPER_DOOR,
			BlockIds::EXPOSED_COPPER_DOOR => BlockIds::WAXED_EXPOSED_COPPER_DOOR,
			default => BlockIds::WAXED_COPPER_DOOR
		};
	}

	public function getNonWaxedId() : int{
		return match ($this->id) {
			BlockIds::WAXED_OXIDIZED_COPPER_DOOR => BlockIds::OXIDIZED_COPPER_DOOR,
			BlockIds::WAXED_WEATHERED_COPPER_DOOR => BlockIds::WEATHERED_COPPER_DOOR,
			BlockIds::WAXED_EXPOSED_COPPER_DOOR => BlockIds::EXPOSED_COPPER_DOOR,
			default => BlockIds::COPPER_DOOR
		};
	}

	public function getToolType() : int
	{
		return BlockToolType::TYPE_PICKAXE;
	}

	public function getToolHarvestLevel() : int
	{
		return TieredTool::TIER_STONE;
	}

	public function getHardness() : float
	{
		return 3;
	}
}
