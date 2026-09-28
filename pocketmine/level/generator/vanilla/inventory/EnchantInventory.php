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

use pocketmine\level\generator\vanilla\event\player\PlayerEnchantingOptionsRequestEvent;
use pocketmine\level\generator\vanilla\item\enchantment\EnchantingHelper as Helper;
use pocketmine\level\generator\vanilla\item\enchantment\EnchantingOption;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\level\Position;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\inventory\UIInventorySlotOffset;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\inventory\WindowTypes;
use pocketmine\Player;
use function array_values;
use function count;

class EnchantInventory extends ContainerInventory implements FakeInventory
{
	public const SLOT_INPUT = 0;
	public const SLOT_LAPIS = 1;

	/** @var Position */
	protected $holder;

	/**
	 * @var EnchantingOption[] $options
	 * @phpstan-var list<EnchantingOption>
	 */
	private array $options = [];

	public function __construct(Position $pos)
	{
		parent::__construct($pos->asPosition());
	}

	public function getNetworkType() : int
	{
		return WindowTypes::ENCHANTMENT;
	}

	public function getName() : string
	{
		return "Enchantment Table";
	}

	public function getDefaultSize() : int
	{
		return 2; //1 input, 1 lapis
	}

	public function getUIOffsets(?Player $player) : array
	{
		return UIInventorySlotOffset::ENCHANTING_TABLE;
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

	public function onSlotChange(int $index, Item $before, bool $send) : void{
		if($index === self::SLOT_INPUT){
			foreach($this->viewers as $viewer){
				$this->options = [];
				$item = $this->getInput();
				$options = Helper::generateOptions($this->holder, $item, $viewer->getEnchantmentSeed());

				$event = new PlayerEnchantingOptionsRequestEvent($viewer, $this, $options);
				$event->call();
				if(!$event->isCancelled() && count($event->getOptions()) > 0){
					$this->options = array_values($event->getOptions());
					$viewer->syncEnchantingTableOptions($this->options);
				}
			}
		}

		parent::onSlotChange($index, $before, $send);
	}

	public function getInput() : Item{
		return $this->getItem(self::SLOT_INPUT);
	}

	public function getLapis() : Item{
		return $this->getItem(self::SLOT_LAPIS);
	}

	public function getOutput(int $optionId) : ?Item{
		$option = $this->getOption($optionId);
		return $option === null ? null : Helper::enchantItem($this->getInput(), $option->getEnchantments());
	}

	public function getOption(int $optionId) : ?EnchantingOption{
		return $this->options[$optionId] ?? null;
	}
}
