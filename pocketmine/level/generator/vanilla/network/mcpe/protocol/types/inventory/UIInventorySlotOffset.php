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

namespace pocketmine\level\generator\vanilla\network\mcpe\protocol\types\inventory;

/**
 * Values extracted from PlayerUISlot enum in Bedrock
 */
final class UIInventorySlotOffset
{
	private function __construct()
	{
		//NOOP
	}

	public const CURSOR = 0;
	public const ANVIL = [
		1 => 0,
		2 => 1,
	];
	public const STONE_CUTTER_INPUT = 3;
	public const TRADE2_INGREDIENT = [
		4 => 0,
		5 => 1,
	];
	public const TRADE_INGREDIENT = [
		6 => 0,
		7 => 1,
	];
	public const MATERIAL_REDUCER_INPUT = 8;
	public const LOOM = [
		9 => 0,
		10 => 1,
		11 => 2,
	];
	public const CARTOGRAPHY_TABLE = [
		12 => 0,
		13 => 1,
	];
	public const ENCHANTING_TABLE = [
		14 => 0,
		15 => 1,
	];
	public const GRINDSTONE = [
		16 => 0,
		17 => 1,
	];
	public const COMPOUND_CREATOR_INPUT = [
		18 => 0,
		19 => 1,
		20 => 2,
		21 => 3,
		22 => 4,
		23 => 5,
		24 => 6,
		25 => 7,
		26 => 8,
	];
	public const BEACON_PAYMENT = 27;
	public const CRAFTING2X2_INPUT = [
		28 => 0,
		29 => 1,
		30 => 2,
		31 => 3,
	];
	public const CRAFTING3X3_INPUT = [
		32 => 0,
		33 => 1,
		34 => 2,
		35 => 3,
		36 => 4,
		37 => 5,
		38 => 6,
		39 => 7,
		40 => 8,
	];
	public const MATERIAL_REDUCER_OUTPUT = [
		41 => 0,
		42 => 1,
		43 => 2,
		44 => 3,
		45 => 4,
		46 => 5,
		47 => 6,
		48 => 7,
		49 => 8,
	];
	public const CREATED_ITEM_OUTPUT = 50;
	public const SMITHING_TABLE = [
		51 => 0, //input
		52 => 1, //material
		53 => 2, //template
	];
}
