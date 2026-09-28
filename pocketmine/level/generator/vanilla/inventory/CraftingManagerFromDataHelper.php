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
use pocketmine\level\generator\vanilla\item\ItemIds;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ProtocolInfo;
use pocketmine\level\generator\vanilla\utils\AssumptionFailedError;
use pocketmine\level\generator\vanilla\utils\Filesystem;
use Symfony\Component\Filesystem\Path;
use function array_diff;
use function array_map;
use function file_exists;
use function is_array;
use function json_decode;
use function scandir;
use function sort;
use const pocketmine\BEDROCK_DATA_PATH;

final class CraftingManagerFromDataHelper{

	private static function deserializeIngredient(array $data) : ?RecipeIngredient{
		if(isset($data["count"]) && $data["count"] !== 1){
			//every case we've seen so far where this isn't the case, it's been a bug and the count was ignored anyway
			//e.g. gold blocks crafted from 9 ingots, but each input item individually had a count of 9
			throw new AssumptionFailedError("Recipe inputs should have a count of exactly 1");
		}
		if(isset($data["tag"])){
			return new TagWildcardRecipeIngredient($data["tag"]);
		}

		$damage = $data["damage"] ?? null;
		if($damage === -1){
			//this could be an unimplemented item, but it doesn't really matter, since the item shouldn't be able to
			//be obtained anyway - filtering unknown items is only really important for outputs, to prevent players
			//obtaining them
			return new MetaWildcardRecipeIngredient($data["id"]);
		}

		return new ExactRecipeIngredient(Item::jsonDeserialize($data));
	}

	/**
	 * @param Item[] $items
	 */
	private static function containsUnknownOutputs(array $items) : bool{
		foreach($items as $item){
			if($item->hasAnyDamageValue()){
				throw new \InvalidArgumentException("Recipe outputs must not have wildcard meta values");
			}
			if(!ItemFactory::isRegistered($item->getId(), $item->getDamage())){
				return true;
			}
		}

		return false;
	}

	private static function loadJsonFile(int $protocolVersion, string $filename) : array{
		$array = json_decode(Filesystem::resourceGetContents(Path::join(BEDROCK_DATA_PATH . 'recipes/' . $protocolVersion . '/', $filename)), true);
		if(!is_array($array)){
			throw new AssumptionFailedError($filename . " root should contain a map of recipe types");
		}

		return $array;
	}

	public static function make() : CraftingManager{
		$result = new CraftingManager();

		$itemDeserializerOutputFuncFix = function (array $data) {
			$data["damage"] ??= 0;
			return Item::jsonDeserialize($data);
		};

		$protocols = [];
		foreach (array_diff(scandir(BEDROCK_DATA_PATH . 'recipes/'), ["..", "."]) as $protocol) {
			$protocols[] = (int) $protocol;
		}

		sort($protocols);

		$protocolRecipes = [];
		foreach ($protocols as $protocol) {
			foreach ([
				"shapeless_crafting",
				"shaped_crafting",
				"smelting",
				"potion_type",
				"potion_container_change",
				"smithing",
				"smithing_trim"
			] as $recipeType) {
				if (!Filesystem::resourceExists(BEDROCK_DATA_PATH . 'recipes/' . $protocol . '/' . $recipeType . '.json')) {
					$protocolRecipes[$recipeType] = $protocolRecipes[$recipeType] ?? ProtocolInfo::PROTOCOL_527;
				} else {
					$protocolRecipes[$recipeType] = $protocol;
				}
			}

			if ($protocolRecipes["shapeless_crafting"] === $protocol) {
				foreach (self::loadJsonFile($protocol, "shapeless_crafting.json") as $recipe) {
					$recipeType = match ($recipe["block"]) {
						"crafting_table" => ShapelessRecipeType::CRAFTING(),
						"stonecutter" => ShapelessRecipeType::STONECUTTER(),
						"cartography_table" => ShapelessRecipeType::CARTOGRAPHY(),
						default => null
					};
					if ($recipeType === null) {
						continue;
					}
					$output = array_map($itemDeserializerOutputFuncFix, $recipe["output"]);
					if (self::containsUnknownOutputs($output)) {
						continue;
					}

					$inputs = [];
					foreach ($recipe["input"] as $inputData) {
						$input = self::deserializeIngredient($inputData);
						if ($input === null) { //unknown input item
							continue 2;
						}
						$inputs[] = $input;
					}

					$result->registerShapelessRecipe(new ShapelessRecipe($inputs, $output, $recipeType), $protocol);
				}
			}

			if ($protocolRecipes["shaped_crafting"] === $protocol) {
				foreach (self::loadJsonFile($protocol, "shaped_crafting.json") as $recipe) {
					if ($recipe["block"] !== "crafting_table") { //TODO: filter others out for now to avoid breaking economics
						continue;
					}
					$output = array_map($itemDeserializerOutputFuncFix, $recipe["output"]);
					if (self::containsUnknownOutputs($output)) {
						continue;
					}

					$inputs = [];
					foreach ($recipe["input"] as $symbol => $inputData) {
						$input = self::deserializeIngredient($inputData);
						if ($input === null) { //unknown input item
							continue 2;
						}
						$inputs[$symbol] = $input;
					}

					$result->registerShapedRecipe(new ShapedRecipe($recipe["shape"], $inputs, $output), $protocol);
				}
			}

			if ($protocolRecipes["smelting"] === $protocol) {
				foreach (self::loadJsonFile($protocol, "smelting.json") as $recipe) {
					$furnaceType = match ($recipe["block"]) {
						"furnace" => FurnaceType::FURNACE(),
						"blast_furnace" => FurnaceType::BLAST_FURNACE(),
						"smoker" => FurnaceType::SMOKER(),
						"campfire" => FurnaceType::CAMPFIRE(),
						"soul_campfire" => FurnaceType::SOUL_CAMPFIRE(),
						default => null
					};
					if ($furnaceType === null) {
						continue;
					}
					$output = $itemDeserializerOutputFuncFix($recipe["output"]);
					if (self::containsUnknownOutputs([$output])) {
						continue;
					}
					$input = self::deserializeIngredient($recipe["input"]);
					if ($input === null) {
						continue;
					}
					$result->registerFurnaceRecipe(new FurnaceRecipe($output, $input), $furnaceType, $protocol);
				}
			}

			if ($protocol >= ProtocolInfo::PROTOCOL_407) {
				if ($protocolRecipes["potion_type"] === $protocol) {
					foreach (self::loadJsonFile($protocol, "potion_type.json") as $recipe) {
						$output = $itemDeserializerOutputFuncFix($recipe["output"]);
						if (self::containsUnknownOutputs([$output])) {
							continue;
						}
						$input = self::deserializeIngredient($recipe["input"]);
						$ingredient = self::deserializeIngredient($recipe["ingredient"]);
						if ($input === null || $ingredient === null) {
							continue;
						}
						$result->registerPotionTypeRecipe(new PotionTypeRecipe($input, $ingredient, $output), $protocol);
					}
				}

				if ($protocolRecipes["potion_container_change"] === $protocol) {
					foreach (self::loadJsonFile($protocol, "potion_container_change.json") as $recipe) {
						if (!ItemFactory::isRegistered($recipe["input_item_id"]) || !ItemFactory::isRegistered($recipe["output_item_id"])) {
							continue;
						}
						$ingredient = self::deserializeIngredient($recipe["ingredient"]);
						if ($ingredient === null) {
							continue;
						}
						$result->registerPotionContainerChangeRecipe(new PotionContainerChangeRecipe($recipe["input_item_id"], $ingredient, $recipe["output_item_id"]), $protocol);
					}
				}
				if ($protocol >= ProtocolInfo::PROTOCOL_567) {
					if ($protocolRecipes["smithing"] === $protocol) {
						foreach (self::loadJsonFile($protocol, "smithing.json") as $recipe) {
							$output = $itemDeserializerOutputFuncFix($recipe["output"]);
							if (self::containsUnknownOutputs([$output])) {
								continue;
							}
							$addition = self::deserializeIngredient($recipe["addition"]);
							$input = self::deserializeIngredient($recipe["input"]);
							$template = isset($recipe["template"]) ? self::deserializeIngredient($recipe["template"]) : new ExactRecipeIngredient(ItemFactory::get(ItemIds::NETHERITE_UPGRADE_SMITHING_TEMPLATE));
							if ($addition === null || $input === null || $template === null) {
								continue;
							}
							$result->registerSmithingTransformRecipe(new SmithingTransformRecipe($input, $addition, $template, $output), $protocol);
						}
					}
					if ($protocol >= ProtocolInfo::PROTOCOL_582) {
						if ($protocolRecipes["smithing_trim"] === $protocol) {
							foreach (self::loadJsonFile($protocol, "smithing_trim.json") as $recipe) {
								$addition = self::deserializeIngredient($recipe["addition"]);
								$input = self::deserializeIngredient($recipe["input"]);
								$template = self::deserializeIngredient($recipe["template"]);
								if ($addition === null || $input === null || $template === null) {
									continue;
								}
								$result->registerSmithingTrimRecipe(new SmithingTrimRecipe($input, $addition, $template), $protocol);
							}
						}
					}
				}
			}
		}

		return $result;
	}
}
