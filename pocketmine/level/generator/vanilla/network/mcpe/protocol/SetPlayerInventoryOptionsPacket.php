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

namespace pocketmine\level\generator\vanilla\network\mcpe\protocol;

use pocketmine\level\generator\vanilla\network\mcpe\NetworkSession;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\inventory\InventoryLayout;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\inventory\InventoryLeftTab;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\inventory\InventoryRightTab;

class SetPlayerInventoryOptionsPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::SET_PLAYER_INVENTORY_OPTIONS_PACKET;

	private InventoryLeftTab $leftTab;
	private InventoryRightTab $rightTab;
	private bool $filtering;
	private InventoryLayout $inventoryLayout;
	private InventoryLayout $craftingLayout;

	/**
	 * @generate-create-func
	 */
	public static function create(InventoryLeftTab $leftTab, InventoryRightTab $rightTab, bool $filtering, InventoryLayout $inventoryLayout, InventoryLayout $craftingLayout) : self
	{
		$result = new self();
		$result->leftTab = $leftTab;
		$result->rightTab = $rightTab;
		$result->filtering = $filtering;
		$result->inventoryLayout = $inventoryLayout;
		$result->craftingLayout = $craftingLayout;
		return $result;
	}

	public function getLeftTab() : InventoryLeftTab
	{
		return $this->leftTab;
	}

	public function getRightTab() : InventoryRightTab
	{
		return $this->rightTab;
	}

	public function isFiltering() : bool
	{
		return $this->filtering;
	}

	public function getInventoryLayout() : InventoryLayout
	{
		return $this->inventoryLayout;
	}

	public function getCraftingLayout() : InventoryLayout
	{
		return $this->craftingLayout;
	}

	protected function decodePayload() : void
	{
		$this->leftTab = InventoryLeftTab::fromPacket($this->getVarInt());
		$this->rightTab = InventoryRightTab::fromPacket($this->getVarInt());
		$this->filtering = $this->getBool();
		$this->inventoryLayout = InventoryLayout::fromPacket($this->getVarInt());
		$this->craftingLayout = InventoryLayout::fromPacket($this->getVarInt());
	}

	protected function encodePayload() : void
	{
		$this->putVarInt($this->leftTab->value);
		$this->putVarInt($this->rightTab->value);
		$this->putBool($this->filtering);
		$this->putVarInt($this->inventoryLayout->value);
		$this->putVarInt($this->craftingLayout->value);
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleSetPlayerInventoryOptions($this);
	}
}
