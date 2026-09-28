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

namespace pocketmine\level\generator\vanilla\item\trim;

use pocketmine\level\generator\vanilla\item\ItemFactory;
use pocketmine\level\generator\vanilla\item\ItemIds;
use pocketmine\level\generator\vanilla\utils\SingletonTrait;

class TrimFactory {
	use SingletonTrait;

	/** @var array<TrimPatternData> */
	private array $trimPatterns;
	/** @var array<TrimMaterialData> */
	private array $trimMaterials;

	public function __construct(){
		$this->trimPatterns = [
			new TrimPatternData('ward', ItemFactory::get(ItemIds::WARD_ARMOR_TRIM_SMITHING_TEMPLATE)),
			new TrimPatternData('sentry', ItemFactory::get(ItemIds::SENTRY_ARMOR_TRIM_SMITHING_TEMPLATE)),
			new TrimPatternData('snout', ItemFactory::get(ItemIds::SNOUT_ARMOR_TRIM_SMITHING_TEMPLATE)),
			new TrimPatternData('dune', ItemFactory::get(ItemIds::DUNE_ARMOR_TRIM_SMITHING_TEMPLATE)),
			new TrimPatternData('spire', ItemFactory::get(ItemIds::SPIRE_ARMOR_TRIM_SMITHING_TEMPLATE)),
			new TrimPatternData('tide', ItemFactory::get(ItemIds::TIDE_ARMOR_TRIM_SMITHING_TEMPLATE)),
			new TrimPatternData('wild', ItemFactory::get(ItemIds::WILD_ARMOR_TRIM_SMITHING_TEMPLATE)),
			new TrimPatternData('rib', ItemFactory::get(ItemIds::RIB_ARMOR_TRIM_SMITHING_TEMPLATE)),
			new TrimPatternData('coast', ItemFactory::get(ItemIds::COAST_ARMOR_TRIM_SMITHING_TEMPLATE)),
			new TrimPatternData('shaper', ItemFactory::get(ItemIds::SHAPER_ARMOR_TRIM_SMITHING_TEMPLATE)),
			new TrimPatternData('eye', ItemFactory::get(ItemIds::EYE_ARMOR_TRIM_SMITHING_TEMPLATE)),
			new TrimPatternData('vex', ItemFactory::get(ItemIds::VEX_ARMOR_TRIM_SMITHING_TEMPLATE)),
			new TrimPatternData('silence', ItemFactory::get(ItemIds::SILENCE_ARMOR_TRIM_SMITHING_TEMPLATE)),
			new TrimPatternData('wayfinder', ItemFactory::get(ItemIds::WAYFINDER_ARMOR_TRIM_SMITHING_TEMPLATE)),
			new TrimPatternData('raiser', ItemFactory::get(ItemIds::RAISER_ARMOR_TRIM_SMITHING_TEMPLATE)),
			new TrimPatternData('host', ItemFactory::get(ItemIds::HOST_ARMOR_TRIM_SMITHING_TEMPLATE)),
			new TrimPatternData('bolt', ItemFactory::get(ItemIds::BOLT_ARMOR_TRIM_SMITHING_TEMPLATE)),
			new TrimPatternData('flow', ItemFactory::get(ItemIds::FLOW_ARMOR_TRIM_SMITHING_TEMPLATE)),
		];

		$this->trimMaterials = [
			new TrimMaterialData('quartz', '§h', ItemFactory::get(ItemIds::QUARTZ)),
			new TrimMaterialData('iron', '§i', ItemFactory::get(ItemIds::IRON_INGOT)),
			new TrimMaterialData('netherite', '§j', ItemFactory::get(ItemIds::NETHERITE_INGOT)),
			new TrimMaterialData('redstone', '§m', ItemFactory::get(ItemIds::REDSTONE)),
			new TrimMaterialData('copper', '§n', ItemFactory::get(ItemIds::COPPER_INGOT)),
			new TrimMaterialData('gold', '§p', ItemFactory::get(ItemIds::GOLD_INGOT)),
			new TrimMaterialData('emerald', '§q', ItemFactory::get(ItemIds::EMERALD)),
			new TrimMaterialData('diamond', '§s', ItemFactory::get(ItemIds::DIAMOND)),
			new TrimMaterialData('lapis', '§t', ItemFactory::get(ItemIds::DYE, 4)),
			new TrimMaterialData('amethyst', '§u', ItemFactory::get(ItemIds::AMETHYST_SHARD)),
		];
	}

	/**
	 * @return array<TrimPatternData>
	 */
	public function getTrimPatterns() : array{
		return $this->trimPatterns;
	}

	/**
	 * @return array<TrimMaterialData>
	 */
	public function getTrimMaterials() : array{
		return $this->trimMaterials;
	}
}
