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

namespace pocketmine\level\generator\vanilla\network\mcpe\protocol\types\ddui;

use pocketmine\level\generator\vanilla\network\mcpe\NetworkBinaryStream;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\PacketDecodeException;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ProtocolInfo;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\ddui\update\BoolDataStoreUpdateValue;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\ddui\update\DataStoreUpdateValue;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\ddui\update\DataStoreUpdateValueType;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\ddui\update\DoubleDataStoreUpdateValue;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\ddui\update\StringDataStoreUpdateValue;

/**
 * @see ServerboundDataStorePacket&ClientboundDataStorePacket
 */
final class DataStoreUpdate extends DataStoreOperation{
	public const ID = DataStoreOperationType::UPDATE;

	public function __construct(
		private string $name,
		private string $property,
		private string $path,
		private DataStoreUpdateValue $data,
		private int $updateCount,
		private int $pathUpdateCount
	){}

	public function getTypeId() : DataStoreOperationType{
		return self::ID;
	}

	public function getName() : string{ return $this->name; }

	public function getProperty() : string{ return $this->property; }

	public function getPath() : string{ return $this->path; }

	public function getData() : DataStoreUpdateValue{ return $this->data; }

	public function getUpdateCount() : int{ return $this->updateCount; }

	public function getPathUpdateCount() : int{ return $this->pathUpdateCount; }

	/**
	 * Reads a double/bool/string value, also used by DataStoreChange before 1.26.30
	 */
	public static function readValue(NetworkBinaryStream $in, int $type) : DataStoreUpdateValue{
		return match($type){
			DataStoreUpdateValueType::DOUBLE => DoubleDataStoreUpdateValue::read($in),
			DataStoreUpdateValueType::BOOL => BoolDataStoreUpdateValue::read($in),
			DataStoreUpdateValueType::STRING => StringDataStoreUpdateValue::read($in),
			default => throw new PacketDecodeException("Unknown DataStoreValueType $type"),
		};
	}

	public static function read(NetworkBinaryStream $in) : self{
		$name = $in->getString();
		$property = $in->getString();
		$path = $in->getString();

		$data = self::readValue($in, $in->getUnsignedVarInt());

		if ($in->getProtocol() >= ProtocolInfo::PROTOCOL_924) {
			$updateCount = $in->getLInt();
			$pathUpdateCount = $in->getLInt();
		} else {
			$updateCount = $in->getUnsignedVarInt();
		}

		return new self(
			$name,
			$property,
			$path,
			$data,
			$updateCount,
			$pathUpdateCount ?? 0
		);
	}

	public function write(NetworkBinaryStream $out) : void{
		$out->putString($this->name);
		$out->putString($this->property);
		$out->putString($this->path);
		$out->putUnsignedVarInt($this->data->getTypeId());
		$this->data->write($out);

		if ($out->getProtocol() >= ProtocolInfo::PROTOCOL_924) {
			$out->putLInt($this->updateCount);
			$out->putLInt($this->pathUpdateCount);
		} else {
			$out->putUnsignedVarInt($this->updateCount);
		}
	}
}
