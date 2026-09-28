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

namespace pocketmine\level\generator\vanilla\network\mcpe\protocol\types\recipe;

class PotionContainerChangeRecipe{
	public function __construct(
		private int $inputItemId,
		private int $ingredientItemId,
		private int $outputItemId
	){}

	public function getInputItemId() : int{
		return $this->inputItemId;
	}

	public function getIngredientItemId() : int{
		return $this->ingredientItemId;
	}

	public function getOutputItemId() : int{
		return $this->outputItemId;
	}
}
