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
use pocketmine\level\generator\vanilla\item\ItemFactory;

class PotionContainerChangeRecipe implements BrewingRecipe{

	private RecipeIngredient $ingredient;

	public function __construct(
		private int $inputItemId,
		RecipeIngredient|Item $ingredient,
		private int $outputItemId
	){
		if ($ingredient instanceof Item) {
			$ingredient = new ExactRecipeIngredient($ingredient);
		}

		$this->ingredient = $ingredient;
	}

	public function getInputItemId() : int{
		return $this->inputItemId;
	}

	public function getIngredient() : RecipeIngredient{
		return $this->ingredient;
	}

	public function getOutputItemId() : int{
		return $this->outputItemId;
	}

	public function getResultFor(Item $input) : ?Item{
		return $input->getId() === $this->getInputItemId() ? ItemFactory::get($this->getOutputItemId(), $input->getDamage()) : null;
	}
}
