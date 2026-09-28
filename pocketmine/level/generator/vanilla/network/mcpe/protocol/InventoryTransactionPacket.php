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

#include <rules/DataPacket.h>

use pocketmine\level\generator\vanilla\network\mcpe\NetworkSession as PacketHandlerInterface;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\inventory\InventoryTransactionChangedSlotsHack;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\inventory\MismatchTransactionData;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\inventory\NormalTransactionData;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\inventory\ReleaseItemTransactionData;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\inventory\TransactionData;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\inventory\UseItemOnEntityTransactionData;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\inventory\UseItemTransactionData;

use function count;

class InventoryTransactionPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::INVENTORY_TRANSACTION_PACKET;

	public const TYPE_NORMAL = 0;
	public const TYPE_MISMATCH = 1;
	public const TYPE_USE_ITEM = 2;
	public const TYPE_USE_ITEM_ON_ENTITY = 3;
	public const TYPE_RELEASE_ITEM = 4;

	public int $requestId = 0;
	/** @var null|InventoryTransactionChangedSlotsHack[] */
	public ?array $requestChangedSlots = null;
	public ?TransactionData $trData = null;

	protected function decodePayload() : void{
		if ($this->protocol >= ProtocolInfo::PROTOCOL_407) {
			$this->requestId = $this->readLegacyItemStackRequestId();
			if ($this->protocol >= ProtocolInfo::PROTOCOL_1001) {
				$hasChangedSlots = $this->getBool();
			} else {
				$hasChangedSlots = $this->requestId !== 0;
			}

			$this->requestChangedSlots = [];
			if ($hasChangedSlots) {
				for ($i = 0, $len = $this->getUnsignedVarInt(); $i < $len; ++$i) {
					$this->requestChangedSlots[] = InventoryTransactionChangedSlotsHack::read($this);
				}
			}
		}

		$hasDummyOptionals = self::hasDummyOptionals($this->protocol);
		if($hasDummyOptionals && !$this->getBool()){
			throw new PacketDecodeException("Dummy optional bool transactionType should always be 1");
		}

		$transactionType = $this->getUnsignedVarInt();
		if($hasDummyOptionals && !$this->getBool()){
			throw new PacketDecodeException("Dummy optional bool for trData should always be 1");
		}

		$this->trData = match ($transactionType) {
			self::TYPE_NORMAL => new NormalTransactionData(),
			self::TYPE_MISMATCH => new MismatchTransactionData(),
			self::TYPE_USE_ITEM => new UseItemTransactionData(),
			self::TYPE_USE_ITEM_ON_ENTITY => new UseItemOnEntityTransactionData(),
			self::TYPE_RELEASE_ITEM => new ReleaseItemTransactionData(),
			default => throw new PacketDecodeException("Unknown transaction type $transactionType"),
		};

		$this->trData->decode($this, true);
	}

	protected function encodePayload() : void{
		if ($this->protocol >= ProtocolInfo::PROTOCOL_407) {
			$this->writeLegacyItemStackRequestId($this->requestId);
			$hasChangedSlots = $this->requestId !== 0;
			if ($this->protocol >= ProtocolInfo::PROTOCOL_1001) {
				$this->putBool($hasChangedSlots);
			}
			if ($hasChangedSlots) {
				$this->putUnsignedVarInt(count($this->requestChangedSlots));
				foreach ($this->requestChangedSlots as $changedSlots) {
					$changedSlots->write($this);
				}
			}
		}

		$hasDummyOptionals = self::hasDummyOptionals($this->protocol);
		if ($hasDummyOptionals) {
			$this->putBool(true);
		}
		$this->putUnsignedVarInt($this->trData->getTypeId());
		if ($hasDummyOptionals) {
			$this->putBool(true);
		}
		$this->trData->encode($this, true);
	}

	/**
	 * The transaction type and data were wrapped in dummy optionals from 1.26.30 until 1.26.50
	 */
	private static function hasDummyOptionals(int $protocol) : bool
	{
		return $protocol >= ProtocolInfo::PROTOCOL_1001 && $protocol < ProtocolInfo::PROTOCOL_2193;
	}

	public function handle(PacketHandlerInterface $session) : bool
	{
		return $session->handleInventoryTransaction($this);
	}
}
