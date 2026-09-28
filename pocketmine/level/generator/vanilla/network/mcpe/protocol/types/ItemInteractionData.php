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

namespace pocketmine\level\generator\vanilla\network\mcpe\protocol\types;

use pocketmine\level\generator\vanilla\network\mcpe\NetworkBinaryStream;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ProtocolInfo;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\inventory\InventoryTransactionChangedSlotsHack;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\inventory\UseItemTransactionData;

use function count;

final class ItemInteractionData
{
	/**
	 * @param InventoryTransactionChangedSlotsHack[] $requestChangedSlots
	 */
	public function __construct(
		private int $requestId,
		private array $requestChangedSlots,
		private UseItemTransactionData $transactionData
	) {
	}

	public function getRequestId() : int
	{
		return $this->requestId;
	}

	/**
	 * @return InventoryTransactionChangedSlotsHack[]
	 */
	public function getRequestChangedSlots() : array
	{
		return $this->requestChangedSlots;
	}

	public function getTransactionData() : UseItemTransactionData
	{
		return $this->transactionData;
	}

	/**
	 * 1.26.40 and 1.26.45 wrapped the transaction in two always-true bools, 1.26.50 removed them again (real clients
	 * and CloudburstMC's v2193 codec agree; BedrockProtocol 62.0.0 still expects them)
	 */
	private static function hasDummyOptionals(int $protocol) : bool
	{
		return $protocol >= ProtocolInfo::PROTOCOL_2168 && $protocol < ProtocolInfo::PROTOCOL_2193;
	}

	public static function read(NetworkBinaryStream $in) : self
	{
		$requestId = $in->getVarInt();
		if ($in->getProtocol() >= ProtocolInfo::PROTOCOL_2168) {
			$requestChangedSlots = $in->getOptional(fn() => $in->getList(fn() => InventoryTransactionChangedSlotsHack::read($in), 256)) ?? [];
			if (self::hasDummyOptionals($in->getProtocol())) {
				$in->getDummyOptional();
				$in->getDummyOptional();
			}
			$transactionData = new UseItemTransactionData();
			$transactionData->decode($in, false);
			return new ItemInteractionData($requestId, $requestChangedSlots, $transactionData);
		}
		$requestChangedSlots = [];
		if ($requestId !== 0) {
			$len = $in->getUnsignedVarInt();
			for ($i = 0; $i < $len; ++$i) {
				$requestChangedSlots[] = InventoryTransactionChangedSlotsHack::read($in);
			}
		}
		$transactionData = new UseItemTransactionData();
		$transactionData->decode($in, false);
		return new ItemInteractionData($requestId, $requestChangedSlots, $transactionData);
	}

	public function write(NetworkBinaryStream $out) : void
	{
		$out->putVarInt($this->requestId);
		if ($out->getProtocol() >= ProtocolInfo::PROTOCOL_2168) {
			$out->putOptional($this->requestId !== 0 ? $this->requestChangedSlots : null, fn(array $slots) => $out->putList($slots, fn(InventoryTransactionChangedSlotsHack $slot) => $slot->write($out)));
			if (self::hasDummyOptionals($out->getProtocol())) {
				$out->putDummyOptional();
				$out->putDummyOptional();
			}
			$this->transactionData->encode($out, false);
			return;
		}
		if ($this->requestId !== 0) {
			$out->putUnsignedVarInt(count($this->requestChangedSlots));
			foreach ($this->requestChangedSlots as $changedSlot) {
				$changedSlot->write($out);
			}
		}
		$this->transactionData->encode($out, false);
	}
}
