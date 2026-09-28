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

class ChiseledCopper extends Solid implements CopperMaterial
{
	public function getOxidation() : int{
		return match ($this->id) {
			BlockIds::WAXED_OXIDIZED_CHISELED_COPPER, BlockIds::OXIDIZED_CHISELED_COPPER => CopperOxidation::OXIDIZED,
			BlockIds::WAXED_WEATHERED_CHISELED_COPPER, BlockIds::WEATHERED_CHISELED_COPPER => CopperOxidation::WEATHERED,
			BlockIds::WAXED_EXPOSED_CHISELED_COPPER, BlockIds::EXPOSED_CHISELED_COPPER => CopperOxidation::EXPOSED,
			default => CopperOxidation::NONE
		};
	}

	public function getPreviousOxidationId() : ?int
	{
		return match ($this->id) {
			BlockIds::OXIDIZED_CHISELED_COPPER => BlockIds::WEATHERED_CHISELED_COPPER,
			BlockIds::WEATHERED_CHISELED_COPPER => BlockIds::EXPOSED_CHISELED_COPPER,
			BlockIds::EXPOSED_CHISELED_COPPER => BlockIds::CHISELED_COPPER,
			default => null
		};
	}

	public function isWaxed() : bool
	{
		return match ($this->id) {
			BlockIds::WAXED_CHISELED_COPPER, BlockIds::WAXED_EXPOSED_CHISELED_COPPER, BlockIds::WAXED_WEATHERED_CHISELED_COPPER, BlockIds::WAXED_OXIDIZED_CHISELED_COPPER => true,
			default => false
		};
	}

	public function getWaxedId() : int{
		return match ($this->id) {
			BlockIds::OXIDIZED_CHISELED_COPPER => BlockIds::WAXED_OXIDIZED_CHISELED_COPPER,
			BlockIds::WEATHERED_CHISELED_COPPER => BlockIds::WAXED_WEATHERED_CHISELED_COPPER,
			BlockIds::EXPOSED_CHISELED_COPPER => BlockIds::WAXED_EXPOSED_CHISELED_COPPER,
			default => BlockIds::WAXED_CHISELED_COPPER
		};
	}

	public function getNonWaxedId() : int{
		return match ($this->id) {
			BlockIds::WAXED_OXIDIZED_CHISELED_COPPER => BlockIds::OXIDIZED_CHISELED_COPPER,
			BlockIds::WAXED_WEATHERED_CHISELED_COPPER => BlockIds::WEATHERED_CHISELED_COPPER,
			BlockIds::WAXED_EXPOSED_CHISELED_COPPER => BlockIds::EXPOSED_CHISELED_COPPER,
			default => BlockIds::CHISELED_COPPER
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
