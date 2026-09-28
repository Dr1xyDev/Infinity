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

namespace pocketmine\level\generator\vanilla\network\mcpe\protocol\types\inventory;

use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\network\mcpe\NetworkBinaryStream;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ProtocolInfo;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\GetTypeIdFromConstTrait;

class ReleaseItemTransactionData extends TransactionData
{
	use GetTypeIdFromConstTrait;

	public const ID = InventoryTransactionPacket::TYPE_RELEASE_ITEM;

	public const ACTION_RELEASE = 0; //bow shoot
	public const ACTION_CONSUME = 1; //eat food, drink potion

	private int $actionType;
	private int $hotbarSlot;
	private ItemStackWrapper $itemInHand;
	private Vector3 $headPosition;

	public function getActionType() : int
	{
		return $this->actionType;
	}

	public function getHotbarSlot() : int
	{
		return $this->hotbarSlot;
	}

	public function getItemInHand() : ItemStackWrapper
	{
		return $this->itemInHand;
	}

	public function getHeadPosition() : Vector3
	{
		return $this->headPosition;
	}

	protected function decodeData(NetworkBinaryStream $in, bool $legacyTransaction) : void
	{
		$this->actionType = $in->getProtocol() >= ProtocolInfo::PROTOCOL_1001 && $legacyTransaction ? $in->getVarInt() : $in->getUnsignedVarInt();
		$this->hotbarSlot = $in->getVarInt();
		$this->itemInHand = $in->getProtocol() >= ProtocolInfo::PROTOCOL_1001 && $legacyTransaction ? $in->getNetworkItemStackDescriptor() : $in->getItemStackWrapper();
		$this->headPosition = $in->getVector3();
	}

	protected function encodeData(NetworkBinaryStream $out, bool $legacyTransaction) : void
	{
		if ($out->getProtocol() >= ProtocolInfo::PROTOCOL_1001 && $legacyTransaction) {
			$out->putVarInt($this->actionType);
		} else {
			$out->putUnsignedVarInt($this->actionType);
		}
		$out->putVarInt($this->hotbarSlot);
		if ($out->getProtocol() >= ProtocolInfo::PROTOCOL_1001 && $legacyTransaction) {
			$out->putNetworkItemStackDescriptor($this->itemInHand);
		} else {
			$out->putItemStackWrapper($this->itemInHand);
		}
		$out->putVector3($this->headPosition);
	}

	/**
	 * @param NetworkInventoryAction[] $actions
	 */
	public static function new(array $actions, int $actionType, int $hotbarSlot, ItemStackWrapper $itemInHand, Vector3 $headPosition) : self
	{
		$result = new self();
		$result->actions = $actions;
		$result->actionType = $actionType;
		$result->hotbarSlot = $hotbarSlot;
		$result->itemInHand = $itemInHand;
		$result->headPosition = $headPosition;

		return $result;
	}
}
