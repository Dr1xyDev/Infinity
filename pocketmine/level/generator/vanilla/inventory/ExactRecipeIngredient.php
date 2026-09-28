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
 * Recipe ingredient that matches exactly one item, without wildcards.
 * Note that recipe inputs cannot require NBT.
 */
final class ExactRecipeIngredient implements RecipeIngredient{

	public function __construct(private Item $item){
		if($item->isNull()){
			throw new \InvalidArgumentException("Recipe ingredients must not be air items");
		}
		if($item->getCount() !== 1){
			throw new \InvalidArgumentException("Recipe ingredients cannot require count");
		}
		$this->item = clone $item;
	}

	public function getItem() : Item{ return clone $this->item; }

	public function accepts(Item $item) : bool{
		//client-side, recipe inputs can't actually require NBT
		//but on the PM side, we currently check for it if the input requires it, so we have to continue to do so for
		//the sake of consistency
		return $item->getCount() >= 1 && $this->item->equals($item, !$this->item->hasAnyDamageValue(), $this->item->hasNamedTag());
	}

	public function __toString() : string{
		return "ExactRecipeIngredient(" . $this->item . ")";
	}
}
