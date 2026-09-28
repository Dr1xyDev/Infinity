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

namespace pocketmine\level\generator\vanilla\network\mcpe\protocol;

use pocketmine\level\generator\vanilla\network\mcpe\NetworkSession;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\PotionContainerChangeRecipe;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\PotionTypeRecipe;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\recipe\FurnaceRecipe;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\recipe\MaterialReducerRecipe;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\recipe\MaterialReducerRecipeOutput;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\recipe\MultiRecipe;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\recipe\RecipeWithTypeId;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\recipe\ShapedRecipe;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\recipe\ShapelessRecipe;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\recipe\SmithingTransformRecipe;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\recipe\SmithingTrimRecipe;

use function array_filter;
use function array_values;
use function count;

class CraftingDataPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::CRAFTING_DATA_PACKET;

	public const ENTRY_SHAPELESS = 0;
	public const ENTRY_SHAPED = 1;
	public const ENTRY_FURNACE = 2;
	public const ENTRY_FURNACE_DATA = 3;
	public const ENTRY_MULTI = 4;
	public const ENTRY_USER_DATA_SHAPELESS = 5;
	public const ENTRY_SHAPELESS_CHEMISTRY = 6;
	public const ENTRY_SHAPED_CHEMISTRY = 7;
	public const ENTRY_SMITHING_TRANSFORM = 8;
	public const ENTRY_SMITHING_TRIM = 9;

	/** @var RecipeWithTypeId[] */
	public array $recipesWithTypeIds = [];
	/** @var PotionTypeRecipe[] */
	public array $potionTypeRecipes = [];
	/** @var PotionContainerChangeRecipe[] */
	public array $potionContainerRecipes = [];
	/** @var MaterialReducerRecipe[] */
	public array $materialReducerRecipes = [];
	public bool $cleanRecipes = false;

	/**
	 * @generate-create-func
	 * @param RecipeWithTypeId[]            $recipesWithTypeIds
	 * @param PotionTypeRecipe[]            $potionTypeRecipes
	 * @param PotionContainerChangeRecipe[] $potionContainerRecipes
	 * @param MaterialReducerRecipe[]       $materialReducerRecipes
	 */
	public static function create(array $recipesWithTypeIds, array $potionTypeRecipes, array $potionContainerRecipes, array $materialReducerRecipes, bool $cleanRecipes) : self{
		$result = new self();
		$result->recipesWithTypeIds = $recipesWithTypeIds;
		$result->potionTypeRecipes = $potionTypeRecipes;
		$result->potionContainerRecipes = $potionContainerRecipes;
		$result->materialReducerRecipes = $materialReducerRecipes;
		$result->cleanRecipes = $cleanRecipes;
		return $result;
	}

	/** Since 1.26.40 each recipe type is sent in its own list, in this order */
	private const RECIPE_LIST_ORDER_V2168 = [
		self::ENTRY_SHAPED,
		self::ENTRY_SHAPELESS,
		self::ENTRY_MULTI,
		self::ENTRY_USER_DATA_SHAPELESS,
		self::ENTRY_SHAPELESS_CHEMISTRY,
		self::ENTRY_SHAPED_CHEMISTRY,
		self::ENTRY_SMITHING_TRANSFORM,
		self::ENTRY_SMITHING_TRIM,
	];

	private function decodeRecipe(int $recipeType) : RecipeWithTypeId
	{
		return match($recipeType){
			self::ENTRY_SHAPELESS, self::ENTRY_USER_DATA_SHAPELESS, self::ENTRY_SHAPELESS_CHEMISTRY => ShapelessRecipe::decode($recipeType, $this),
			self::ENTRY_SHAPED, self::ENTRY_SHAPED_CHEMISTRY => ShapedRecipe::decode($recipeType, $this),
			self::ENTRY_FURNACE, self::ENTRY_FURNACE_DATA => FurnaceRecipe::decode($recipeType, $this),
			self::ENTRY_MULTI => MultiRecipe::decode($recipeType, $this),
			self::ENTRY_SMITHING_TRANSFORM => SmithingTransformRecipe::decode($recipeType, $this),
			self::ENTRY_SMITHING_TRIM => SmithingTrimRecipe::decode($recipeType, $this),
			default => throw new PacketDecodeException("Unhandled recipe type $recipeType"),
		};
	}

	private function decodePayloadV2168() : void
	{
		foreach (self::RECIPE_LIST_ORDER_V2168 as $recipeType) {
			foreach ($this->getList(fn() => $this->decodeRecipe($recipeType), 1 << 16) as $recipe) {
				$this->recipesWithTypeIds[] = $recipe;
			}
		}
		$this->potionTypeRecipes = $this->getList(fn() => new PotionTypeRecipe($this->getVarInt(), $this->getVarInt(), $this->getVarInt(), $this->getVarInt(), $this->getVarInt(), $this->getVarInt()), 1 << 16);
		$this->potionContainerRecipes = $this->getList(fn() => new PotionContainerChangeRecipe($this->getVarInt(), $this->getVarInt(), $this->getVarInt()), 1 << 16);
		$this->materialReducerRecipes = $this->getList(function() : MaterialReducerRecipe{
			$inputIdAndData = $this->getVarInt();
			$outputs = $this->getList(fn() => new MaterialReducerRecipeOutput($this->getVarInt(), $this->getVarInt()), 1024);
			return new MaterialReducerRecipe($inputIdAndData >> 16, $inputIdAndData & 0x7fff, $outputs);
		}, 1 << 16);
		$this->cleanRecipes = $this->getBool();
	}

	private function encodePayloadV2168() : void
	{
		$recipesByType = [];
		foreach ($this->recipesWithTypeIds as $recipe) {
			//furnace recipes are sent as shapeless recipes since 1.26.20
			if (!$recipe instanceof FurnaceRecipe) {
				$recipesByType[$recipe->getTypeId()][] = $recipe;
			}
		}
		foreach (self::RECIPE_LIST_ORDER_V2168 as $recipeType) {
			$this->putList($recipesByType[$recipeType] ?? [], fn(RecipeWithTypeId $recipe) => $recipe->encode($this));
		}
		$this->putList($this->potionTypeRecipes, function($recipe) : void{
			$this->putVarInt($recipe->getInputItemId());
			$this->putVarInt($recipe->getInputItemMeta());
			$this->putVarInt($recipe->getIngredientItemId());
			$this->putVarInt($recipe->getIngredientItemMeta());
			$this->putVarInt($recipe->getOutputItemId());
			$this->putVarInt($recipe->getOutputItemMeta());
		});
		$this->putList($this->potionContainerRecipes, function($recipe) : void{
			$this->putVarInt($recipe->getInputItemId());
			$this->putVarInt($recipe->getIngredientItemId());
			$this->putVarInt($recipe->getOutputItemId());
		});
		$this->putList($this->materialReducerRecipes, function($recipe) : void{
			$this->putVarInt(($recipe->getInputItemId() << 16) | $recipe->getInputItemMeta());
			$this->putList($recipe->getOutputs(), function($output) : void{
				$this->putVarInt($output->getItemId());
				$this->putVarInt($output->getCount());
			});
		});
		$this->putBool($this->cleanRecipes);
	}

	protected function decodePayload() : void
	{
		if ($this->protocol >= ProtocolInfo::PROTOCOL_2168) {
			$this->decodePayloadV2168();
			return;
		}

		$recipeCount = $this->getUnsignedVarInt();
		$previousType = "none";
		for($i = 0; $i < $recipeCount; ++$i){
			$recipeType = $this->getVarInt();

			$this->recipesWithTypeIds[] = match($recipeType){
				self::ENTRY_SHAPELESS, self::ENTRY_USER_DATA_SHAPELESS, self::ENTRY_SHAPELESS_CHEMISTRY => ShapelessRecipe::decode($recipeType, $this),
				self::ENTRY_SHAPED, self::ENTRY_SHAPED_CHEMISTRY => ShapedRecipe::decode($recipeType, $this),
				self::ENTRY_FURNACE, self::ENTRY_FURNACE_DATA => FurnaceRecipe::decode($recipeType, $this),
				self::ENTRY_MULTI => MultiRecipe::decode($recipeType, $this),
				self::ENTRY_SMITHING_TRANSFORM => SmithingTransformRecipe::decode($recipeType, $this),
				self::ENTRY_SMITHING_TRIM => SmithingTrimRecipe::decode($recipeType, $this),
				default => throw new PacketDecodeException("Unhandled recipe type $recipeType (previous was $previousType)"),
			};
			$previousType = $recipeType;
		}
		if ($this->protocol >= ProtocolInfo::PROTOCOL_407) {
			for ($i = 0, $count = $this->getUnsignedVarInt(); $i < $count; ++$i) {
				$inputId = $this->getVarInt();
				$inputMeta = $this->getVarInt();
				$ingredientId = $this->getVarInt();
				$ingredientMeta = $this->getVarInt();
				$outputId = $this->getVarInt();
				$outputMeta = $this->getVarInt();
				$this->potionTypeRecipes[] = new PotionTypeRecipe($inputId, $inputMeta, $ingredientId, $ingredientMeta, $outputId, $outputMeta);
			}
			for ($i = 0, $count = $this->getUnsignedVarInt(); $i < $count; ++$i) {
				$input = $this->getVarInt();
				$ingredient = $this->getVarInt();
				$output = $this->getVarInt();
				$this->potionContainerRecipes[] = new PotionContainerChangeRecipe($input, $ingredient, $output);
			}

			if($this->protocol >= ProtocolInfo::PROTOCOL_465) {
				for ($i = 0, $count = $this->getUnsignedVarInt(); $i < $count; ++$i) {
					$inputIdAndData = $this->getVarInt();
					[$inputId, $inputMeta] = [$inputIdAndData >> 16, $inputIdAndData & 0x7fff];
					$outputs = [];
					for ($j = 0, $outputCount = $this->getUnsignedVarInt(); $j < $outputCount; ++$j) {
						$outputItemId = $this->getVarInt();
						$outputItemCount = $this->getVarInt();
						$outputs[] = new MaterialReducerRecipeOutput($outputItemId, $outputItemCount);
					}
					$this->materialReducerRecipes[] = new MaterialReducerRecipe($inputId, $inputMeta, $outputs);
				}
			}
		}
		$this->cleanRecipes = $this->getBool();
	}

	protected function encodePayload() : void {
		if ($this->protocol >= ProtocolInfo::PROTOCOL_2168) {
			$this->encodePayloadV2168();
			return;
		}

		$recipes = $this->recipesWithTypeIds;
		if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_975) {
			//furnace recipes are sent as shapeless recipes since 1.26.20; the count must not include skipped entries
			$recipes = array_values(array_filter($recipes, fn(RecipeWithTypeId $d) => !$d instanceof FurnaceRecipe));
		}
		$this->putUnsignedVarInt(count($recipes));
		foreach($recipes as $d){
			$this->putVarInt($d->getTypeId());
			$d->encode($this);
		}

		if ($this->protocol >= ProtocolInfo::PROTOCOL_407) {
			$this->putUnsignedVarInt(count($this->potionTypeRecipes));
			foreach($this->potionTypeRecipes as $recipe) {
				$this->putVarInt($recipe->getInputItemId());
				$this->putVarInt($recipe->getInputItemMeta());
				$this->putVarInt($recipe->getIngredientItemId());
				$this->putVarInt($recipe->getIngredientItemMeta());
				$this->putVarInt($recipe->getOutputItemId());
				$this->putVarInt($recipe->getOutputItemMeta());
			}

			$this->putUnsignedVarInt(count($this->potionContainerRecipes));
			foreach($this->potionContainerRecipes as $recipe){
				$this->putVarInt($recipe->getInputItemId());
				$this->putVarInt($recipe->getIngredientItemId());
				$this->putVarInt($recipe->getOutputItemId());
			}

			if($this->protocol >= ProtocolInfo::PROTOCOL_465) {
				$this->putUnsignedVarInt(count($this->materialReducerRecipes));
				foreach ($this->materialReducerRecipes as $recipe) {
					$this->putVarInt(($recipe->getInputItemId() << 16) | $recipe->getInputItemMeta());
					$this->putUnsignedVarInt(count($recipe->getOutputs()));
					foreach ($recipe->getOutputs() as $output) {
						$this->putVarInt($output->getItemId());
						$this->putVarInt($output->getCount());
					}
				}
			}
		}

		$this->putBool($this->cleanRecipes);
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleCraftingData($this);
	}
}
