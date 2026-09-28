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

namespace pocketmine\level\generator\vanilla\command;

use InvalidArgumentException;
use JsonSerializable;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\command\CommandData;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\command\CommandEnum;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\command\CommandParameter;

use function count;
use function implode;
use function sprintf;

final class CommandListJson implements JsonSerializable
{
	public const PERMISSION_NORMAL = 0;
	public const PERMISSION_OPERATOR = 1;
	public const PERMISSION_HOST = 2;
	public const PERMISSION_AUTOMATION = 3;
	public const PERMISSION_ADMIN = 4;

	public const MAGIC = "overload%d";

	public static function permName(int $permission) : string
	{
		switch ($permission) {
			case self::PERMISSION_NORMAL:
				return "any";
			case self::PERMISSION_OPERATOR:
				return "gamemasters";
			case self::PERMISSION_HOST:
				return "host";
			case self::PERMISSION_ADMIN:
				return "admin";
		}

		throw new InvalidArgumentException("Permission $permission not exists");
	}

	/** @var array */
	protected $commandData;

	public function __construct(array $commandData)
	{
		$this->commandData = $commandData;
	}

	public function jsonSerialize() : array
	{
		$json = [];
		foreach ($this->commandData as $data) {
			$json[$data->commandName] = self::dataJson($data);
		}

		return $json;
	}

	public static function dataJson(CommandData $commandData) : array
	{
		$data = [
			"description" => $commandData->description,
			"permission" => self::permName($commandData->permission->value)
		];

		if ($commandData->aliases instanceof CommandEnum) {
			$data["aliases"] = $commandData->aliases->getValues();
		}

		$data["overloads"] = [];
		if (count($commandData->overloads) === 0) {
			$data["overloads"]["default"] = [
				"input" => [
					"parameters" => []
				]
			];
		} else {
			foreach ($commandData->overloads as $i => $overload) {
				$overloadData = [
					"input" => [
						"parameters" => self::overloadJson($overload->getParameters())
					],
					"output" => [
						"parameters" => []
					]
				];

				$newData = null;
				foreach ($overload->getParameters() as $parameter) {
					if ($parameter->postfix !== null) {
						$newData = [];
						foreach ($overload->getParameters() as $key => $value) {
							$newData[] = "\{$key\}" . ($value->postfix === null ? "" : $value->postfix);
						}

						break;
					}
				}

				if ($newData !== null) {
					$overloadData["parser"] = implode(" ", $newData);
				}

				$data["overloads"][sprintf(self::MAGIC, $i)] = $overloadData;
			}
		}

		return ["versions" => [
			$data
		]];
	}

	public static function overloadJson(array $overload) : array
	{
		$jsonOverload = [];
		foreach ($overload as $param) {
			$jsonOverload[] = self::parameterJson($param);
		}

		return $jsonOverload;
	}

	public static function parameterJson(CommandParameter $parameter) : array
	{
		$data = ["name" => $parameter->paramName];
		$data["type"] = self::getParameterType($parameter, $data);
		if ($parameter->enum !== null && $data["type"] === "stringenum") {
			static $paramTypes = [
				"EntityType" => "entityType",
				"Effect" => "effectType",
				"Enchant" => "enchantmentType",
				"Item" => "itemType",
				"Block" => "blockType",
				"BoolGameRule" => "gameRuleTypes",
				"IntGameRule" => "gameRuleTypes",
				"Feature" => "featureType"
			];

			$data["type"] = "stringenum";
			$data["enum_type"] = $paramTypes[$parameter->enum->getName()] ?? $parameter->enum->getName();

			if (count($parameter->enum->getValues()) > 0) {
				$data["enum_values"] = $parameter->enum->getValues();
			}
		}

		//$data["optional"] = $parameter->isOptional;
		$data["optional"] = true;

		return $data;
	}

	public static function getParameterType(CommandParameter $parameter, ?array &$data = []) : string
	{
		if ($parameter->enum !== null) {
			if ($parameter->enum->getName() === "Boolean") {
				return "bool";
			}

			return "stringenum";
		}

		switch ($parameter->paramType & 0xffff) {
			case AvailableCommandsPacket::ARG_TYPE_INT:
				return "int";
			case AvailableCommandsPacket::ARG_TYPE_FLOAT:
				return "float";
			case AvailableCommandsPacket::ARG_TYPE_VALUE:
				return "rotation";
			case AvailableCommandsPacket::ARG_TYPE_TARGET:
				return "target";
			case AvailableCommandsPacket::ARG_TYPE_STRING:
				return "string";
			case AvailableCommandsPacket::ARG_TYPE_POSITION:
				return "blockpos";
			case AvailableCommandsPacket::ARG_TYPE_MESSAGE:
				return "rawtext";
			case AvailableCommandsPacket::ARG_TYPE_RAWTEXT:
				return "rawtext";
			case AvailableCommandsPacket::ARG_TYPE_JSON:
				return "components";
			case AvailableCommandsPacket::ARG_TYPE_COMMAND:
				$data["enum_type"] = "commandName";
				return "stringenum";
			default:
				return "unknown";
		}
	}
}
