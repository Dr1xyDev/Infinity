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

namespace pocketmine\level\generator\vanilla\network\mcpe\cache;

use pocketmine\level\generator\vanilla\inventory\CraftingManager;
use pocketmine\level\generator\vanilla\inventory\FurnaceType;
use pocketmine\level\generator\vanilla\inventory\RecipeIngredient;
use pocketmine\level\generator\vanilla\inventory\ShapedRecipe;
use pocketmine\level\generator\vanilla\inventory\ShapelessRecipe;
use pocketmine\level\generator\vanilla\inventory\ShapelessRecipeType;
use pocketmine\level\generator\vanilla\inventory\SmithingTransformRecipe;
use pocketmine\level\generator\vanilla\inventory\SmithingTrimRecipe;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\network\mcpe\convert\ItemTranslator;
use pocketmine\level\generator\vanilla\network\mcpe\convert\TypeConverter;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\CraftingDataPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ProtocolInfo;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\inventory\ItemStack;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\recipe\CraftingRecipeBlockName;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\recipe\FurnaceRecipe as ProtocolFurnaceRecipe;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\recipe\FurnaceRecipeBlockName;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\recipe\IntIdMetaItemDescriptor;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\recipe\PotionContainerChangeRecipe as ProtocolPotionContainerChangeRecipe;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\recipe\PotionTypeRecipe as ProtocolPotionTypeRecipe;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\recipe\RecipeIngredient as ProtocolRecipeIngredient;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\recipe\RecipeUnlockingRequirement;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\recipe\ShapedRecipe as ProtocolShapedRecipe;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\recipe\ShapelessRecipe as ProtocolShapelessRecipe;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\recipe\SmithingTransformRecipe as ProtocolSmithingTransformRecipe;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\recipe\SmithingTrimRecipe as ProtocolSmithingTrimRecipe;
use pocketmine\level\generator\vanilla\timings\Timings;
use pocketmine\level\generator\vanilla\utils\AssumptionFailedError;
use pocketmine\level\generator\vanilla\utils\Binary;
use pocketmine\level\generator\vanilla\utils\SingletonTrait;
use pocketmine\level\generator\vanilla\utils\UUID;
use function array_map;
use function spl_object_id;
use function str_repeat;

final class CraftingDataCache{
	use SingletonTrait;

	/**
	 * @var CraftingDataPacket[][]
	 * @phpstan-var array<int, CraftingDataPacket>
	 */
	private array $caches = [];

	/**
	 * The client doesn't like recipes with ID 0 (as of 1.21.100) and complains about them in the content log
	 * This doesn't actually affect the function of the recipe, but it is annoying, so this offset fixes it
	 */
	public const RECIPE_ID_OFFSET = 1;

	public function getCache(CraftingManager $manager, int $protocolVersion) : CraftingDataPacket{
		$id = spl_object_id($manager);
		if(!isset($this->caches[$id][$protocolVersion])){
			//recipes only change through the manager, which clears this cache, so the packet is encoded only once
			$this->caches[$id][$protocolVersion] = $this->buildCraftingDataCache($manager, $protocolVersion)->enableEncodedCache();
		}

		return $this->caches[$id][$protocolVersion];
	}

	public function clearCache(CraftingManager $manager) : void{
		unset($this->caches[spl_object_id($manager)]);
	}

	/**
	 * Rebuilds the cached CraftingDataPacket.
	 */
	private function buildCraftingDataCache(CraftingManager $manager, int $protocolVersion) : CraftingDataPacket{
		Timings::$craftingDataCacheRebuild->startTiming();

		$nullUUID = UUID::fromBinary(str_repeat("\x00", 16), 0);
		$converter = TypeConverter::getInstance();
		$recipesWithTypeIds = [];

		$noUnlockingRequirement = new RecipeUnlockingRequirement(null);
		foreach ($manager->getCraftingRecipeIndex($protocolVersion) as $index => $recipe) {
			//the client doesn't like recipes with an ID of 0, so we need to offset them
			$recipeNetId = $index + self::RECIPE_ID_OFFSET;
			if ($recipe instanceof ShapelessRecipe) {
				$typeTag = match ($recipe->getType()) {
					ShapelessRecipeType::CRAFTING => CraftingRecipeBlockName::CRAFTING_TABLE,
					ShapelessRecipeType::STONECUTTER => CraftingRecipeBlockName::STONECUTTER,
					ShapelessRecipeType::CARTOGRAPHY => CraftingRecipeBlockName::CARTOGRAPHY_TABLE,
					ShapelessRecipeType::SMITHING => CraftingRecipeBlockName::SMITHING_TABLE,
				};

				$recipesWithTypeIds[] = new ProtocolShapelessRecipe(
					CraftingDataPacket::ENTRY_SHAPELESS,
					Binary::writeInt($recipeNetId),
					array_map(fn(RecipeIngredient $ingredient) : ProtocolRecipeIngredient => $converter->coreRecipeIngredientToNet($ingredient, $protocolVersion), $recipe->getIngredientList()),
					array_map(fn(Item $result) : ItemStack => $converter->coreItemStackToNet($result, $protocolVersion), $recipe->getResults()),
					$nullUUID,
					$typeTag,
					50,
					$noUnlockingRequirement,
					$recipeNetId
				);
			} elseif ($recipe instanceof ShapedRecipe) {
				$inputs = [];

				for ($row = 0, $height = $recipe->getHeight(); $row < $height; ++$row) {
					for ($column = 0, $width = $recipe->getWidth(); $column < $width; ++$column) {
						$inputs[$row][$column] = $converter->coreRecipeIngredientToNet($recipe->getIngredient($column, $row), $protocolVersion);
					}
				}

				$recipesWithTypeIds[] = new ProtocolShapedRecipe(
					CraftingDataPacket::ENTRY_SHAPED,
					Binary::writeInt($recipeNetId),
					$inputs,
					array_map(fn(Item $result) : ItemStack => $converter->coreItemStackToNet($result, $protocolVersion), $recipe->getResults()),
					$nullUUID,
					CraftingRecipeBlockName::CRAFTING_TABLE,
					50,
					true,
					$noUnlockingRequirement,
					$recipeNetId,
				);
			} elseif ($recipe instanceof SmithingTransformRecipe) {
				$template = $converter->coreRecipeIngredientToNet($recipe->getTemplate(), $protocolVersion);
				$input = $converter->coreRecipeIngredientToNet($recipe->getInput(), $protocolVersion);
				$addition = $converter->coreRecipeIngredientToNet($recipe->getAddition(), $protocolVersion);
				$output = $converter->coreItemStackToNet($recipe->getOutput(), $protocolVersion);
				$recipesWithTypeIds[] = new ProtocolSmithingTransformRecipe(
					CraftingDataPacket::ENTRY_SMITHING_TRANSFORM,
					Binary::writeInt($recipeNetId),
					$template,
					$input,
					$addition,
					$output,
					CraftingRecipeBlockName::SMITHING_TABLE,
					$recipeNetId
				);
			} elseif ($recipe instanceof SmithingTrimRecipe) {
				$template = $converter->coreRecipeIngredientToNet($recipe->getTemplate(), $protocolVersion);
				$input = $converter->coreRecipeIngredientToNet($recipe->getInput(), $protocolVersion);
				$addition = $converter->coreRecipeIngredientToNet($recipe->getAddition(), $protocolVersion);
				$recipesWithTypeIds[] = new ProtocolSmithingTrimRecipe(
					CraftingDataPacket::ENTRY_SMITHING_TRIM,
					Binary::writeInt($recipeNetId),
					$template,
					$input,
					$addition,
					CraftingRecipeBlockName::SMITHING_TABLE,
					$recipeNetId
				);
			} else {
				//TODO: probably special recipe types
			}
		}

		foreach ($manager->getFurnaceRecipes($protocolVersion) as $furnaceTypeName => $recipes) {
			$typeTag = match ($furnaceTypeName) {
				FurnaceType::FURNACE->name() => FurnaceRecipeBlockName::FURNACE,
				FurnaceType::BLAST_FURNACE->name() => FurnaceRecipeBlockName::BLAST_FURNACE,
				FurnaceType::SMOKER->name() => FurnaceRecipeBlockName::SMOKER,
				FurnaceType::CAMPFIRE->name() => FurnaceRecipeBlockName::CAMPFIRE,
				FurnaceType::SOUL_CAMPFIRE->name() => FurnaceRecipeBlockName::SOUL_CAMPFIRE
			};

			foreach ($recipes as $recipe) {
				if ($protocolVersion >= ProtocolInfo::PROTOCOL_975) {
					$recipeNetId = ($recipeNetId ?? self::RECIPE_ID_OFFSET) + 1;
					$recipesWithTypeIds[] = new ProtocolShapelessRecipe(
						CraftingDataPacket::ENTRY_SHAPELESS,
						Binary::writeInt($recipeNetId), //TODO: this should probably be changed to something human-readable
						[$converter->coreRecipeIngredientToNet($recipe->getInput(), $protocolVersion)],
						[$converter->coreItemStackToNet($recipe->getResult(), $protocolVersion)],
						$nullUUID,
						$typeTag,
						50,
						$noUnlockingRequirement,
						$recipeNetId
					);
				} else {
					$input = $converter->coreRecipeIngredientToNet($recipe->getInput(), $protocolVersion)->getDescriptor();
					if (!$input instanceof IntIdMetaItemDescriptor) {
						throw new AssumptionFailedError();
					}
					$recipesWithTypeIds[] = new ProtocolFurnaceRecipe(
						CraftingDataPacket::ENTRY_FURNACE_DATA,
						$input->getId(),
						$input->getMeta(),
						$converter->coreItemStackToNet($recipe->getResult(), $protocolVersion),
						$typeTag
					);
				}
			}
		}

		$potionTypeRecipes = [];
		$potionContainerChangeRecipes = [];
		if ($protocolVersion >= ProtocolInfo::PROTOCOL_407) {
			foreach ($manager->getPotionTypeRecipes($protocolVersion) as $recipe) {
				$input = $converter->coreRecipeIngredientToNet($recipe->getInput(), $protocolVersion)->getDescriptor();
				$ingredient = $converter->coreRecipeIngredientToNet($recipe->getIngredient(), $protocolVersion)->getDescriptor();
				if(!$input instanceof IntIdMetaItemDescriptor || !$ingredient instanceof IntIdMetaItemDescriptor){
					throw new AssumptionFailedError();
				}
				$output = $converter->coreItemStackToNet($recipe->getOutput(), $protocolVersion);
				$potionTypeRecipes[] = new ProtocolPotionTypeRecipe(
					$input->getId(),
					$input->getMeta(),
					$ingredient->getId(),
					$ingredient->getMeta(),
					$output->getId(),
					$output->getMeta()
				);
			}

			if ($protocolVersion >= ProtocolInfo::PROTOCOL_419) {
				$itemTranslator = ItemTranslator::getInstance($protocolVersion);
				foreach ($manager->getPotionContainerChangeRecipes($protocolVersion) as $recipe) {
					$ingredient = $converter->coreRecipeIngredientToNet($recipe->getIngredient(), $protocolVersion)->getDescriptor();
					if(!$ingredient instanceof IntIdMetaItemDescriptor){
						throw new AssumptionFailedError();
					}

					$input = $itemTranslator->toNetworkId($recipe->getInputItemId(), 0);
					$output = $itemTranslator->toNetworkId($recipe->getOutputItemId(), 0);
					$potionContainerChangeRecipes[] = new ProtocolPotionContainerChangeRecipe(
						$input[0],
						$ingredient->getId(),
						$output[0]
					);
				}
			} else {
				foreach ($manager->getPotionContainerChangeRecipes($protocolVersion) as $recipe) {
					$ingredient = $converter->coreRecipeIngredientToNet($recipe->getIngredient(), $protocolVersion)->getDescriptor();
					if(!$ingredient instanceof IntIdMetaItemDescriptor){
						throw new AssumptionFailedError();
					}

					$potionContainerChangeRecipes[] = new ProtocolPotionContainerChangeRecipe(
						$recipe->getInputItemId(),
						$ingredient->getId(),
						$recipe->getOutputItemId()
					);
				}
			}
		}

		Timings::$craftingDataCacheRebuild->stopTiming();
		return CraftingDataPacket::create($recipesWithTypeIds, $potionTypeRecipes, $potionContainerChangeRecipes, [], true);
	}
}
