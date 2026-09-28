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

class SmithingTransformRecipe implements SmithingRecipe {

	private Item $output;

	public function __construct(
		private readonly RecipeIngredient $input,
		private readonly RecipeIngredient $addition,
		private readonly RecipeIngredient $template,
		Item $output
	){
		$this->output = clone $output;
	}

	public function getInput() : RecipeIngredient{
		return $this->input;
	}

	public function getAddition() : RecipeIngredient{
		return $this->addition;
	}

	public function getTemplate() : RecipeIngredient{
		return $this->template;
	}

	public function getOutput() : Item{
		return clone $this->output;
	}

	public function getResult(Item $template, Item $input, Item $addition) : ?Item{
		return $this->getOutput();
	}

	/**
	 * @return Item[]
	 */
	public function getResultsFor(CraftingGrid $grid) : array{
		return [$this->getOutput()];
	}

	/**
	 * @return RecipeIngredient[]
	 */
	public function getIngredientList() : array{
		return [$this->template, $this->input, $this->addition];
	}

	public function matchesCraftingGrid(CraftingGrid $grid) : bool{
		return false; // Custom mechanics for smithing table
	}
}
