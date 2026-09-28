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
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ProtocolInfo;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\cereal\DynamicValue;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\cereal\DynamicValueBool;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\cereal\DynamicValueDouble;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\cereal\DynamicValueLong;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\cereal\DynamicValueString;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\cereal\DynamicValueType;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\ddui\update\BoolDataStoreUpdateValue;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\ddui\update\DataStoreUpdateValue;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\ddui\update\DoubleDataStoreUpdateValue;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\ddui\update\StringDataStoreUpdateValue;

/**
 * Wire formats:
 *  - before 1.26.30: varint update count, varint value type, value as a DataStoreUpdateValue (double/bool/string)
 *  - 1.26.30+: LE int update count, LE int value type, value as a cereal::DynamicValue (nullable)
 * Values of either family are converted when needed.
 *
 * @see ClientboundDataStorePacket
 */
final class DataStoreChange extends DataStoreOperation{

	public const ID = DataStoreOperationType::CHANGE;

	public function __construct(
		private string $name,
		private string $property,
		private int $updateCount,
		private DynamicValue|DataStoreUpdateValue|null $data
	){}

	public function getTypeId() : DataStoreOperationType{
		return self::ID;
	}

	public function getName() : string{ return $this->name; }

	public function getProperty() : string{ return $this->property; }

	public function getUpdateCount() : int{ return $this->updateCount; }

	public function getData() : DynamicValue|DataStoreUpdateValue|null{ return $this->data; }

	public static function read(NetworkBinaryStream $in) : self{
		$name = $in->getString();
		$property = $in->getString();

		if ($in->getProtocol() >= ProtocolInfo::PROTOCOL_1001) {
			$updateCount = $in->getLInt();
			$data = DynamicValue::read($in, $in->getLInt());
		} else {
			$updateCount = $in->getUnsignedVarInt();
			$data = DataStoreUpdate::readValue($in, $in->getUnsignedVarInt());
		}

		return new self(
			$name,
			$property,
			$updateCount,
			$data,
		);
	}

	public function write(NetworkBinaryStream $out) : void{
		$out->putString($this->name);
		$out->putString($this->property);

		if ($out->getProtocol() >= ProtocolInfo::PROTOCOL_1001) {
			$out->putLInt($this->updateCount);
			$value = self::toDynamicValue($this->data);
			$out->putLInt($value?->getTypeId() ?? DynamicValueType::NULL);
			$value?->write($out);
		} else {
			$out->putUnsignedVarInt($this->updateCount);
			$value = self::toUpdateValue($this->data);
			$out->putUnsignedVarInt($value->getTypeId());
			$value->write($out);
		}
	}

	private static function toDynamicValue(DynamicValue|DataStoreUpdateValue|null $data) : ?DynamicValue{
		return match (true) {
			$data === null, $data instanceof DynamicValue => $data,
			$data instanceof BoolDataStoreUpdateValue => new DynamicValueBool($data->getValue()),
			$data instanceof DoubleDataStoreUpdateValue => new DynamicValueDouble($data->getValue()),
			$data instanceof StringDataStoreUpdateValue => new DynamicValueString($data->getValue()),
			default => throw new \InvalidArgumentException("Unsupported data store value " . $data::class)
		};
	}

	private static function toUpdateValue(DynamicValue|DataStoreUpdateValue|null $data) : DataStoreUpdateValue{
		return match (true) {
			$data instanceof DataStoreUpdateValue => $data,
			$data === null => new StringDataStoreUpdateValue(""),
			$data instanceof DynamicValueBool => new BoolDataStoreUpdateValue($data->getValue()),
			$data instanceof DynamicValueDouble => new DoubleDataStoreUpdateValue($data->getValue()),
			$data instanceof DynamicValueLong => new DoubleDataStoreUpdateValue((float) $data->getValue()),
			$data instanceof DynamicValueString => new StringDataStoreUpdateValue($data->getValue()),
			default => throw new \InvalidArgumentException("Lists and maps can't be sent to clients older than 1.26.30")
		};
	}
}
