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
use pocketmine\level\generator\vanilla\inventory\transaction\GrindstoneTransaction;
use pocketmine\level\generator\vanilla\inventory\transaction\TransactionValidationException;
use pocketmine\level\generator\vanilla\inventory\utils\GrindstoneHelper;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\item\ItemFactory;
use pocketmine\level\generator\vanilla\level\Position;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\inventory\UIInventorySlotOffset;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\inventory\WindowTypes;
use pocketmine\Player;

class GrindstoneInventory extends ContainerInventory implements FakeInventory, FakeResultInventory
{
	public const int SLOT_INPUT = 0;
	public const int SLOT_ADDITIONAL = 1;

	/** @var Position */
	protected $holder;

	public function __construct(Position $pos)
	{
		parent::__construct($pos->asPosition());
	}

	public function getNetworkType() : int
	{
		return WindowTypes::GRINDSTONE;
	}

	public function getName() : string
	{
		return "Grindstone";
	}

	public function getUIOffsets(?Player $player) : array
	{
		return UIInventorySlotOffset::GRINDSTONE;
	}

	public function getDefaultSize() : int
	{
		return 2; //input and additional, the result is computed
	}

	/**
	 * @return Position
	 */
	public function getHolder()
	{
		return $this->holder;
	}

	public function getInput() : Item
	{
		return $this->getItem(self::SLOT_INPUT);
	}

	public function getAdditional() : Item
	{
		return $this->getItem(self::SLOT_ADDITIONAL);
	}

	/**
	 * Result taken via the legacy NetworkInventoryAction transaction protocol. Clients using item stack requests go
	 * through ItemStackRequestExecutor instead. Either way the result is validated by GrindstoneTransaction.
	 */
	public function onResult(Player $player, Item $result) : bool
	{
		$input = $this->getInput();
		$additional = $this->getAdditional();
		$cursor = $player->getCursorInventory();
		$expected = GrindstoneHelper::calculateResult($input, $additional);
		if ($expected === null || !$expected->getOutput()->equalsExact($result) || !$cursor->getItem(0)->isNull()) {
			$this->sendContents($player);
			return true;
		}

		$transaction = new GrindstoneTransaction($player);
		if (!$input->isNull()) {
			$transaction->addAction(new SlotChangeAction($this, self::SLOT_INPUT, $input, ItemFactory::air()));
		}
		if (!$additional->isNull()) {
			$transaction->addAction(new SlotChangeAction($this, self::SLOT_ADDITIONAL, $additional, ItemFactory::air()));
		}
		$transaction->addAction(new SlotChangeAction($cursor, 0, ItemFactory::air(), $result));

		try {
			if (!$transaction->execute()) {
				$player->getInventory()->sendContents($player);
				$this->sendContents($player);
			}
		} catch (TransactionValidationException) {
			$player->getInventory()->sendContents($player);
			$this->sendContents($player);
		}

		return true;
	}

	public function onClose(Player $who) : void
	{
		parent::onClose($who);

		foreach ($this->getContents() as $item) {
			$who->dropItem($item);
		}
		$this->clearAll();
	}
}
