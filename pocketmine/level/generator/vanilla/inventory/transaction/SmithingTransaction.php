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

namespace pocketmine\level\generator\vanilla\inventory\transaction;

use pocketmine\level\generator\vanilla\event\player\PlayerUseSmithingTableEvent;
use pocketmine\level\generator\vanilla\inventory\CraftingRecipe;
use pocketmine\level\generator\vanilla\inventory\SmithingTransformRecipe;
use pocketmine\level\generator\vanilla\inventory\SmithingTrimRecipe;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\Player;
use function count;
use function min;

class SmithingTransaction extends InventoryTransaction{

	private ?Item $inputItem = null;
	private ?Item $outputItem = null;

	public function __construct(
		Player $source,
		private readonly CraftingRecipe $recipe,
		private readonly int $repetitions,
		private readonly Item $expectedResult
	){
		parent::__construct($source);
	}

	public function validate() : void{
		$this->squashDuplicateSlotChanges();

		if (count($this->actions) < 1) {
			throw new TransactionValidationException("Transaction must have at least one action to be executable");
		}

		/** @var Item[] $inputs */
		$inputs = [];
		/** @var Item[] $outputs */
		$outputs = [];
		$this->matchItems($outputs, $inputs);

		if (($outputCount = count($outputs)) !== 1) {
			throw new TransactionValidationException("Expected 1 output item, but received $outputCount");
		}
		$outputItem = $outputs[0];

		if($this->recipe instanceof SmithingTransformRecipe || $this->recipe instanceof SmithingTrimRecipe){
			$ingredients = [
				$this->recipe->getTemplate(),
				$this->recipe->getInput(),
				$this->recipe->getAddition()
			];
		} else {
			throw new TransactionValidationException("Recipe must be a smithing recipe");
		}

		//Verify that the produced output actually matches the recipe's result for the requested repetitions.
		//Without this check, a crafted client could consume the correct ingredients but receive an arbitrary item.
		$expectedOutput = clone $this->expectedResult;
		$expectedOutput->setCount($expectedOutput->getCount() * $this->repetitions);
		if(!$outputItem->equalsExact($expectedOutput)){
			throw new TransactionValidationException("Output item does not match the expected smithing result");
		}

		$providedItems = [];
		foreach($inputs as $input){
			$providedItems[] = clone $input;
		}

		foreach($ingredients as $ingredient){
			$needed = $this->repetitions;
			foreach($providedItems as $k => $provided){
				if($ingredient->accepts($provided)){
					$take = min($needed, $provided->getCount());
					$needed -= $take;
					$provided->setCount($provided->getCount() - $take);
					if($provided->getCount() === 0){
						unset($providedItems[$k]);
					}
					if($needed === 0){
						break;
					}
				}
			}
			if($needed > 0){
				throw new TransactionValidationException("Not enough items to satisfy smithing ingredient");
			}
		}

		if(count($providedItems) > 0){
			throw new TransactionValidationException("Not all provided items were used");
		}

		//Capture the item being upgraded (the one accepted by the recipe's input ingredient) for the event.
		foreach($inputs as $input){
			if($this->recipe instanceof SmithingTransformRecipe || $this->recipe instanceof SmithingTrimRecipe){
				if($this->recipe->getInput()->accepts($input)){
					$this->inputItem = $input;
					break;
				}
			}
		}
		$this->outputItem = $outputItem;
	}

	public function execute() : bool{
		if (!parent::execute()) {
			return false;
		}

		// Sound effect is handled by client for now, or we can broadcast here
		return true;
	}

	protected function callExecuteEvent() : bool{
		if($this->inputItem === null || $this->outputItem === null){
			//validate() must have run and succeeded before this point
			return true;
		}

		$event = new PlayerUseSmithingTableEvent($this->source, $this->inputItem, $this->outputItem);
		$event->call();
		return !$event->isCancelled();
	}
}
