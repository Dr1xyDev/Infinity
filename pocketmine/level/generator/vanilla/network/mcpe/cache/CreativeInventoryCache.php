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

namespace pocketmine\level\generator\vanilla\network\mcpe\cache;

use pocketmine\level\generator\vanilla\item\Durable;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\item\ItemFactory;
use pocketmine\level\generator\vanilla\network\mcpe\convert\TypeConverter;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ContainerSetContentPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\CreativeContentPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\DataPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ProtocolInfo;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\inventory\ContainerIds;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\inventory\CreativeGroupEntry;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\inventory\CreativeItemEntry;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\inventory\ItemStack;
use pocketmine\Player;
use pocketmine\level\generator\vanilla\utils\Filesystem;
use pocketmine\level\generator\vanilla\utils\SingletonTrait;
use function array_diff;
use function base64_decode;
use function file_exists;
use function hex2bin;
use function json_decode;
use function krsort;
use function scandir;
use function sort;
use function spl_object_id;
use const PHP_INT_MIN;
use const pocketmine\BEDROCK_DATA_PATH;

final class CreativeInventoryCache
{
	use SingletonTrait;

	private static function make() : self
	{
		/** @var CreativeInventoryEntry[][] $creativeInventoryEntries */
		$creativeInventoryEntries = [];

		$itemDeserializerFunc = function (array $data) : Item {
			$nbt = null;
			//Backwards compatibility
			if (isset($data["nbt"])) {
				$nbt = $data["nbt"];
			} elseif (isset($data["nbt_hex"])) {
				$nbt = hex2bin($data["nbt_hex"]);
			} elseif (isset($data["nbt_b64"])) {
				$nbt = base64_decode($data["nbt_b64"], true);
			}

			return ItemFactory::get(
				(int) $data["id"],
				(int) ($data["meta"] ?? 0),
				1,
				$nbt
			);
		};

		$protocols = [];
		foreach (array_diff(scandir(BEDROCK_DATA_PATH . 'creative/'), ["..", "."]) as $protocol) {
			$protocols[] = (int) $protocol;
		}

		sort($protocols);

		$protocolCategories = [];
		foreach ($protocols as $protocol) {
			$index = 1;
			$creativeItemEntry = [];
			if ($protocol >= ProtocolInfo::PROTOCOL_776) {
				foreach ([
					"construction" => CreativeCategory::CONSTRUCTION,
					"nature" => CreativeCategory::NATURE,
					"equipment" => CreativeCategory::EQUIPMENT,
					"items" => CreativeCategory::ITEMS,
				] as $categoryName => $categoryId) {
					$categoryFilePath = BEDROCK_DATA_PATH . 'creative/' . $protocol . '/' . $categoryName . '.json';
					if (!Filesystem::resourceExists($categoryFilePath)) {
						$categoryFilePath = BEDROCK_DATA_PATH . 'creative/' . ($protocolCategories[$categoryName] ?? ProtocolInfo::PROTOCOL_776) . '/' . $categoryName . '.json';
					} else {
						$protocolCategories[$categoryName] = $protocol;
					}

					foreach (json_decode(Filesystem::resourceGetContents($categoryFilePath), true) as $groupData) {
						$icon = $groupData["group_icon"] === null ? null : $itemDeserializerFunc($groupData["group_icon"]);
						$group = $icon === null ? null : new CreativeGroup($groupData["group_name"], $icon);

						foreach ($groupData["items"] as $itemData) {
							$item = $itemDeserializerFunc($itemData);
							if ($item->getName() === "Unknown") {
								continue;
							}

							$creativeItemEntry[$index++] = new CreativeInventoryEntry($item, $categoryId, $group ?? null);
						}
					}
				}
			} else {
				foreach (json_decode(Filesystem::resourceGetContents(BEDROCK_DATA_PATH . 'creative/' . $protocol . '/creative_items.json'), true) as $itemData) {
					$item = $itemDeserializerFunc($itemData);
					if ($item->getName() === "Unknown") {
						continue;
					}

					$creativeItemEntry[$index++] = new CreativeInventoryEntry($item, CreativeCategory::CONSTRUCTION, null);
				}
			}

			$creativeInventoryEntries[$protocol] = $creativeItemEntry;
		}

		krsort($creativeInventoryEntries);

		return new self($creativeInventoryEntries);
	}

	/** @var DataPacket[] */
	private array $caches = [];

	/**
	 * @param CreativeInventoryEntry[][] $items
	 */
	public function __construct(
		private array $items = []
	) {
	}

	public function getItems(int $protocolVersion) : array{
		foreach ($this->items as $protocol => $items) {
			if ($protocolVersion >= $protocol) {
				return $items;
			}
		}

		return [];
	}

	public function clearItems(?int $protocolVersion = null) : void{
		foreach ($this->items as $protocol => $items) {
			if ($protocolVersion === null || $protocolVersion >= $protocol) {
				unset($this->items[$protocol]);
			}
		}

		$this->clearCache($protocolVersion);
		krsort($this->items);
	}

	public function addItem(Item $item, int $categoryId = CreativeCategory::CONSTRUCTION, ?CreativeGroup $group = null, ?int $protocolVersion = null) : void{
		foreach ($this->items as $protocol => $items) {
			if ($protocolVersion === null || $protocolVersion >= $protocol) {
				$this->items[$protocol][] = new CreativeInventoryEntry($item, $categoryId, $group);
			}
		}

		$this->clearCache($protocolVersion);
		krsort($this->items);
	}

	public function removeItem(Item $item, ?int $protocolVersion = null) : void{
		foreach ($this->items as $protocol => $items) {
			if ($protocolVersion === null || $protocolVersion >= $protocol) {
				foreach ($items as $index => $itemEntry) {
					if ($item->equals($itemEntry->getItem(), !($item instanceof Durable))) {
						unset($this->items[$protocol][$index]);
					}
				}
			}
		}

		$this->clearCache($protocolVersion);
		krsort($this->items);
	}

	public function getItemIndex(Item $item, ?int $protocolVersion = null) : int {
		foreach ($this->items as $protocol => $items) {
			if ($protocolVersion === null || $protocolVersion >= $protocol) {
				foreach ($items as $index => $itemEntry) {
					if ($item->equals($itemEntry->getItem(), !($item instanceof Durable))) {
						return $index;
					}
				}
			}
		}

		return -1;
	}

	public function getItemFromIndex(int $index, ?int $protocolVersion = null) : ?Item{
		foreach ($this->items as $protocol => $items) {
			if ($protocolVersion === null || $protocolVersion >= $protocol) {
				if (isset($items[$index])) {
					return $items[$index]->getItem();
				}
			}
		}

		return null;
	}

	public function buildPacket(Player $player) : DataPacket{
		$protocolVersion = $player->getProtocolVersion();
		if (isset($this->caches[$protocolVersion])) {
			return $this->caches[$protocolVersion];
		}

		$itemEntries = $this->getItems($protocolVersion);

		$categories = [];
		$groups = [];

		$typeConverter = TypeConverter::getInstance();

		$nextIndex = 0;
		$groupIndexes = [];
		$itemGroupIndexes = [];

		foreach($itemEntries as $k => $entry){
			$group = $entry->getGroup();
			$categoryId = $entry->getCategoryId();
			if($group === null){
				$groupId = PHP_INT_MIN;
			}else{
				$groupId = spl_object_id($group);
				unset($groupIndexes[$categoryId][PHP_INT_MIN]); //start a new anonymous group for this category
			}

			//group object may be reused by multiple categories
			if(!isset($groupIndexes[$categoryId][$groupId])){
				$groupIndexes[$categoryId][$groupId] = $nextIndex++;
				$categories[] = $categoryId;
				$groups[] = $group;
			}
			$itemGroupIndexes[$k] = $groupIndexes[$categoryId][$groupId];
		}

		//creative inventory may have holes if items were unregistered - ensure network IDs used are always consistent
		$itemsEntries = [];
		foreach($itemEntries as $k => $entry){
			$itemsEntries[] = new CreativeItemEntry(
				$k,
				$typeConverter->coreItemStackToNet($entry->getItem(), $protocolVersion),
				$itemGroupIndexes[$k]
			);
		}

		$groupEntries = [];
		if ($protocolVersion >= ProtocolInfo::PROTOCOL_776) {
			foreach ($categories as $index => $categoryId) {
				$group = $groups[$index];
				if ($group === null) {
					$groupEntries[] = new CreativeGroupEntry($categoryId, "", ItemStack::null());
				} else {
					$groupIcon = $group->getIcon();
					//TODO: HACK! In 1.21.60, Workaround glitchy behaviour when an item is used as an icon for a group it
					//doesn't belong to. Without this hack, both instances of the item will show a +, but neither of them
					//will actually expand the group work correctly.
					$groupIcon->getNamedTag()->setInt("___GroupBugWorkaround___", $index);
					$groupEntries[] = new CreativeGroupEntry(
						$categoryId,
						$group->getName(),
						$typeConverter->coreItemStackToNet($groupIcon, $protocolVersion)
					);
				}
			}
		}

		if ($protocolVersion >= ProtocolInfo::PROTOCOL_407) {
			$packet = CreativeContentPacket::create($groupEntries, $itemsEntries)->enableEncodedCache();
		} else {
			$items = [];
			foreach ($itemsEntries as $entry) {
				$items[] = $entry->getItem();
			}

			$packet = ContainerSetContentPacket::create(ContainerIds::CREATIVE, $player->getId(), $items, []);
		}

		return $this->caches[$protocolVersion] = $packet;
	}

	public function clearCache(?int $protocolVersion = null) : void{
		if ($protocolVersion === null) {
			$this->caches = [];
		} else {
			foreach ($this->caches as $protocol => $_) {
				if ($protocol >= $protocolVersion) {
					unset($this->caches[$protocol]);
				}
			}
		}
	}
}
