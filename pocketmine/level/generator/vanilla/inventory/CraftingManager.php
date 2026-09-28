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

use Generator;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\nbt\LittleEndianNBTStream;
use pocketmine\level\generator\vanilla\network\mcpe\cache\CraftingDataCache;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ProtocolInfo;
use pocketmine\level\generator\vanilla\utils\BinaryStream;

use function count;
use function krsort;
use function ksort;
use function usort;

class CraftingManager
{

	/**
	 * @var ShapedRecipe[][][]
	 * @phpstan-var array<int, array<string, list<ShapedRecipe>>>
	 */
	protected array $shapedRecipes = [];
	/**
	 * @var ShapelessRecipe[][][]
	 * @phpstan-var array<int, array<string, list<ShapelessRecipe>>>
	 */
	protected array $shapelessRecipes = [];

	/**
	 * @var FurnaceRecipe[][][]
	 * @phpstan-var array<string, array<int, list<FurnaceRecipe>>>
	 */
	protected array $furnaceRecipes = [];
	/**
	 * @var FurnaceRecipe[][]
	 * @phpstan-var array<int, array<string, array<int, FurnaceRecipe>>>
	 */
	protected array $furnaceRecipeCache = [];

	/**
	 * @var CraftingRecipe[][]
	 * @phpstan-var array<int, array<int, CraftingRecipe>>
	 */
	protected array $craftingRecipeIndexCache = [];

	/**
	 * @var PotionTypeRecipe[][]
	 * @phpstan-var array<int, list<PotionTypeRecipe>>
	 */
	protected array $potionTypeRecipes = [];

	/**
	 * @var PotionContainerChangeRecipe[][]
	 * @phpstan-var array<int, list<PotionContainerChangeRecipe>>
	 */
	protected array $potionContainerChangeRecipes = [];

	/**
	 * @var SmithingTransformRecipe[][]
	 * @phpstan-var array<int, list<SmithingTransformRecipe>>
	 */
	protected array $smithingTransformRecipes = [];

	/**
	 * @var SmithingTrimRecipe[][]
	 * @phpstan-var array<int, list<SmithingTrimRecipe>>
	 */
	protected array $smithingTrimRecipes = [];

	/**
	 * @var BrewingRecipe[][][]
	 * @phpstan-var array<int, array<int, array<int, BrewingRecipe>>>
	 */
	protected array $brewingRecipeCache = [];

	/**
	 * @var SmithingRecipe[][][][]
	 * @phpstan-var array<int, array<string, array<string, array<string, SmithingRecipe>>>>
	 */
	protected array $smithingRecipeCache = [];

	/**
	 * Function used to arrange Shapeless Recipe ingredient lists into a consistent order.
	 */
	public static function sort(Item $i1, Item $i2) : int{
		//Use spaceship operator to compare each property, then try the next one if they are equivalent.
		($retval = $i1->getId() <=> $i2->getId()) === 0 && ($retval = $i1->getDamage() <=> $i2->getDamage()) === 0 && ($retval = $i1->getCount() <=> $i2->getCount()) === 0;

		return $retval;
	}

	/**
	 * @param Item[] $items
	 *
	 * @return Item[]
	 */
	private static function pack(array $items) : array{
		/** @var Item[] $result */
		$result = [];

		foreach($items as $i => $item){
			foreach($result as $otherItem){
				if($item->canStackWith($otherItem)){
					$otherItem->setCount($otherItem->getCount() + $item->getCount());
					continue 2;
				}
			}

			//No matching item found
			$result[] = clone $item;
		}

		return $result;
	}

	/**
	 * @param Item[] $outputs
	 */
	private static function hashOutputs(array $outputs) : string{
		$outputs = self::pack($outputs);
		usort($outputs, [self::class, "sort"]);
		$result = new BinaryStream();
		foreach($outputs as $o){
			//count is not written because the outputs might be from multiple repetitions of a single recipe
			//this reduces the accuracy of the hash, but it won't matter in most cases.
			$result->putVarInt($o->getId());
			$result->putVarInt($o->getDamage());

			$tags = $o->getNamedTag()->getValue();
			ksort($tags);
			$result->put((new LittleEndianNBTStream())->write($tags));
		}

		return $result->getBuffer();
	}

	/**
	 * @return ShapelessRecipe[][]
	 * @phpstan-return array<string, list<ShapelessRecipe>>
	 */
	public function getShapelessRecipes(int $protocolVersion = ProtocolInfo::CURRENT_PROTOCOL) : array{
		foreach ($this->shapelessRecipes as $protocol => $shapelessRecipes) {
			if ($protocolVersion >= $protocol) {
				return $shapelessRecipes;
			}
		}

		return [];
	}

	/**
	 * @return ShapedRecipe[][]
	 * @phpstan-return array<string, list<ShapedRecipe>>
	 */
	public function getShapedRecipes(int $protocolVersion = ProtocolInfo::CURRENT_PROTOCOL) : array{
		foreach ($this->shapedRecipes as $protocol => $shapedRecipes) {
			if ($protocolVersion >= $protocol) {
				return $shapedRecipes;
			}
		}

		return [];
	}

	/**
	 * @return CraftingRecipe[][]
	 * @phpstan-return array<string, CraftingRecipe>
	 */
	public function getFurnaceRecipes(int $protocolVersion = ProtocolInfo::CURRENT_PROTOCOL) : array{
		foreach ($this->furnaceRecipes as $protocol => $furnaceRecipes) {
			if ($protocolVersion >= $protocol) {
				return $furnaceRecipes;
			}
		}

		return [];
	}

	/**
	 * @return CraftingRecipe[]
	 * @phpstan-return array<int, CraftingRecipe>
	 */
	public function getCraftingRecipeIndex(int $protocolVersion = ProtocolInfo::CURRENT_PROTOCOL) : array{
		if (isset($this->craftingRecipeIndexCache[$protocolVersion])) {
			return $this->craftingRecipeIndexCache[$protocolVersion];
		}
		$index = [];
		foreach ($this->getShapelessRecipes($protocolVersion) as $list) {
			foreach ($list as $recipe) {
				$index[] = $recipe;
			}
		}
		foreach ($this->getShapedRecipes($protocolVersion) as $list) {
			foreach ($list as $recipe) {
				$index[] = $recipe;
			}
		}
		foreach ($this->getSmithingTransformRecipes($protocolVersion) as $recipe) {
			$index[] = $recipe;
		}
		foreach ($this->getSmithingTrimRecipes($protocolVersion) as $recipe) {
			$index[] = $recipe;
		}
		return $this->craftingRecipeIndexCache[$protocolVersion] = $index;
	}

	public function getCraftingRecipeFromIndex(int $index, int $protocolVersion = ProtocolInfo::CURRENT_PROTOCOL) : ?CraftingRecipe{
		return $this->getCraftingRecipeIndex($protocolVersion)[$index] ?? null;
	}

	/**
	 * @return PotionTypeRecipe[]
	 * @phpstan-return list<PotionTypeRecipe>
	 */
	public function getPotionTypeRecipes(int $protocolVersion = ProtocolInfo::CURRENT_PROTOCOL) : array{
		foreach ($this->potionTypeRecipes as $protocol => $potionTypeRecipes) {
			if ($protocolVersion >= $protocol) {
				return $potionTypeRecipes;
			}
		}

		return [];
	}

	/**
	 * @return PotionContainerChangeRecipe[]
	 * @phpstan-return list<PotionContainerChangeRecipe>
	 */
	public function getPotionContainerChangeRecipes(int $protocolVersion = ProtocolInfo::CURRENT_PROTOCOL) : array{
		foreach ($this->potionContainerChangeRecipes as $protocol => $potionContainerChangeRecipes) {
			if ($protocolVersion >= $protocol) {
				return $potionContainerChangeRecipes;
			}
		}

		return [];
	}

	/**
	 * @return SmithingTransformRecipe[]
	 * @phpstan-return list<SmithingTransformRecipe>
	 */
	public function getSmithingTransformRecipes(int $protocolVersion = ProtocolInfo::CURRENT_PROTOCOL) : array{
		foreach ($this->smithingTransformRecipes as $protocol => $smithingRecipes) {
			if ($protocolVersion >= $protocol) {
				return $smithingRecipes;
			}
		}

		return [];
	}

	/**
	 * @return SmithingTrimRecipe[]
	 * @phpstan-return list<SmithingTrimRecipe>
	 */
	public function getSmithingTrimRecipes(int $protocolVersion = ProtocolInfo::CURRENT_PROTOCOL) : array{
		foreach ($this->smithingTrimRecipes as $protocol => $smithingTrimRecipes) {
			if ($protocolVersion >= $protocol) {
				return $smithingTrimRecipes;
			}
		}

		return [];
	}

	public function registerShapedRecipe(ShapedRecipe $recipe, ?int $protocolVersion = null) : void{
		$outputHash = self::hashOutputs($recipe->getResults());
		if ($protocolVersion === null) {
			foreach ($this->shapedRecipes as $protocol => $shapedRecipes) {
				$this->shapedRecipes[$protocol][$outputHash][] = $recipe;

			}
		} else {
			$this->shapedRecipes[$protocolVersion][$outputHash][] = $recipe;

		}

		krsort($this->shapedRecipes);
		$this->craftingRecipeIndexCache = [];

		CraftingDataCache::getInstance()->clearCache($this);
	}

	public function registerShapelessRecipe(ShapelessRecipe $recipe, ?int $protocolVersion = null) : void{
		$outputHash = self::hashOutputs($recipe->getResults());
		if ($protocolVersion === null) {
			foreach ($this->shapelessRecipes as $protocol => $shapelessRecipes) {
				$this->shapelessRecipes[$protocol][$outputHash][] = $recipe;

			}
		} else {
			$this->shapelessRecipes[$protocolVersion][$outputHash][] = $recipe;

		}

		krsort($this->shapelessRecipes);
		$this->craftingRecipeIndexCache = [];

		CraftingDataCache::getInstance()->clearCache($this);
	}

	public function registerFurnaceRecipe(FurnaceRecipe $recipe, FurnaceType $furnaceType = FurnaceType::FURNACE, ?int $protocolVersion = null) : void{
		if ($protocolVersion === null) {
			foreach ($this->furnaceRecipes as $protocol => $furnaceRecipes) {
				$this->furnaceRecipes[$protocol][$furnaceType->name()][] = $recipe;
			}
		} else {
			$this->furnaceRecipes[$protocolVersion][$furnaceType->name()][] = $recipe;
		}

		krsort($this->furnaceRecipes);

		CraftingDataCache::getInstance()->clearCache($this);
	}

	public function registerPotionTypeRecipe(PotionTypeRecipe $recipe, ?int $protocolVersion = null) : void{
		if ($protocolVersion === null) {
			foreach ($this->potionTypeRecipes as $protocol => $potionTypeRecipes) {
				$this->potionTypeRecipes[$protocol][] = $recipe;
			}
		} else {
			$this->potionTypeRecipes[$protocolVersion][] = $recipe;
		}

		krsort($this->potionTypeRecipes);

		CraftingDataCache::getInstance()->clearCache($this);
	}

	public function registerPotionContainerChangeRecipe(PotionContainerChangeRecipe $recipe, ?int $protocolVersion = null) : void{
		if ($protocolVersion === null) {
			foreach ($this->potionContainerChangeRecipes as $protocol => $potionContainerChangeRecipes) {
				$this->potionContainerChangeRecipes[$protocol][] = $recipe;
			}
		} else {
			$this->potionContainerChangeRecipes[$protocolVersion][] = $recipe;
		}

		krsort($this->potionContainerChangeRecipes);

		CraftingDataCache::getInstance()->clearCache($this);
	}

	public function registerSmithingTransformRecipe(SmithingTransformRecipe $recipe, ?int $protocolVersion = null) : void{
		if ($protocolVersion === null) {
			foreach ($this->smithingTransformRecipes as $protocol => $smithingTransformRecipes) {
				$this->smithingTransformRecipes[$protocol][] = $recipe;

			}
		} else {
			$this->smithingTransformRecipes[$protocolVersion][] = $recipe;

		}

		krsort($this->smithingTransformRecipes);
		$this->craftingRecipeIndexCache = [];

		CraftingDataCache::getInstance()->clearCache($this);
	}

	public function registerSmithingTrimRecipe(SmithingTrimRecipe $recipe, ?int $protocolVersion = null) : void{
		if ($protocolVersion === null) {
			foreach ($this->smithingTrimRecipes as $protocol => $smithingTrimRecipes) {
				$this->smithingTrimRecipes[$protocol][] = $recipe;

			}
		} else {
			$this->smithingTrimRecipes[$protocolVersion][] = $recipe;

		}

		krsort($this->smithingTrimRecipes);
		$this->craftingRecipeIndexCache = [];

		CraftingDataCache::getInstance()->clearCache($this);
	}

	/**
	 * @param Item[] $outputs
	 */
	public function matchRecipe(CraftingGrid $grid, array $outputs, int $protocolVersion = ProtocolInfo::CURRENT_PROTOCOL) : ?CraftingRecipe{
		//TODO: try to match special recipes before anything else (first they need to be implemented!)

		$outputHash = self::hashOutputs($outputs);

		$shapedRecipes = $this->getShapedRecipes($protocolVersion);
		if(isset($shapedRecipes[$outputHash])){
			foreach($shapedRecipes[$outputHash] as $recipe){
				if($recipe->matchesCraftingGrid($grid)){
					return $recipe;
				}
			}
		}

		$shapelessRecipes = $this->getShapelessRecipes($protocolVersion);
		if(isset($shapelessRecipes[$outputHash])){
			foreach($shapelessRecipes[$outputHash] as $recipe){
				if($recipe->matchesCraftingGrid($grid)){
					return $recipe;
				}
			}
		}

		return null;
	}

	/**
	 * @param Item[] $outputs
	 *
	 * @return CraftingRecipe[]|Generator
	 * @phpstan-return Generator<int, CraftingRecipe, void, void>
	 */
	public function matchRecipeByOutputs(array $outputs, int $protocolVersion = ProtocolInfo::CURRENT_PROTOCOL) : Generator{
		//TODO: try to match special recipes before anything else (first they need to be implemented!)

		$outputHash = self::hashOutputs($outputs);

		$shapedRecipes = $this->getShapedRecipes($protocolVersion);
		if(isset($shapedRecipes[$outputHash])){
			foreach($shapedRecipes[$outputHash] as $recipe){
				yield $recipe;
			}
		}

		$shapelessRecipes = $this->getShapelessRecipes($protocolVersion);
		if(isset($shapelessRecipes[$outputHash])){
			foreach($shapelessRecipes[$outputHash] as $recipe){
				yield $recipe;
			}
		}
	}

	public function matchFurnaceRecipe(Item $input, FurnaceType $furnaceType = FurnaceType::FURNACE, int $protocolVersion = ProtocolInfo::CURRENT_PROTOCOL) : ?FurnaceRecipe{
		$index = $input->getId() . ":" . $input->getDamage();
		$simpleRecipe = $this->furnaceRecipeCache[$protocolVersion][$furnaceType->name()][$index] ?? null;
		if($simpleRecipe !== null){
			return $simpleRecipe;
		}

		$furnaceRecipes = $this->getFurnaceRecipes($protocolVersion);
		if (isset($furnaceRecipes[$furnaceType->name()])) {
			foreach ($furnaceRecipes[$furnaceType->name()] as $recipe) {
				if ($recipe->getInput()->accepts($input)) {
					//remember that this item is accepted by this recipe, so we don't need to bruteforce it again
					$this->furnaceRecipeCache[$protocolVersion][$furnaceType->name()][$index] = $recipe;
					return $recipe;
				}
			}
		}

		return null;
	}

	public function matchBrewingRecipe(Item $input, Item $ingredient, int $protocolVersion = ProtocolInfo::CURRENT_PROTOCOL) : ?BrewingRecipe{
		$inputHash = $input->getId() . ":" . $input->getDamage();
		$ingredientHash = $ingredient->getId() . ":" . $ingredient->getDamage();
		$cached = $this->brewingRecipeCache[$protocolVersion][$inputHash][$ingredientHash] ?? null;
		if($cached !== null){
			return $cached;
		}

		$potionContainerChangeRecipes = $this->getPotionContainerChangeRecipes($protocolVersion);
		foreach($potionContainerChangeRecipes as $recipe){
			if($recipe->getIngredient()->accepts($ingredient) && $recipe->getResultFor($input) !== null){
				return $this->brewingRecipeCache[$protocolVersion][$inputHash][$ingredientHash] = $recipe;
			}
		}

		$potionTypeRecipes = $this->getPotionTypeRecipes($protocolVersion);
		foreach($potionTypeRecipes as $recipe){
			if($recipe->getIngredient()->accepts($ingredient) && $recipe->getResultFor($input) !== null){
				return $this->brewingRecipeCache[$protocolVersion][$inputHash][$ingredientHash] = $recipe;
			}
		}

		return null;
	}

	public function matchSmithingRecipe(Item $template, Item $input, Item $addition, int $protocolVersion = ProtocolInfo::CURRENT_PROTOCOL) : ?SmithingRecipe{
		$templateHash = $template->getId() . ":" . $template->getDamage();
		$inputHash = $input->getId() . ":" . $input->getDamage();
		$additionHash = $addition->getId() . ":" . $addition->getDamage();
		$cached = $this->smithingRecipeCache[$protocolVersion][$templateHash][$inputHash][$additionHash] ?? null;
		if($cached !== null){
			return $cached;
		}

		$smithingTransformRecipes = $this->getSmithingTransformRecipes($protocolVersion);
		foreach($smithingTransformRecipes as $recipe){
			if($recipe->getTemplate()->accepts($template) && $recipe->getInput()->accepts($input) && $recipe->getAddition()->accepts($addition)){
				return $this->smithingRecipeCache[$protocolVersion][$templateHash][$inputHash][$additionHash] = $recipe;
			}
		}

		$smithingTrimRecipes = $this->getSmithingTrimRecipes($protocolVersion);
		foreach($smithingTrimRecipes as $recipe){
			if($recipe->getTemplate()->accepts($template) && $recipe->getInput()->accepts($input) && $recipe->getAddition()->accepts($addition)){
				return $this->smithingRecipeCache[$protocolVersion][$templateHash][$inputHash][$additionHash] = $recipe;
			}
		}

		return null;
	}
}
