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

use pocketmine\level\generator\vanilla\item\Armor;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\item\trim\ItemTrimMaterialType;
use pocketmine\level\generator\vanilla\item\trim\ItemTrimPatternType;
use pocketmine\level\generator\vanilla\item\trim\TrimData;
use pocketmine\level\generator\vanilla\item\trim\TrimFactory;

readonly class SmithingTrimRecipe implements SmithingRecipe {
	public function __construct(
		private RecipeIngredient $input,
		private RecipeIngredient $addition,
		private RecipeIngredient $template
	){}

	public function getInput() : RecipeIngredient{
		return $this->input;
	}

	public function getAddition() : RecipeIngredient{
		return $this->addition;
	}

	public function getTemplate() : RecipeIngredient{
		return $this->template;
	}

	public function getResult(Item $template, Item $input, Item $addition) : ?Item{
		if($input instanceof Armor){
			$trimFactory = TrimFactory::getInstance();
			$patternType = null;
			foreach($trimFactory->getTrimPatterns() as $trimPattern){
				if($trimPattern->item->equals($template, true, false)){
					$patternType = ItemTrimPatternType::tryFrom($trimPattern->name);
					break;
				}
			}
			$materialType = null;
			foreach($trimFactory->getTrimMaterials() as $trimMaterial){
				if($trimMaterial->item->equals($addition, true, false)){
					$materialType = ItemTrimMaterialType::tryFrom($trimMaterial->name);
					break;
				}
			}
			if($patternType !== null && $materialType !== null){
				$result = clone $input;
				$result->setTrim(new TrimData($patternType, $materialType));
				return $result;
			}
		}
		return null;
	}

	/**
	 * @return Item[]
	 */
	public function getResultsFor(CraftingGrid $grid) : array{
		return []; // To be implemented via transaction logic
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
