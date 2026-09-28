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

use pocketmine\level\generator\vanilla\network\mcpe\NetworkBinaryStream;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\GetTypeIdFromConstTrait;

/**
 * Renames an item in an anvil, or map on a cartography table.
 */
final class CraftRecipeOptionalStackRequestAction extends ItemStackRequestAction
{
	use GetTypeIdFromConstTrait;

	public const ID = ItemStackRequestActionType::CRAFTING_RECIPE_OPTIONAL;

	private int $recipeId;
	private int $filterStringIndex;

	//TODO: promote this when we can rename parameters (BC break)
	public function __construct(int $type, int $filterStringIndex)
	{
		$this->recipeId = $type;
		$this->filterStringIndex = $filterStringIndex;
	}

	public function getRecipeId() : int
	{
		return $this->recipeId;
	}

	public function getFilterStringIndex() : int
	{
		return $this->filterStringIndex;
	}

	public static function read(NetworkBinaryStream $in) : self
	{
		$recipeId = $in->readRecipeNetId();
		$filterStringIndex = $in->getLInt();
		return new self($recipeId, $filterStringIndex);
	}

	public function write(NetworkBinaryStream $out) : void
	{
		$out->writeRecipeNetId($this->recipeId);
		$out->putLInt($this->filterStringIndex);
	}
}
