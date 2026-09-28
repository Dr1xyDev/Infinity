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

use pocketmine\level\generator\vanilla\inventory\transaction\action\SlotChangeAction;
use pocketmine\level\generator\vanilla\inventory\transaction\AnvilTransaction;
use pocketmine\level\generator\vanilla\inventory\transaction\TransactionValidationException;
use pocketmine\level\generator\vanilla\inventory\utils\AnvilHelper;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\item\ItemFactory;
use pocketmine\level\generator\vanilla\level\Position;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ProtocolInfo;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\inventory\UIInventorySlotOffset;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\inventory\WindowTypes;
use pocketmine\Player;

use function count;

class AnvilInventory extends ContainerInventory implements FakeInventory, FakeResultInventory
{
	public const int SLOT_INPUT = 0;
	public const int SLOT_MATERIAL = 1;
	public const int SLOT_OUTPUT = 2;

	/** @var Position */
	protected $holder;

	public function __construct(Position $pos)
	{
		parent::__construct($pos->asPosition());
	}

	public function getNetworkType() : int
	{
		return WindowTypes::ANVIL;
	}

	public function getName() : string
	{
		return "Anvil";
	}

	public function getUIOffsets(?Player $player) : array
	{
		return UIInventorySlotOffset::ANVIL;
	}

	public function getDefaultSize() : int
	{
		return 3; //1 input, 1 material, 1 output
	}

	/**
	 * Legacy anvil handling for the old NetworkInventoryAction transaction protocol (InventoryTransactionPacket),
	 * called via FakeResultInventory::onResult() from TypeConverter / Player. This is NOT a duplicate of the modern
	 * path in ItemStackRequestExecutor: clients using ItemStackRequestPacket (CraftRecipeOptional) build their
	 * AnvilTransaction there instead and never reach this method. Both paths are required for multi-protocol support
	 * — do not remove this one, or anvils break for clients still on the legacy transaction protocol.
	 */
	public function onResult(Player $player, Item $result) : bool{
		if ($player->getProtocolVersion() >= ProtocolInfo::PROTOCOL_407) {
			$input = $this->getItem(self::SLOT_INPUT);
			$material = $this->getItem(self::SLOT_MATERIAL);

			$customName = null;
			if ($result->hasCustomName()) {
				if ($input->getCustomName() !== $result->getCustomName()) {
					$customName = $input->getCustomName();
				}
			}

			$anvilResult = AnvilHelper::calculateResult($input, $material, $customName, $player->isCreative());
			if ($anvilResult === null) {
				return true;
			}

			$actions = [
				new SlotChangeAction($this, self::SLOT_INPUT, $input, ItemFactory::air()),
				new SlotChangeAction($this, self::SLOT_OUTPUT, $this->getItem(self::SLOT_OUTPUT), $result)
			];

			if (!$material->isNull()) {
				$actions[] = new SlotChangeAction($this, self::SLOT_MATERIAL, $material, ItemFactory::air());
			}
		} else {
			if ($result->isNull()) {
				return true;
			}

			$craftingGild = $player->getCraftingGrid();
			if ($craftingGild->contains($result)) {
				return true;
			}

			$contents = $craftingGild->getContents();
			if (count($contents) !== 1 && count($contents) !== 2) {
				return true;
			}

			$input = null;
			$inputSlot = null;
			$material = ItemFactory::air();
			$materialSlot = null;
			foreach ($contents as $slot => $content) {
				if ($content->isNull()) {
					continue;
				}

				if ($input === null) {
					$input = $content;
					$inputSlot = $slot;
				} elseif ($material->isNull()) {
					$material = $content;
					$materialSlot = $slot;
				} else {
					break;
				}
			}

			if ($input === null || $inputSlot === null) {
				return true;
			}

			$customName = null;
			if ($result->hasCustomName()) {
				if ($input->getCustomName() !== $result->getCustomName()) {
					$customName = $result->getCustomName();
				}
			}

			$anvilResult = AnvilHelper::calculateResult($input, $material, $customName, $player->isCreative());
			if ($anvilResult === null) {
				$customName = null;
				if ($result->hasCustomName()) {
					if ($material->getCustomName() !== $result->getCustomName()) {
						$customName = $result->getCustomName();
					}
				}

				$anvilResult = AnvilHelper::calculateResult($material, $input, $customName, $player->isCreative());
				if ($anvilResult === null) {
					return true;
				}
			}

			$actions = [
				new SlotChangeAction($craftingGild, $inputSlot, $input, ItemFactory::air()),
				new SlotChangeAction($craftingGild, $craftingGild->firstEmpty(), ItemFactory::air(), $result)
			];

			if ($materialSlot !== null && !$material->isNull()) {
				$actions[] = new SlotChangeAction($craftingGild, $materialSlot, $material, ItemFactory::air());
			}
		}

		try {
			$transaction = new AnvilTransaction($player, $anvilResult, $customName);
			foreach ($actions as $action) {
				$transaction->addAction($action);
			}

			if (!$transaction->execute()) {
				$player->getInventory()->sendContents($player);
				$this->sendContents($player);
			}
		} catch (TransactionValidationException $e) {
			if ($player->getProtocolVersion() >= ProtocolInfo::PROTOCOL_407) {
				$player->getInventory()->sendContents($player);
				$this->sendContents($player);
			}
		}

		return true;
	}

	/**
	 * This override is here for documentation and code completion purposes only.
	 * @return Position
	 */
	public function getHolder()
	{
		return $this->holder;
	}

	public function onClose(Player $who) : void
	{
		parent::onClose($who);

		foreach ($this->getContents() as $item) {
			$who->dropItem($item);
		}
		$this->clearAll();
	}

	public function getInput() : Item {
		return $this->getItem(self::SLOT_INPUT);
	}

	public function getMaterial() : Item {
		return $this->getItem(self::SLOT_MATERIAL);
	}
}
