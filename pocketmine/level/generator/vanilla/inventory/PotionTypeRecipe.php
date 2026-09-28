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

class PotionTypeRecipe implements BrewingRecipe{

	private RecipeIngredient $input;
	private RecipeIngredient $ingredient;

	public function __construct(
		RecipeIngredient $input,
		RecipeIngredient $ingredient,
		private Item $output
	){
		if ($input instanceof Item) {
			$input = new ExactRecipeIngredient($input);
		}
		if ($ingredient instanceof Item) {
			$ingredient = new ExactRecipeIngredient($ingredient);
		}

		$this->ingredient = $ingredient;
		$this->input = $input;
		$this->output = clone $output;
	}

	public function getInput() : RecipeIngredient{
		return $this->input;
	}

	public function getIngredient() : RecipeIngredient{
		return $this->ingredient;
	}

	public function getOutput() : Item{
		return clone $this->output;
	}

	public function getResultFor(Item $input) : ?Item{
		return $this->input->accepts($input) ? $this->getOutput() : null;
	}
}
