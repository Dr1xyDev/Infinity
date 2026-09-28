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

use pocketmine\level\generator\vanilla\block\Anvil;
use pocketmine\level\generator\vanilla\block\BlockFactory;
use pocketmine\level\generator\vanilla\block\BlockIds;
use pocketmine\level\generator\vanilla\event\block\AnvilUseEvent;
use pocketmine\level\generator\vanilla\event\player\PlayerUseAnvilEvent;
use pocketmine\level\generator\vanilla\inventory\AnvilInventory;
use pocketmine\level\generator\vanilla\inventory\AnvilResult;
use pocketmine\level\generator\vanilla\inventory\utils\AnvilHelper;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\item\ItemFactory;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\LevelEventPacket;
use pocketmine\Player;
use pocketmine\level\generator\vanilla\utils\AssumptionFailedError;
use function count;
use function mt_rand;

class AnvilTransaction extends InventoryTransaction{

	private ?Item $baseItem = null;
	private ?Item $materialItem = null;

	public function __construct(
		Player $source,
		private readonly AnvilResult $expectedResult,
		private readonly ?string $customName
	){
		parent::__construct($source);
	}

	private function validateFiniteResources(int $xpSpent) : void{
		$expectedXpCost = $this->expectedResult->getXpCost();
		if($xpSpent !== $expectedXpCost){
			throw new TransactionValidationException("Expected the amount of xp spent to be $expectedXpCost, but received $xpSpent");
		}

		$xpLevel = $this->source->getXpLevel();
		if($xpLevel < $expectedXpCost){
			throw new TransactionValidationException("Player's XP level $xpLevel is less than the required XP level $expectedXpCost");
		}
	}

	private function validateInputs(Item $base, Item $material, Item $expectedOutput) : ?int{
		$calculAttempt = AnvilHelper::calculateResult($base, $material, $this->customName, !$this->source->hasFiniteResources());
		if($calculAttempt === null){
			return null;
		}
		$result = $calculAttempt->getOutput();

		AnvilHelper::sortEnchantments($expectedOutput, $result);
		if(!$result->equalsExact($expectedOutput)){
			return null;
		}

		$this->baseItem = $base;
		$this->materialItem = $material;

		return $calculAttempt->getXpCost();
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

		if (($inputCount = count($inputs)) < 1) {
			throw new TransactionValidationException("Expected at least 1 input item, but received $inputCount");
		}
		if ($inputCount > 2) {
			throw new TransactionValidationException("Expected at most 2 input items, but received $inputCount");
		}

		if(count($inputs) < 2){
			$xpCost = $this->validateInputs($inputs[0], ItemFactory::air(), $outputItem) ??
				throw new TransactionValidationException("Inputs do not match expected result");
		} else {
			$xpCost = $this->validateInputs($inputs[0], $inputs[1], $outputItem) ??
				$this->validateInputs($inputs[1], $inputs[0], $outputItem) ??
				throw new TransactionValidationException("Inputs do not match expected result");
		}

		if ($this->source->hasFiniteResources()) {
			$this->validateFiniteResources($xpCost);
		}
	}

	public function execute() : bool{
		if (!parent::execute()) {
			return false;
		}

		$source = $this->source;
		if ($source->hasFiniteResources()) {
			$source->subtractXpLevels($this->expectedResult->getXpCost());
		}

		$inventory = $source->getCurrentWindow();
		if ($inventory instanceof AnvilInventory) {
			$level = $inventory->getHolder()->getLevel();
			$anvilBlock = $level->getBlock($inventory->getHolder());
			if ($anvilBlock instanceof Anvil) {
				$event = new AnvilUseEvent($anvilBlock, mt_rand(0, 12) === 0);
				$event->call();
				if (!$event->isCancelled()) {
					if ($event->shouldTakeDamage()) {
						$direction = $anvilBlock->getDamage() & 3;
						$type = $anvilBlock->getDamage() - $direction;

						if ($type === Anvil::TYPE_NORMAL) {
							$type = Anvil::TYPE_SLIGHTLY_DAMAGED;
						} elseif ($type === Anvil::TYPE_SLIGHTLY_DAMAGED) {
							$type = Anvil::TYPE_VERY_DAMAGED;
						} else {
							$type = -1;
						}

						if ($type !== -1) {
							$level->setBlock($inventory->getHolder(), BlockFactory::get(BlockIds::ANVIL, $direction | $type));
						} else {
							$level->setBlock($inventory->getHolder(), BlockFactory::get(BlockIds::AIR));

							$level->broadcastLevelEvent($inventory->getHolder(), LevelEventPacket::EVENT_SOUND_ANVIL_BREAK);
						}
					}

					$level->broadcastLevelEvent($inventory->getHolder(), LevelEventPacket::EVENT_SOUND_ANVIL_USE);
				}
			}
		}

		return true;
	}

	protected function callExecuteEvent() : bool{
		if($this->baseItem === null){
			throw new AssumptionFailedError("Expected that baseItem is not null before executing the event");
		}

		$ev = new PlayerUseAnvilEvent($this->source, $this->baseItem, $this->materialItem, $this->expectedResult->getOutput(), $this->customName, $this->expectedResult->getXpCost());
		$ev->call();
		return !$ev->isCancelled();
	}
}
