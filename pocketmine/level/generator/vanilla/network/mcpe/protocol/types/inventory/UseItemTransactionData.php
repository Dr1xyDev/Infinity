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
use pocketmine\level\generator\vanilla\network\mcpe\protocol\PacketDecodeException;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ProtocolInfo;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\GetTypeIdFromConstTrait;

class UseItemTransactionData extends TransactionData
{
	use GetTypeIdFromConstTrait;

	public const ID = InventoryTransactionPacket::TYPE_USE_ITEM;

	public const ACTION_CLICK_BLOCK = 0;
	public const ACTION_CLICK_AIR = 1;
	public const ACTION_BREAK_BLOCK = 2;
	public const ACTION_USE_AS_ATTACK = 3;

	private int $actionType;
	private TriggerType $triggerType;
	private Vector3 $blockPos;
	private int $face;
	private int $hotbarSlot;
	private ItemStackWrapper $itemInHand;
	private Vector3 $playerPos;
	private Vector3 $clickPos;
	private int $blockRuntimeId;
	private PredictedResult $clientInteractPrediction = PredictedResult::SUCCESS;
	private int $clientCooldownState;
	/** @since 1.26.50: 0 = main hand, 1 = off hand */
	private int $hand = self::HAND_MAIN;

	public const HAND_MAIN = 0;
	public const HAND_OFF = 1;

	/**
	 * Since 1.26.30, the transaction data uses signed/byte fields and the network item descriptor, both in
	 * InventoryTransactionPacket and in PlayerAuthInputPacket's item interaction data.
	 */
	private static function usesNewDataFormat(int $protocol) : bool
	{
		return $protocol >= ProtocolInfo::PROTOCOL_1001;
	}

	public function getHand() : int
	{
		return $this->hand;
	}

	public function getActionType() : int
	{
		return $this->actionType;
	}

	public function getTriggerType() : TriggerType
	{
		return $this->triggerType;
	}

	public function getBlockPosition() : Vector3
	{
		return $this->blockPos;
	}

	public function getFace() : int
	{
		return $this->face;
	}

	public function getHotbarSlot() : int
	{
		return $this->hotbarSlot;
	}

	public function getItemInHand() : ItemStackWrapper
	{
		return $this->itemInHand;
	}

	public function getPlayerPosition() : Vector3
	{
		return $this->playerPos;
	}

	public function getClickPosition() : Vector3
	{
		return $this->clickPos;
	}

	public function getBlockRuntimeId() : int
	{
		return $this->blockRuntimeId;
	}

	public function getClientInteractPrediction() : PredictedResult
	{
		return $this->clientInteractPrediction;
	}

	public function getClientCooldownState() : int{
		return $this->clientCooldownState;
	}

	protected function decodeData(NetworkBinaryStream $in, bool $legacyTransaction) : void
	{
		$this->actionType = self::usesNewDataFormat($in->getProtocol()) ? $in->getVarInt() : $in->getUnsignedVarInt();
		if ($in->getProtocol() >= ProtocolInfo::PROTOCOL_712) {
			$this->triggerType = TriggerType::fromPacket(self::usesNewDataFormat($in->getProtocol()) ? $in->getByte() : $in->getUnsignedVarInt());
		}
		$x = $y = $z = 0;
		$in->getBlockPosition($x, $y, $z);
		$this->blockPos = new Vector3($x, $y, $z);
		$this->face = self::usesNewDataFormat($in->getProtocol()) ? $in->getByte() : $in->getVarInt();
		$this->hotbarSlot = $in->getVarInt();
		if ($in->getProtocol() >= ProtocolInfo::PROTOCOL_2193) {
			$this->hand = $in->getByte();
			if ($this->hand !== self::HAND_MAIN && $this->hand !== self::HAND_OFF) {
				throw new PacketDecodeException("Invalid hand slot $this->hand");
			}
		}
		$this->itemInHand = self::usesNewDataFormat($in->getProtocol()) ? $in->getNetworkItemStackDescriptor() : $in->getItemStackWrapper();
		$this->playerPos = $in->getVector3();
		$this->clickPos = $in->getVector3();
		if ($in->getProtocol() >= ProtocolInfo::PROTOCOL_407) {
			$this->blockRuntimeId = $in->getUnsignedVarInt();
			if ($in->getProtocol() >= ProtocolInfo::PROTOCOL_712) {
				$this->clientInteractPrediction = PredictedResult::fromPacket(self::usesNewDataFormat($in->getProtocol()) ? $in->getByte() : $in->getUnsignedVarInt());
				if ($in->getProtocol() >= ProtocolInfo::PROTOCOL_944) {
					$this->clientCooldownState = $in->getByte();
				}
			}
		}
	}

	protected function encodeData(NetworkBinaryStream $out, bool $legacyTransaction) : void
	{
		if (self::usesNewDataFormat($out->getProtocol())) {
			$out->putVarInt($this->actionType);
		} else {
			$out->putUnsignedVarInt($this->actionType);
		}
		if ($out->getProtocol() >= ProtocolInfo::PROTOCOL_712) {
			if (self::usesNewDataFormat($out->getProtocol())) {
				$out->putByte($this->triggerType->value);
			} else {
				$out->putUnsignedVarInt($this->triggerType->value);
			}
		}
		$out->putBlockPosition($this->blockPos->x, $this->blockPos->y, $this->blockPos->z);
		if (self::usesNewDataFormat($out->getProtocol())) {
			$out->putByte($this->face);
		} else {
			$out->putVarInt($this->face);
		}
		$out->putVarInt($this->hotbarSlot);
		if ($out->getProtocol() >= ProtocolInfo::PROTOCOL_2193) {
			$out->putByte($this->hand);
		}
		if (self::usesNewDataFormat($out->getProtocol())) {
			$out->putNetworkItemStackDescriptor($this->itemInHand);
		} else {
			$out->putItemStackWrapper($this->itemInHand);
		}
		$out->putVector3($this->playerPos);
		$out->putVector3($this->clickPos);
		if ($out->getProtocol() >= ProtocolInfo::PROTOCOL_407) {
			$out->putUnsignedVarInt($this->blockRuntimeId);
			if ($out->getProtocol() >= ProtocolInfo::PROTOCOL_712) {
				if (self::usesNewDataFormat($out->getProtocol())) {
					$out->putByte($this->clientInteractPrediction->value);
				} else {
					$out->putUnsignedVarInt($this->clientInteractPrediction->value);
				}
				if ($out->getProtocol() >= ProtocolInfo::PROTOCOL_944) {
					$out->putByte($this->clientCooldownState);
				}
			}
		}
	}

	/**
	 * @param NetworkInventoryAction[] $actions
	 */
	public static function new(array $actions, int $actionType, TriggerType $triggerType, Vector3 $blockPos, int $face, int $hotbarSlot, ItemStackWrapper $itemInHand, Vector3 $playerPos, Vector3 $clickPos, int $blockRuntimeId, PredictedResult $clientPrediction, int $clientCooldownState) : self
	{
		$result = new self();
		$result->actions = $actions;
		$result->actionType = $actionType;
		$result->triggerType = $triggerType;
		$result->blockPos = $blockPos;
		$result->face = $face;
		$result->hotbarSlot = $hotbarSlot;
		$result->itemInHand = $itemInHand;
		$result->playerPos = $playerPos;
		$result->clickPos = $clickPos;
		$result->blockRuntimeId = $blockRuntimeId;
		$result->clientInteractPrediction = $clientPrediction;
		$result->clientCooldownState = $clientCooldownState;
		return $result;
	}
}
