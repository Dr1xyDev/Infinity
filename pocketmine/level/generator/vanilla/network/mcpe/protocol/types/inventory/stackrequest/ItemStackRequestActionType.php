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

namespace pocketmine\level\generator\vanilla\network\mcpe\protocol\types\inventory\stackrequest;

final class ItemStackRequestActionType
{
	private function __construct()
	{
		//NOOP
	}

	public const TAKE = 0;
	public const PLACE = 1;
	public const SWAP = 2;
	public const DROP = 3;
	public const DESTROY = 4;
	public const CRAFTING_CONSUME_INPUT = 5;
	public const CRAFTING_CREATE_SPECIFIC_RESULT = 6;
	public const LAB_TABLE_COMBINE = 9;
	public const BEACON_PAYMENT = 10;
	public const MINE_BLOCK = 11;
	public const CRAFTING_RECIPE = 12;
	public const CRAFTING_RECIPE_AUTO = 13; //recipe book?
	public const CREATIVE_CREATE = 14;
	public const CRAFTING_RECIPE_OPTIONAL = 15; //anvil/cartography table rename
	public const CRAFTING_GRINDSTONE = 16;
	public const CRAFTING_LOOM = 17;
	public const CRAFTING_NON_IMPLEMENTED_DEPRECATED_ASK_TY_LAING = 18;
	public const CRAFTING_RESULTS_DEPRECATED_ASK_TY_LAING = 19; //no idea what this is for
}
