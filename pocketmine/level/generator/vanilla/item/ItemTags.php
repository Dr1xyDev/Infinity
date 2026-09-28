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

namespace pocketmine\level\generator\vanilla\item;

use pocketmine\level\generator\vanilla\utils\AssumptionFailedError;
use pocketmine\level\generator\vanilla\utils\Filesystem;
use pocketmine\level\generator\vanilla\utils\SingletonTrait;
use pocketmine\level\generator\vanilla\utils\Utils;
use function array_keys;
use function gettype;
use function is_array;
use function is_int;
use function is_string;
use function json_decode;
use const JSON_THROW_ON_ERROR;
use const pocketmine\BEDROCK_DATA_PATH;

/**
 * Tracks Minecraft Bedrock item tags, and the item IDs which belong to them
 *
 * @internal
 */
final class ItemTags{
	use SingletonTrait;

	private static function make() : self{
		$map = json_decode(Filesystem::resourceGetContents(BEDROCK_DATA_PATH . "items/item_tags.json"), true, flags: JSON_THROW_ON_ERROR);
		if(!is_array($map)){
			throw new AssumptionFailedError("Invalid item tag map, expected array");
		}
		$tagToIds = [];
		foreach($map["simple"] as $tagName => $ids){
			if(!is_string($tagName)){
				throw new AssumptionFailedError("Invalid item tag name $tagName, expected string as key");
			}
			if(!is_array($ids)){
				throw new AssumptionFailedError("Invalid item tag $tagName, expected array of IDs as value");
			}
			$idsMap = [];
			foreach($ids as $id){
				if(!is_int($id)){
					throw new AssumptionFailedError("Invalid item tag $tagName, expected int as ID, got " . gettype($id));
				}
				$idsMap[] = $id;
			}
			$tagToIds[$tagName] = $idsMap;
		}

		$tagToMetaIds = [];
		foreach($map["complex"] as $tagName => $metaIds){
			if(!is_string($tagName)){
				throw new AssumptionFailedError("Invalid item tag name $tagName, expected string as key");
			}
			$metaIdsMap = [];
			foreach($metaIds as [$id, $meta]){
				if(!is_int($id)){
					throw new AssumptionFailedError("Invalid item tag $tagName, expected int as ID, got " . gettype($id));
				}
				$metaIdsMap[$id][] = $meta;
			}
			$tagToMetaIds[$tagName] = $metaIdsMap;
		}

		return new self($tagToIds, $tagToMetaIds);
	}

	/**
	 * @var true[][]
	 * @phpstan-var array<string, array<int, true>>
	 */
	private array $tagToIdsMap = [];
	/**
	 * @var true[][][]
	 * @phpstan-var array<string, array<int, array<int, true>>>
	 */
	private array $tagToMetaIdsMap = [];

	/**
	 * @param string[][] $tagToIds
	 * @phpstan-param array<string, list<string>> $tagToMetaIds
	 */
	public function __construct(
		array $tagToIds,
		array $tagToMetaIds
	){
		foreach(Utils::stringifyKeys($tagToIds) as $tag => $ids){
			foreach($ids as $id){
				$this->tagToIdsMap[$tag][$id] = true;
			}
		}

		foreach(Utils::stringifyKeys($tagToMetaIds) as $tag => $metaIds){
			foreach($metaIds as $id => $metas){
				foreach ($metas as $meta) {
					$this->tagToMetaIdsMap[$tag][$id][$meta] = true;
				}
			}
		}
	}

	/**
	 * @return int[]
	 * @phpstan-return list<int>
	 */
	public function getIdsForTag(string $tag) : array{
		return array_keys($this->tagToIdsMap[$tag] ?? []);
	}

	/**
	 * @return int[]
	 * @phpstan-return list<int, list<int, true>>
	 */
	public function getMetaIdsForTag(string $tag) : array{
		return array_keys($this->tagToMetaIdsMap[$tag] ?? []);
	}

	public function tagContainsId(string $tag, int $id, int $meta) : bool{
		return isset($this->tagToIdsMap[$tag][$id]) || isset($this->tagToMetaIdsMap[$tag][$id][$meta]);
	}

	public function addIdToTag(string $tag, int $id, ?int $meta = null) : void{
		if ($meta === null) {
			$this->tagToIdsMap[$tag][$id] = true;
		} else {
			$this->tagToMetaIdsMap[$tag][$id][$meta] = true;
		}
	}
}
