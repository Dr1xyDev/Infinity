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

namespace pocketmine\level\generator\vanilla\inventory;

use pocketmine\level\generator\vanilla\item\Item;

interface CraftingRecipe{
	/**
	 * Returns a list of items needed to craft this recipe. This MUST NOT include Air items or items with a zero count.
	 *
	 * @return RecipeIngredient[]
	 * @phpstan-return list<RecipeIngredient>
	 */
	public function getIngredientList() : array;

	/**
	 * Returns a list of results this recipe will produce when the inputs in the given crafting grid are consumed.
	 *
	 * @return Item[]
	 * @phpstan-return list<Item>
	 */
	public function getResultsFor(CraftingGrid $grid) : array;

	/**
	 * Returns whether the given crafting grid meets the requirements to craft this recipe.
	 */
	public function matchesCraftingGrid(CraftingGrid $grid) : bool;
}
