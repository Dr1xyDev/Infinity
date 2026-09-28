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
use pocketmine\level\generator\vanilla\utils\Utils;
use function count;

class ShapelessRecipe implements CraftingRecipe{
	/**
	 * @var RecipeIngredient[]
	 * @phpstan-var list<RecipeIngredient>
	 */
	private array $ingredients = [];
	/**
	 * @var Item[]
	 * @phpstan-var list<Item>
	 */
	private array $results;
	private ShapelessRecipeType $type;

	/**
	 * @param RecipeIngredient[]|Item[] $ingredients No more than 9 total. This applies to sum of item stack counts, not count of array.
	 * @param Item[]                    $results     List of result items created by this recipe.
	 *
	 * @phpstan-param list<RecipeIngredient|Item> $ingredients
	 * @phpstan-param list<Item>             $results
	 */
	public function __construct(array $ingredients, array $results, ?ShapelessRecipeType $type = null){
		foreach ($ingredients as $i => $ingredient) {
			if ($ingredient instanceof Item) {
				$ingredients[$i] = new ExactRecipeIngredient($ingredient);
			}
		}

		$this->type = $type ?? ShapelessRecipeType::CRAFTING();

		if(count($ingredients) > 9){
			throw new \InvalidArgumentException("Shapeless recipes cannot have more than 9 ingredients");
		}
		Utils::validateArrayValueType($ingredients, function(RecipeIngredient $_) : void{});
		$this->ingredients = $ingredients;
		Utils::validateArrayValueType($results, function(Item $_) : void{});
		$this->results = Utils::cloneObjectArray($results);
	}

	/**
	 * @return Item[]
	 * @phpstan-return list<Item>
	 */
	public function getResults() : array{
		return Utils::cloneObjectArray($this->results);
	}

	public function getResultsFor(CraftingGrid $grid) : array{
		return $this->getResults();
	}

	public function getType() : ShapelessRecipeType{
		return $this->type;
	}

	public function getIngredientList() : array{
		return $this->ingredients;
	}

	public function getIngredientCount() : int{
		return count($this->ingredients);
	}

	public function matchesCraftingGrid(CraftingGrid $grid) : bool{
		//don't pack the ingredients - shapeless recipes require that each ingredient be in a separate slot
		$input = $grid->getContents();

		foreach($this->ingredients as $ingredient){
			foreach($input as $j => $haveItem){
				if($ingredient->accepts($haveItem)){
					unset($input[$j]);
					continue 2;
				}
			}

			return false; //failed to match the needed item to a given item
		}

		return count($input) === 0; //crafting grid should be empty apart from the given ingredient stacks
	}
}
