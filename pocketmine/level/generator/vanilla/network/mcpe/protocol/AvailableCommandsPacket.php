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

use InvalidStateException;
use pocketmine\level\generator\vanilla\network\mcpe\convert\ConstantTranslator;
use pocketmine\level\generator\vanilla\network\mcpe\NetworkSession;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\command\ChainedSubCommandData;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\command\ChainedSubCommandValue;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\command\CommandData;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\command\CommandEnum;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\command\CommandEnumConstraint;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\command\CommandOverload;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\command\CommandParameter;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\command\CommandPermissions;
use pocketmine\level\generator\vanilla\utils\BinaryDataException;
use UnexpectedValueException;

use function array_search;
use function count;
use function dechex;
use function json_decode;
use function json_encode;

class AvailableCommandsPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::AVAILABLE_COMMANDS_PACKET;

	/**
	 * This flag is set on all types EXCEPT the POSTFIX type. Not completely sure what this is for, but it is required
	 * for the argtype to work correctly. VALID seems as good a name as any.
	 */
	public const ARG_FLAG_VALID = 0x100000;

	/**
	 * Basic parameter types. These must be combined with the ARG_FLAG_VALID constant.
	 * ARG_FLAG_VALID | (type const)
	 */
	public const ARG_TYPE_INT = 1;
	public const ARG_TYPE_FLOAT = 3;
	public const ARG_TYPE_VALUE = 4;
	public const ARG_TYPE_WILDCARD_INT = 5;
	public const ARG_TYPE_OPERATOR = 6;
	public const ARG_TYPE_COMPARE_OPERATOR = 7;
	public const ARG_TYPE_TARGET = 8;

	public const ARG_TYPE_WILDCARD_TARGET = 10;

	public const ARG_TYPE_FILEPATH = 17;

	public const ARG_TYPE_FULL_INTEGER_RANGE = 23;

	public const ARG_TYPE_EQUIPMENT_SLOT = 47;
	public const ARG_TYPE_STRING = 56;

	public const ARG_TYPE_INT_POSITION = 64;
	public const ARG_TYPE_POSITION = 65;

	public const ARG_TYPE_MESSAGE = 68;

	public const ARG_TYPE_RAWTEXT = 70;

	public const ARG_TYPE_JSON = 74;

	public const ARG_TYPE_BLOCK_STATES = 84;

	public const ARG_TYPE_TIMEMARKER_NAME = 86;

	public const ARG_TYPE_COMMAND = 88;

	/**
	 * Enums are a little different: they are composed as follows:
	 * ARG_FLAG_ENUM | ARG_FLAG_VALID | (enum index)
	 */
	public const ARG_FLAG_ENUM = 0x200000;

	/**
	 * This is used for /xp <level: int>L. It can only be applied to integer parameters.
	 */
	public const ARG_FLAG_POSTFIX = 0x1000000;

	public const ARG_FLAG_SOFT_ENUM = 0x4000000;

	public const HARDCODED_ENUM_NAMES = [
		"CommandName" => true
	];

	/**
	 * @var string|CommandData[]
	 * List of command data, including name, description, alias indexes and parameters.
	 */
	public $commandData = [];

	/**
	 * @var CommandEnum[]
	 * List of enums which aren't directly referenced by any vanilla command.
	 * This is used for the `CommandName` enum, which is a magic enum used by the `command` argument type.
	 */
	public $hardcodedEnums = [];

	/**
	 * @var CommandEnum[]
	 * List of dynamic command enums, also referred to as "soft" enums. These can by dynamically updated mid-game
	 * without resending this packet.
	 */
	public $softEnums = [];

	/**
	 * @var CommandEnumConstraint[]
	 * List of constraints for enum members. Used to constrain gamerules that can bechanged in nocheats mode and more.
	 */
	public $enumConstraints = [];

	/** @var array */
	public $jsonCommandData = [];
	/** @var string */
	public $unknown = "";

	protected function decodePayload() : void
	{
		if ($this->protocol >= ProtocolInfo::PROTOCOL_407) {
			/** @var string[] $enumValues */
			$enumValues = [];
			for ($i = 0, $enumValuesCount = $this->getUnsignedVarInt(); $i < $enumValuesCount; ++$i) {
				$enumValues[] = $this->getString();
			}

			/** @var string[] $chainedSubcommandValueNames */
			$chainedSubcommandValueNames = [];
			if ($this->protocol >= ProtocolInfo::PROTOCOL_594) {
				for ($i = 0, $count = $this->getUnsignedVarInt(); $i < $count; ++$i) {
					$chainedSubcommandValueNames[] = $this->getString();
				}
			}

			/** @var string[] $postfixes */
			$postfixes = [];
			for ($i = 0, $count = $this->getUnsignedVarInt(); $i < $count; ++$i) {
				$postfixes[] = $this->getString();
			}

			/** @var CommandEnum[] $enums */
			$enums = [];
			for ($i = 0, $count = $this->getUnsignedVarInt(); $i < $count; ++$i) {
				$enums[] = $enum = $this->getEnum($enumValues);
				//TODO: Bedrock may provide some enums which are not referenced by any command, and can't reasonably be
				//considered "hardcoded". This happens with various Edu command enums, and other enums which are probably
				//intended to be used by commands which aren't present in public releases.
				//We should probably store these somewhere, since we'll need them to be able to correctly re-encode the
				//packet for testing.
				if (isset(self::HARDCODED_ENUM_NAMES[$enum->getName()])) {
					$this->hardcodedEnums[] = $enum;
				}
			}

			if ($this->protocol >= ProtocolInfo::PROTOCOL_594) {
				$chainedSubCommandData = [];
				for ($i = 0, $count = $this->getUnsignedVarInt(); $i < $count; ++$i) {
					$name = $this->getString();
					$values = [];
					for ($j = 0, $valueCount = $this->getUnsignedVarInt(); $j < $valueCount; ++$j) {
						if ($this->protocol >= ProtocolInfo::PROTOCOL_897) {
							$valueName = $chainedSubcommandValueNames[$this->getUnsignedVarInt()];
							$valueType = $this->getUnsignedVarInt();
						} else {
							$valueName = $chainedSubcommandValueNames[$this->getLShort()];
							$valueType = $this->getLShort();
						}

						$values[] = new ChainedSubCommandValue($valueName, $valueType);
					}
					$chainedSubCommandData[] = new ChainedSubCommandData($name, $values);
				}
			}

			for ($i = 0, $count = $this->getUnsignedVarInt(); $i < $count; ++$i) {
				$this->commandData[] = $this->getCommandData($enums, $postfixes, $chainedSubCommandData ?? []);
			}

			for ($i = 0, $count = $this->getUnsignedVarInt(); $i < $count; ++$i) {
				$this->softEnums[] = $this->getSoftEnum();
			}

			$this->initSoftEnumsInCommandData();
			for ($i = 0, $count = $this->getUnsignedVarInt(); $i < $count; ++$i) {
				$this->enumConstraints[] = $this->getEnumConstraint($enums, $enumValues);
			}
		} else {
			$this->jsonCommandData = json_decode($this->getString(), true);
			$this->unknown = $this->getString();
		}
	}

	/**
	 * Command data is decoded without referencing to any soft enums, as they are decoded afterwards.
	 * So we need to separately add soft enums to the command data
	 */
	protected function initSoftEnumsInCommandData() : void
	{
		foreach ($this->commandData as $datum) {
			foreach ($datum->overloads as $overload) {
				foreach ($overload->getParameters() as $parameter) {
					if (($parameter->paramType & self::ARG_FLAG_SOFT_ENUM) !== 0) {
						$index = $parameter->paramType & 0xffff;
						$parameter->enum = $this->softEnums[$index] ?? null;
						if ($parameter->enum === null) {
							throw new PacketDecodeException("deserializing $datum->name parameter $parameter->paramName: expected soft enum at $index, but got none");
						}
					}
				}
			}
		}
	}

	/**
	 * @param string[] $enumValueList
	 *
	 * @throws UnexpectedValueException
	 * @throws BinaryDataException
	 */
	protected function getEnum(array $enumValueList) : CommandEnum
	{
		$enumName = $this->getString();
		$enumValues = [];

		$listSize = count($enumValueList);

		for ($i = 0, $count = $this->getUnsignedVarInt(); $i < $count; ++$i) {
			$index = $this->getEnumValueIndex($listSize);
			if (!isset($enumValueList[$index])) {
				throw new PacketDecodeException("Invalid enum value index $index");
			}
			//Get the enum value from the initial pile of mess
			$enumValues[] = $enumValueList[$index];
		}

		return new CommandEnum($enumName, $enumValues);
	}

	protected function getSoftEnum() : CommandEnum
	{
		$enumName = $this->getString();
		$enumValues = [];

		for ($i = 0, $count = $this->getUnsignedVarInt(); $i < $count; ++$i) {
			//Get the enum value from the initial pile of mess
			$enumValues[] = $this->getString();
		}

		return new CommandEnum($enumName, $enumValues, true);
	}

	/**
	 * @param int[] $enumValueMap string enum name -> int index
	 */
	protected function putEnum(CommandEnum $enum, array $enumValueMap) : void
	{
		$this->putString($enum->getName());

		$values = $enum->getValues();
		$this->putUnsignedVarInt(count($values));
		$listSize = count($enumValueMap);
		foreach ($values as $value) {
			if (!isset($enumValueMap[$value])) {
				throw new \LogicException("Enum value '$value' doesn't have a value index");
			}
			$this->putEnumValueIndex($enumValueMap[$value], $listSize);
		}
	}

	protected function putSoftEnum(CommandEnum $enum) : void
	{
		$this->putString($enum->getName());

		$values = $enum->getValues();
		$this->putUnsignedVarInt(count($values));
		foreach ($values as $value) {
			$this->putString($value);
		}
	}

	/**
	 * @throws BinaryDataException
	 */
	protected function getEnumValueIndex(int $valueCount) : int
	{
		if ($this->protocol >= ProtocolInfo::PROTOCOL_897) {
			return $this->getLInt();
		} else {
			if ($valueCount < 256) {
				return $this->getByte();
			} elseif ($valueCount < 65536) {
				return $this->getLShort();
			} else {
				return $this->getLInt();
			}
		}
	}

	protected function putEnumValueIndex(int $index, int $valueCount) : void
	{
		if ($this->protocol >= ProtocolInfo::PROTOCOL_897) {
			$this->putLInt($index);
		} else {
			if ($valueCount < 256) {
				$this->putByte($index);
			} elseif ($valueCount < 65536) {
				$this->putLShort($index);
			} else {
				$this->putLInt($index);
			}
		}
	}

	/**
	 * @param CommandEnum[] $enums
	 * @param string[]      $enumValues
	 */
	protected function getEnumConstraint(array $enums, array $enumValues) : CommandEnumConstraint
	{
		//wtf, what was wrong with an offset inside the enum? :(
		$valueIndex = $this->getLInt();
		if (!isset($enumValues[$valueIndex])) {
			throw new PacketDecodeException("Enum constraint refers to unknown enum value index $valueIndex");
		}
		$enumIndex = $this->getLInt();
		if (!isset($enums[$enumIndex])) {
			throw new PacketDecodeException("Enum constraint refers to unknown enum index $enumIndex");
		}
		$enum = $enums[$enumIndex];
		$valueOffset = array_search($enumValues[$valueIndex], $enum->getValues(), true);
		if ($valueOffset === false) {
			throw new PacketDecodeException("Value \"" . $enumValues[$valueIndex] . "\" does not belong to enum \"" . $enum->getName() . "\"");
		}

		$constraintIds = [];
		for ($i = 0, $count = $this->getUnsignedVarInt(); $i < $count; ++$i) {
			$constraintIds[] = $this->getByte();
		}

		return new CommandEnumConstraint($enum, $valueOffset, $constraintIds);
	}

	/**
	 * @param int[] $enumIndexes      string enum name -> int index
	 * @param int[] $enumValueIndexes string value -> int index
	 */
	protected function putEnumConstraint(CommandEnumConstraint $constraint, array $enumIndexes, array $enumValueIndexes) : void
	{
		$this->putLInt($enumValueIndexes[$constraint->getAffectedValue()]);
		$this->putLInt($enumIndexes[$constraint->getEnum()->getName()]);
		$this->putUnsignedVarInt(count($constraint->getConstraints()));
		foreach ($constraint->getConstraints() as $v) {
			$this->putByte($v);
		}
	}

	/**
	 * @param CommandEnum[]           $enums
	 * @param string[]                $postfixes
	 * @param ChainedSubCommandData[] $allChainedSubCommandData
	 *
	 * @throws UnexpectedValueException
	 * @throws BinaryDataException
	 */
	protected function getCommandData(array $enums, array $postfixes, array $allChainedSubCommandData) : CommandData
	{
		$name = $this->getString();
		$description = $this->getString();
		if ($this->protocol < ProtocolInfo::PROTOCOL_448) {
			$flags = $this->getByte();
		} else {
			$flags = $this->getLShort();
		}

		if ($this->protocol >= ProtocolInfo::PROTOCOL_897) {
			$permission = CommandPermissions::fromName($this->getString());
		} else {
			$permission = CommandPermissions::fromPacket($this->getByte());
		}

		$aliases = $enums[$this->getLInt()] ?? null;

		if ($this->protocol >= ProtocolInfo::PROTOCOL_594) {
			$chainedSubCommandData = [];
			for ($i = 0, $count = $this->getUnsignedVarInt(); $i < $count; ++$i) {
				if ($this->protocol >= ProtocolInfo::PROTOCOL_897) {
					$index = $this->getLInt();
				} else {
					$index = $this->getLShort();
				}

				$chainedSubCommandData[] = $allChainedSubCommandData[$index] ?? throw new PacketDecodeException("Unknown chained subcommand data index $index");
			}
		}

		$overloads = [];

		for ($overloadIndex = 0, $overloadCount = $this->getUnsignedVarInt(); $overloadIndex < $overloadCount; ++$overloadIndex) {
			$parameters = [];
			if ($this->protocol >= ProtocolInfo::PROTOCOL_594) {
				$isChaining = $this->getBool();
			}
			for ($paramIndex = 0, $paramCount = $this->getUnsignedVarInt(); $paramIndex < $paramCount; ++$paramIndex) {
				$parameter = new CommandParameter();
				$parameter->paramName = $this->getString();
				$parameter->paramType = $this->getLInt();
				$parameter->isOptional = $this->getBool();
				if ($this->protocol >= ProtocolInfo::PROTOCOL_407) {
					$parameter->flags = $this->getByte();
				}

				if ($parameter->paramType & self::ARG_FLAG_ENUM) {
					$index = ($parameter->paramType & 0xffff);
					$parameter->enum = $enums[$index] ?? null;
					if ($parameter->enum === null) {
						throw new PacketDecodeException("deserializing $name parameter $parameter->paramName: expected enum at $index, but got none");
					}
				} elseif ($parameter->paramType & self::ARG_FLAG_POSTFIX) {
					$index = ($parameter->paramType & 0xffff);
					$parameter->postfix = $postfixes[$index] ?? null;
					if ($parameter->postfix === null) {
						throw new PacketDecodeException("deserializing $name parameter $parameter->paramName: expected postfix at $index, but got none");
					}
				} elseif (($parameter->paramType & self::ARG_FLAG_VALID) === 0) {
					throw new PacketDecodeException("deserializing $name parameter $parameter->paramName: Invalid parameter type 0x" . dechex($parameter->paramType));
				} else {
					$type = $parameter->paramType & ~self::ARG_FLAG_VALID;
					$type = ConstantTranslator::getInstance()->fromNetworkId(AvailableCommandsPacket::class, $type, $this->protocol);
					$parameter->paramType = $type | self::ARG_FLAG_VALID;
				}

				$parameters[$paramIndex] = $parameter;
			}
			$overloads[$overloadIndex] = new CommandOverload($isChaining ?? false, $parameters);
		}

		return new CommandData($name, $description, $flags, $permission, $aliases, $overloads, $chainedSubCommandData ?? []);
	}

	/**
	 * @param int[] $enumIndexes                  string enum name -> int index
	 * @param int[] $softEnumIndexes
	 * @param int[] $postfixIndexes
	 * @param int[] $chainedSubCommandDataIndexes
	 */
	protected function putCommandData(CommandData $data, array $enumIndexes, array $softEnumIndexes, array $postfixIndexes, array $chainedSubCommandDataIndexes) : void
	{
		$this->putString($data->name);
		$this->putString($data->description);
		if ($this->protocol < ProtocolInfo::PROTOCOL_448) {
			$this->putByte($data->flags);
		} else {
			$this->putLShort($data->flags);
		}

		if ($this->protocol >= ProtocolInfo::PROTOCOL_897) {
			$this->putString($data->permission->getName());
		} else {
			$this->putByte($data->permission->value);
		}

		if ($data->aliases !== null) {
			$this->putLInt($enumIndexes[$data->aliases->getName()] ?? -1);
		} else {
			$this->putLInt(-1);
		}

		if ($this->protocol >= ProtocolInfo::PROTOCOL_594) {
			$this->putUnsignedVarInt(count($data->chainedSubCommandData));
			foreach ($data->chainedSubCommandData as $chainedSubCommandData) {
				$index = $chainedSubCommandDataIndexes[$chainedSubCommandData->getName()] ??
					throw new \LogicException("Chained subcommand data {$chainedSubCommandData->getName()} does not have an index (this should be impossible)");

				if ($this->protocol >= ProtocolInfo::PROTOCOL_897) {
					$this->putLInt($index);
				} else {
					$this->putLShort($index);
				}
			}
		}

		$this->putUnsignedVarInt(count($data->overloads));
		foreach ($data->overloads as $overload) {
			if ($this->protocol >= ProtocolInfo::PROTOCOL_594) {
				$this->putBool($overload->isChaining());
			}
			$this->putUnsignedVarInt(count($overload->getParameters()));
			foreach ($overload->getParameters() as $parameter) {
				$this->putString($parameter->paramName);

				if ($parameter->enum !== null) {
					if ($parameter->enum->isSoft()) {
						$type = self::ARG_FLAG_SOFT_ENUM | self::ARG_FLAG_VALID | ($softEnumIndexes[$parameter->enum->getName()] ?? -1);
					} else {
						$type = self::ARG_FLAG_ENUM | self::ARG_FLAG_VALID | ($enumIndexes[$parameter->enum->getName()] ?? -1);
					}
				} elseif ($parameter->postfix !== null) {
					$key = $postfixIndexes[$parameter->postfix] ?? -1;
					if ($key === -1) {
						throw new InvalidStateException("Postfix '$parameter->postfix' not in postfixes array");
					}
					$type = self::ARG_FLAG_POSTFIX | $key;
				} else {
					$type = $parameter->paramType;
					if (($type & self::ARG_FLAG_VALID) !== 0x0) {
						$type &= ~self::ARG_FLAG_VALID;
					}
					$type = ConstantTranslator::getInstance()->toNetworkId(AvailableCommandsPacket::class, $type, $this->protocol, AvailableCommandsPacket::ARG_TYPE_RAWTEXT);
					$type |= self::ARG_FLAG_VALID;
				}

				$this->putLInt($type);
				$this->putBool($parameter->isOptional);
				if ($this->protocol >= ProtocolInfo::PROTOCOL_407) {
					$this->putByte($parameter->flags);
				}
			}
		}
	}

	protected function encodePayload() : void
	{
		if ($this->protocol >= ProtocolInfo::PROTOCOL_407) {
			/**
			 * @var int[] $enumValueIndexes
			 * @phpstan-var array<string, int> $enumValueIndexes
			 */
			$enumValueIndexes = [];
			/**
			 * @var int[] $postfixIndexes
			 * @phpstan-var array<string, int> $postfixIndexes
			 */
			$postfixIndexes = [];

			/**
			 * @var CommandEnum[] $enums
			 * @phpstan-var array<string, CommandEnum> $enums
			 */
			$enums = [];
			/**
			 * @var int[] $enumIndexes
			 * @phpstan-var array<string, int> $enumIndexes
			 */
			$enumIndexes = [];

			/**
			 * @var CommandEnum[] $softEnums
			 * @phpstan-var array<string, CommandEnum> $softEnums
			 */
			$softEnums = [];
			/**
			 * @var int[] $softEnumIndexes
			 * @phpstan-var array<string, int> $softEnumIndexes
			 */
			$softEnumIndexes = [];

			/**
			 * @var ChainedSubCommandData[] $allChainedSubCommandData
			 * @phpstan-var array<string, ChainedSubCommandData> $allChainedSubCommandData
			 */
			$allChainedSubCommandData = [];
			/**
			 * @var int[] $chainedSubCommandDataIndexes
			 * @phpstan-var array<string, int> $chainedSubCommandDataIndexes
			 */
			$chainedSubCommandDataIndexes = [];

			/**
			 * @var int[] $chainedSubCommandValueNameIndexes
			 * @phpstan-var array<string, int> $chainedSubCommandValueNameIndexes
			 */
			$chainedSubCommandValueNameIndexes = [];

			$addEnumFn = static function (CommandEnum $enum) use (
				&$enums,
				&$softEnums,
				&$enumIndexes,
				&$softEnumIndexes,
				&$enumValueIndexes
			) : void {
				$enumName = $enum->getName();

				if ($enum->isSoft()) {
					if (!isset($softEnumIndexes[$enumName])) {
						$softEnums[$softEnumIndexes[$enumName] = count($softEnumIndexes)] = $enum;
					}
				} else {
					foreach ($enum->getValues() as $str) {
						$enumValueIndexes[$str] = $enumValueIndexes[$str] ?? count($enumValueIndexes); //latest index
					}
					if (!isset($enumIndexes[$enumName])) {
						$enums[$enumIndexes[$enumName] = count($enumIndexes)] = $enum;
					}
				}
			};

			foreach ($this->hardcodedEnums as $enum) {
				$addEnumFn($enum);
			}
			foreach ($this->softEnums as $enum) {
				$addEnumFn($enum);
			}
			foreach ($this->commandData as $commandData) {
				if ($commandData->aliases !== null) {
					$addEnumFn($commandData->aliases);
				}
				foreach ($commandData->overloads as $overload) {
					foreach ($overload->getParameters() as $parameter) {
						if ($parameter->enum !== null) {
							$addEnumFn($parameter->enum);
						}

						if ($parameter->postfix !== null) {
							$postfixIndexes[$parameter->postfix] = $postfixIndexes[$parameter->postfix] ?? count($postfixIndexes);
						}
					}
				}
				foreach ($commandData->chainedSubCommandData as $chainedSubCommandData) {
					if (!isset($allChainedSubCommandData[$chainedSubCommandData->getName()])) {
						$allChainedSubCommandData[$chainedSubCommandData->getName()] = $chainedSubCommandData;
						$chainedSubCommandDataIndexes[$chainedSubCommandData->getName()] = count($chainedSubCommandDataIndexes);

						foreach ($chainedSubCommandData->getValues() as $value) {
							$chainedSubCommandValueNameIndexes[$value->getName()] ??= count($chainedSubCommandValueNameIndexes);
						}
					}
				}
			}

			$this->putUnsignedVarInt(count($enumValueIndexes));
			foreach ($enumValueIndexes as $enumValue => $index) {
				$this->putString((string) $enumValue); //stupid PHP key casting D:
			}

			if ($this->protocol >= ProtocolInfo::PROTOCOL_594) {
				$this->putUnsignedVarInt(count($chainedSubCommandValueNameIndexes));
				foreach ($chainedSubCommandValueNameIndexes as $chainedSubCommandValueName => $index) {
					$this->putString((string) $chainedSubCommandValueName); //stupid PHP key casting D:
				}
			}

			$this->putUnsignedVarInt(count($postfixIndexes));
			foreach ($postfixIndexes as $postfix => $index) {
				$this->putString((string) $postfix); //stupid PHP key casting D:
			}

			$this->putUnsignedVarInt(count($enums));
			foreach ($enums as $enum) {
				$this->putEnum($enum, $enumValueIndexes);
			}

			if ($this->protocol >= ProtocolInfo::PROTOCOL_594) {
				$this->putUnsignedVarInt(count($allChainedSubCommandData));
				foreach ($allChainedSubCommandData as $chainedSubCommandData) {
					$this->putString($chainedSubCommandData->getName());
					$this->putUnsignedVarInt(count($chainedSubCommandData->getValues()));
					foreach ($chainedSubCommandData->getValues() as $value) {
						$valueNameIndex = $chainedSubCommandValueNameIndexes[$value->getName()] ??
							throw new \LogicException("Chained subcommand value name index for \"" . $value->getName() . "\" not found (this should never happen)");

						if ($this->protocol >= ProtocolInfo::PROTOCOL_897) {
							$this->putUnsignedVarInt($valueNameIndex);
							$this->putUnsignedVarInt($value->getType());
						} else {
							$this->putLShort($valueNameIndex);
							$this->putLShort($value->getType());
						}
					}
				}
			}

			$this->putUnsignedVarInt(count($this->commandData));
			foreach ($this->commandData as $data) {
				$this->putCommandData($data, $enumIndexes, $softEnumIndexes, $postfixIndexes, $chainedSubCommandDataIndexes);
			}

			$this->putUnsignedVarInt(count($this->softEnums));
			foreach ($this->softEnums as $enum) {
				$this->putSoftEnum($enum);
			}

			$this->putUnsignedVarInt(count($this->enumConstraints));
			foreach ($this->enumConstraints as $constraint) {
				$this->putEnumConstraint($constraint, $enumIndexes, $enumValueIndexes);
			}
		} else {
			$this->putString(json_encode($this->jsonCommandData));
			$this->putString($this->unknown);
		}
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleAvailableCommands($this);
	}
}
