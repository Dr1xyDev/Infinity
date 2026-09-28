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

namespace pocketmine\level\generator\vanilla\network\mcpe\protocol\types;

class PotionTypeRecipe
{
	/** @var int */
	private $inputItemId;
	/** @var int */
	private $inputItemMeta;
	/** @var int */
	private $ingredientItemId;
	/** @var int */
	private $ingredientItemMeta;
	/** @var int */
	private $outputItemId;
	/** @var int */
	private $outputItemMeta;

	public function __construct(int $inputItemId, int $inputItemMeta, int $ingredientItemId, int $ingredientItemMeta, int $outputItemId, int $outputItemMeta)
	{
		$this->inputItemId = $inputItemId;
		$this->inputItemMeta = $inputItemMeta;
		$this->ingredientItemId = $ingredientItemId;
		$this->ingredientItemMeta = $ingredientItemMeta;
		$this->outputItemId = $outputItemId;
		$this->outputItemMeta = $outputItemMeta;
	}

	public function getInputItemId() : int
	{
		return $this->inputItemId;
	}

	public function getInputItemMeta() : int
	{
		return $this->inputItemMeta;
	}

	public function getIngredientItemId() : int
	{
		return $this->ingredientItemId;
	}

	public function getIngredientItemMeta() : int
	{
		return $this->ingredientItemMeta;
	}

	public function getOutputItemId() : int
	{
		return $this->outputItemId;
	}

	public function getOutputItemMeta() : int
	{
		return $this->outputItemMeta;
	}
}
