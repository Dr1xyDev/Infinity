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

use pocketmine\level\generator\vanilla\event\player\PlayerUseGrindstoneEvent;
use pocketmine\level\generator\vanilla\inventory\GrindstoneInventory;
use pocketmine\level\generator\vanilla\inventory\utils\GrindstoneHelper;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\item\ItemFactory;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\Player;
use function count;

/**
 * The output is re-computed from the consumed items during validation, so a client can't take anything but the
 * vanilla result out of a grindstone.
 */
class GrindstoneTransaction extends InventoryTransaction{

	/** @var Item[] */
	private array $inputItems = [];
	private ?Item $outputItem = null;
	private int $experience = 0;

	public function __construct(Player $source){
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
		$inputCount = count($inputs);
		if ($inputCount < 1 || $inputCount > 2) {
			throw new TransactionValidationException("Expected 1 or 2 input items, but received $inputCount");
		}

		$result = GrindstoneHelper::calculateResult($inputs[0], $inputs[1] ?? ItemFactory::air());
		if ($result === null || !$result->getOutput()->equalsExact($outputs[0])) {
			throw new TransactionValidationException("Output item does not match the grindstone result for the given inputs");
		}

		$this->inputItems = $inputs;
		$this->outputItem = $outputs[0];
		$this->experience = GrindstoneHelper::rollExperience($result->getEnchantmentValue());
	}

	protected function callExecuteEvent() : bool{
		if ($this->outputItem === null) {
			return true;
		}

		$event = new PlayerUseGrindstoneEvent($this->source, $this->inputItems, $this->outputItem, $this->experience);
		$event->call();
		$this->experience = $event->getExperience();
		return !$event->isCancelled();
	}

	public function execute() : bool{
		if (!parent::execute()) {
			return false;
		}

		$window = $this->source->getCurrentWindow();
		$position = $window instanceof GrindstoneInventory ? $window->getHolder() : $this->source;
		$level = $this->source->getLevel();
		if ($this->experience > 0) {
			$level->dropExperience($position->add(0.5, 0.5, 0.5), $this->experience);
		}
		$level->broadcastLevelSoundEvent($position, LevelSoundEventPacket::SOUND_BLOCK_GRINDSTONE_USE);

		return true;
	}
}
