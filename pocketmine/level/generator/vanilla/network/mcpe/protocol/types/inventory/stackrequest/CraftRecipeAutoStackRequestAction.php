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

namespace pocketmine\level\generator\vanilla\network\mcpe\protocol\types\inventory\stackrequest;

use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\network\mcpe\NetworkBinaryStream;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ProtocolInfo;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\GetTypeIdFromConstTrait;

use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\recipe\RecipeIngredient;
use function count;

/**
 * Tells that the current transaction crafted the specified recipe, using the recipe book. This is effectively the same
 * as the regular crafting result action.
 */
final class CraftRecipeAutoStackRequestAction extends ItemStackRequestAction
{
	use GetTypeIdFromConstTrait;

	public const ID = ItemStackRequestActionType::CRAFTING_RECIPE_AUTO;

	/**
	 * @param RecipeIngredient[] $ingredients
	 * @phpstan-param list<RecipeIngredient> $ingredients
	 */
	final public function __construct(
		private int $recipeId,
		private int $repetitions,
		private int $repetitions2,
		private array $ingredients
	) {
	}

	public function getRecipeId() : int
	{
		return $this->recipeId;
	}

	public function getRepetitions() : int
	{
		return $this->repetitions;
	}

	public function getRepetitions2() : int
	{
		return $this->repetitions2;
	}

	/**
	 * @return Item[]
	 * @phpstan-return list<Item>
	 */
	public function getIngredients() : array
	{
		return $this->ingredients;
	}

	public static function read(NetworkBinaryStream $in) : self
	{
		$recipeId = $in->readRecipeNetId();
		$repetitions = $in->getByte();
		if ($in->getProtocol() >= ProtocolInfo::PROTOCOL_2168) {
			//since 1.26.40 the duplicated repetitions field is gone and ingredients use the item stack request format
			$ingredients = $in->getList(fn() => $in->getStackRequestIngredient(), 256);
			return new self($recipeId, $repetitions, $repetitions, $ingredients);
		}
		if ($in->getProtocol() >= ProtocolInfo::PROTOCOL_712) {
			$repetitions2 = $in->getByte(); //repetitions property is sent twice, mojang...
		}
		$ingredients = [];
		for ($i = 0, $count = $in->getByte(); $i < $count; ++$i) {
			$ingredients[] = $in->getRecipeIngredient();
		}
		return new self($recipeId, $repetitions, $repetitions2 ?? 0, $ingredients);
	}

	public function write(NetworkBinaryStream $out) : void
	{
		$out->writeRecipeNetId($this->recipeId);
		$out->putByte($this->repetitions);
		if ($out->getProtocol() >= ProtocolInfo::PROTOCOL_2168) {
			$out->putList($this->ingredients, fn(RecipeIngredient $ingredient) => $out->putStackRequestIngredient($ingredient));
			return;
		}
		if ($out->getProtocol() >= ProtocolInfo::PROTOCOL_712) {
			$out->putByte($this->repetitions2);
		}
		$out->putByte(count($this->ingredients));
		foreach ($this->ingredients as $ingredient) {
			$out->putRecipeIngredient($ingredient);
		}
	}
}
