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

/**
 * Recipe ingredient that matches items by their Minecraft ID only. This is used for things like the crafting table
 * recipe from planks (multiple types of planks are accepted).
 *
 * WARNING: Plugins shouldn't usually use this. This is a hack that relies on internal Minecraft behaviour, which might
 * change or break at any time.
 *
 * @internal
 */
final class MetaWildcardRecipeIngredient implements RecipeIngredient{

	public function __construct(
		private int $itemId,
	){}

	public function getItemId() : int{ return $this->itemId; }

	public function accepts(Item $item) : bool{
		if($item->getCount() < 1){
			return false;
		}

		return $item->getId() === $this->itemId;
	}

	public function __toString() : string{
		return "MetaWildcardRecipeIngredient($this->itemId)";
	}
}
