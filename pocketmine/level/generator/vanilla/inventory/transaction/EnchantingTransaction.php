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

use pocketmine\level\generator\vanilla\event\player\PlayerItemEnchantEvent;
use pocketmine\level\generator\vanilla\item\enchantment\EnchantingHelper;
use pocketmine\level\generator\vanilla\item\enchantment\EnchantingOption;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\item\ItemIds;
use pocketmine\Player;
use pocketmine\level\generator\vanilla\utils\AssumptionFailedError;
use function count;
use function min;

class EnchantingTransaction extends InventoryTransaction{

	private ?Item $inputItem = null;
	private ?Item $outputItem = null;

	public function __construct(
		Player $source,
		private readonly EnchantingOption $option,
		private readonly int $cost
	){
		parent::__construct($source);
	}

	private function validateOutput() : void{
		if($this->inputItem === null || $this->outputItem === null){
			throw new AssumptionFailedError("Expected that inputItem and outputItem are not null before validating output");
		}

		$enchantedInput = EnchantingHelper::enchantItem($this->inputItem, $this->option->getEnchantments());
		if(!$this->outputItem->equalsExact($enchantedInput)){
			throw new TransactionValidationException("Invalid output item");
		}
	}

	private function validateFiniteResources(int $lapisSpent) : void{
		if($lapisSpent !== $this->cost){
			throw new TransactionValidationException("Expected the amount of lapis lazuli spent to be $this->cost, but received $lapisSpent");
		}

		$xpLevel = $this->source->getXpLevel();
		$requiredXpLevel = $this->option->getRequiredXpLevel();

		if($xpLevel < $requiredXpLevel){
			throw new TransactionValidationException("Player's XP level $xpLevel is less than the required XP level $requiredXpLevel");
		}
		//XP level cost is intentionally not checked here, as the required level may be lower than the cost, allowing
		//the option to be used with less XP than the cost - in this case, as much XP as possible will be deducted.
	}

	public function validate() : void{
		$this->squashDuplicateSlotChanges();

		if(count($this->actions) < 1){
			throw new TransactionValidationException("Transaction must have at least one action to be executable");
		}

		/** @var Item[] $inputs */
		$inputs = [];
		/** @var Item[] $outputs */
		$outputs = [];
		$this->matchItems($outputs, $inputs);

		$lapisSpent = 0;
		foreach($inputs as $input){
			if($input->getId() === ItemIds::DYE && $input->getDamage() === 4){
				$lapisSpent = $input->getCount();
			}else{
				if($this->inputItem !== null){
					throw new TransactionValidationException("Received more than 1 items to enchant");
				}
				$this->inputItem = $input;
			}
		}

		if($this->inputItem === null){
			throw new TransactionValidationException("No item to enchant received");
		}

		if(($outputCount = count($outputs)) !== 1){
			throw new TransactionValidationException("Expected 1 output item, but received $outputCount");
		}
		$this->outputItem = $outputs[0];

		$this->validateOutput();

		if($this->source->hasFiniteResources()){
			$this->validateFiniteResources($lapisSpent);
		}
	}

	public function execute() : bool{
		if (!parent::execute()) {
			return false;
		}

		if($this->source->hasFiniteResources()){
			//If the required XP level is less than the XP cost, the option can be selected with less XP than the cost.
			//In this case, as much XP as possible will be taken.
			$this->source->subtractXpLevels(min($this->cost, $this->source->getXpLevel()));
		}
		$this->source->regenerateEnchantmentSeed();
		return true;
	}

	protected function callExecuteEvent() : bool{
		if($this->inputItem === null || $this->outputItem === null){
			throw new AssumptionFailedError("Expected that inputItem and outputItem are not null before executing the event");
		}

		$event = new PlayerItemEnchantEvent($this->source, $this, $this->option, $this->inputItem, $this->outputItem, $this->cost);
		$event->call();
		return !$event->isCancelled();
	}
}
