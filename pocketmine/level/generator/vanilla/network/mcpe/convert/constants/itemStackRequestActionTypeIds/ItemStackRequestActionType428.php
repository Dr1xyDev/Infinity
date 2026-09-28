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

namespace pocketmine\level\generator\vanilla\network\mcpe\convert\constants\itemStackRequestActionTypeIds;

final class ItemStackRequestActionType428
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
	public const LAB_TABLE_COMBINE = 7;
	public const BEACON_PAYMENT = 8;
	public const MINE_BLOCK = 9;
	public const CRAFTING_RECIPE = 10;
	public const CRAFTING_RECIPE_AUTO = 11; //recipe book?
	public const CREATIVE_CREATE = 12;
	public const CRAFTING_RECIPE_OPTIONAL = 13; //anvil/cartography table rename
	public const CRAFTING_NON_IMPLEMENTED_DEPRECATED_ASK_TY_LAING = 14;
	public const CRAFTING_RESULTS_DEPRECATED_ASK_TY_LAING = 15; //no idea what this is for

}
