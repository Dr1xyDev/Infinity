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

use pocketmine\level\generator\vanilla\nbt\LittleEndianNBTStream;
use pocketmine\level\generator\vanilla\nbt\tag\CompoundTag;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\ItemTypeEntry;
use pocketmine\level\generator\vanilla\utils\AssumptionFailedError;
use pocketmine\level\generator\vanilla\utils\Filesystem;
use function base64_decode;
use function is_array;
use function is_bool;
use function is_int;
use function is_string;
use function json_decode;
use const pocketmine\BEDROCK_DATA_PATH;

final class GlobalItemTypeDictionary
{
	/** @var self[] */
	private static array $instance = [];

	public static function getInstance(int $protocolVersion) : self
	{
		$protocolVersion = ProtocolConvertor::getInstance()->getItemPaletteProtocol($protocolVersion);
		if (!isset(self::$instance[$protocolVersion])) {
			self::$instance[$protocolVersion] = self::make($protocolVersion);
		}
		return self::$instance[$protocolVersion];
	}

	private static function make(int $protocolVersion) : self
	{
		$table = json_decode(Filesystem::resourceGetContents(BEDROCK_DATA_PATH . "items/" . $protocolVersion . "/required_item_list.json"), true);
		if (!is_array($table)) {
			throw new AssumptionFailedError("Invalid item list format");
		}

		$params = [];
		$emptyNBT = new CompoundTag();
		$nbtSerializer = new LittleEndianNBTStream();
		foreach ($table as $name => $entry) {
			if (!is_array($entry) || !is_string($name) || !isset($entry["component_based"], $entry["runtime_id"]) || !is_bool($entry["component_based"]) || !is_int($entry["runtime_id"])) {
				throw new AssumptionFailedError("Invalid item list format");
			}
			$params[] = new ItemTypeEntry($name, $entry["runtime_id"], $entry["component_based"], $entry["version"] ?? 0, isset($entry["component_nbt"]) ? $nbtSerializer->read(base64_decode($entry["component_nbt"], true)) : $emptyNBT);
		}
		return new self(new ItemTypeDictionary($params));
	}

	public function __construct(
		private ItemTypeDictionary $dictionary
	) {
	}

	public function getDictionary() : ItemTypeDictionary
	{
		return $this->dictionary;
	}
}
