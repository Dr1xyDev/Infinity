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

use pocketmine\level\generator\vanilla\network\mcpe\NetworkBinaryStream;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ProtocolInfo;
use pocketmine\level\generator\vanilla\utils\BinaryDataException;
use UnexpectedValueException as PacketDecodeException;

use function count;

abstract class TransactionData
{
	/** @var NetworkInventoryAction[] */
	protected array $actions = [];

	protected bool $hasItemStackIds = true;

	/**
	 * @return NetworkInventoryAction[]
	 */
	final public function getActions() : array
	{
		return $this->actions;
	}

	abstract public function getTypeId() : int;

	/**
	 * @throws BinaryDataException
	 * @throws PacketDecodeException
	 */
	final public function decode(NetworkBinaryStream $in, bool $legacyTransaction) : void
	{
		if ($in->getProtocol() >= ProtocolInfo::PROTOCOL_407 && $in->getProtocol() < ProtocolInfo::PROTOCOL_431) {
			$this->hasItemStackIds = $in->getBool();
		}

		$actionCount = $in->getUnsignedVarInt();
		for ($i = 0; $i < $actionCount; ++$i) {
			$this->actions[] = (new NetworkInventoryAction())->read($in, $legacyTransaction, $this->hasItemStackIds);
		}
		$this->decodeData($in, $legacyTransaction);
	}

	/**
	 * @throws BinaryDataException
	 * @throws PacketDecodeException
	 */
	abstract protected function decodeData(NetworkBinaryStream $in, bool $legacyTransaction) : void;

	final public function encode(NetworkBinaryStream $out, bool $legacyTransaction) : void
	{
		if ($out->getProtocol() >= ProtocolInfo::PROTOCOL_407 && $out->getProtocol() < ProtocolInfo::PROTOCOL_431) {
			$out->putBool($this->hasItemStackIds);
		}

		$out->putUnsignedVarInt(count($this->actions));
		foreach ($this->actions as $action) {
			$action->write($out, $legacyTransaction, $this->hasItemStackIds);
		}
		$this->encodeData($out, $legacyTransaction);
	}

	abstract protected function encodeData(NetworkBinaryStream $out, bool $legacyTransaction) : void;
}
