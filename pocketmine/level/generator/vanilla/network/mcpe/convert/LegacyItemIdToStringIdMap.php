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

namespace pocketmine\level\generator\vanilla\network\mcpe\convert;

use pocketmine\level\generator\vanilla\utils\AssumptionFailedError;
use pocketmine\level\generator\vanilla\utils\Filesystem;

use function is_array;
use function is_int;
use function is_string;
use function json_decode;

use const pocketmine\BEDROCK_DATA_PATH;

class LegacyItemIdToStringIdMap
{
	/** @var self[] */
	private static array $instance = [];

	/**
	 * @var string[]
	 * @phpstan-var array<int, string>
	 */
	private array $legacyToString = [];
	/**
	 * @var int[]
	 * @phpstan-var array<string, int>
	 */
	private array $stringToLegacy = [];

	public static function getInstance(int $protocolVersion) : self
	{
		$protocolVersion = ProtocolConvertor::getInstance()->getLegacyItemProtocol($protocolVersion);
		if (!isset(self::$instance[$protocolVersion])) {
			self::$instance[$protocolVersion] = new self($protocolVersion);
		}
		return self::$instance[$protocolVersion];
	}

	public function __construct(int $protocolVersion)
	{
		$stringToLegacyId = json_decode(Filesystem::resourceGetContents(BEDROCK_DATA_PATH . "items/" . $protocolVersion . "/item_id_map.json"), true);
		if (!is_array($stringToLegacyId)) {
			throw new AssumptionFailedError("Invalid format of ID map");
		}

		foreach ($stringToLegacyId as $stringId => $legacyId) {
			if (!is_string($stringId) || !is_int($legacyId)) {
				throw new AssumptionFailedError("ID map should have string keys and int values");
			}
			$this->legacyToString[$legacyId] = $stringId;
			$this->stringToLegacy[$stringId] = $legacyId;
		}
	}

	public function legacyToString(int $legacy) : ?string
	{
		return $this->legacyToString[$legacy] ?? null;
	}

	public function stringToLegacy(string $string) : ?int
	{
		return $this->stringToLegacy[$string] ?? null;
	}

	/**
	 * @return string[]
	 * @phpstan-return array<int, string>
	 */
	public function getLegacyToStringMap() : array
	{
		return $this->legacyToString;
	}

	/**
	 * @return int[]
	 * @phpstan-return array<string, int>
	 */
	public function getStringToLegacyMap() : array
	{
		return $this->stringToLegacy;
	}
}
