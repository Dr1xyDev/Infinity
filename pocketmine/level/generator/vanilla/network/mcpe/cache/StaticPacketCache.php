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

use pocketmine\level\generator\vanilla\nbt\NetworkLittleEndianNBTStream;
use pocketmine\level\generator\vanilla\nbt\tag\CompoundTag;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\AvailableActorIdentifiersPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\BiomeDefinitionListPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\JigsawStructureDataPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ProtocolInfo;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\TrimDataPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\biome\BiomeDefinitionEntry;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\BlockPaletteEntry;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\SerializableVoxelCells;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\SerializableVoxelShape;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\TrimMaterial;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\TrimPattern;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\VoxelShapesPacket;
use pocketmine\level\generator\vanilla\utils\AssumptionFailedError;
use pocketmine\level\generator\vanilla\utils\Color;
use pocketmine\level\generator\vanilla\utils\Filesystem;
use pocketmine\level\generator\vanilla\utils\SingletonTrait;

use function array_diff;
use function array_map;
use function count;
use function json_decode;
use function krsort;
use function scandir;
use const JSON_THROW_ON_ERROR;

use const pocketmine\BEDROCK_DATA_PATH;

class StaticPacketCache
{
	use SingletonTrait;

	private static function make() : self
	{
		$biomeDefs = [];
		foreach (array_diff(scandir($biomeDefsDirectory = BEDROCK_DATA_PATH . 'biomes/'), ["..", "."]) as $protocol) {
			if ($protocol >= ProtocolInfo::PROTOCOL_800) {
				$biomeEntries = json_decode(Filesystem::resourceGetContents($biomeDefsDirectory . $protocol . '/biome_definitions.json'), true);
				$entries = [];
				foreach ($biomeEntries as $name => $entry) {
					$entries[] = new BiomeDefinitionEntry(
						$name,
						$entry["id"],
						$entry["temperature"],
						$entry["downfall"],
						$entry["foliageSnow"] ?? 0,
						$entry["redSporeDensity"] ?? 0,
						$entry["blueSporeDensity"] ?? 0,
						$entry["ashDensity"] ?? 0,
						$entry["whiteAshDensity"] ?? 0,
						$entry["depth"],
						$entry["scale"],
						new Color(
							$entry["mapWaterColour"]["r"],
							$entry["mapWaterColour"]["g"],
							$entry["mapWaterColour"]["b"],
							$entry["mapWaterColour"]["a"]
						),
						$entry["rain"],
						count($entry["tags"]) > 0 ? $entry["tags"] : null,
					);
				}

				$biomeDefs[$protocol] = BiomeDefinitionListPacket::create("", $entries)->enableEncodedCache();
			} else {
				$biomeDefs[$protocol] = BiomeDefinitionListPacket::create(Filesystem::resourceGetContents($biomeDefsDirectory . $protocol . '/biome_definitions.nbt'), [])->enableEncodedCache();
			}
		}
		krsort($biomeDefs);

		$actorIds = [];
		foreach (array_diff(scandir($actorIdsDirectory = BEDROCK_DATA_PATH . 'entity/'), ["..", "."]) as $protocol) {
			$actorIds[$protocol] = AvailableActorIdentifiersPacket::create(Filesystem::resourceGetContents($actorIdsDirectory . $protocol . '/entity_identifiers.nbt'))->enableEncodedCache();
		}
		krsort($actorIds);

		$trimDataPacket = TrimDataPacket::create([
			new TrimPattern("minecraft:ward_armor_trim_smithing_template", "ward"),
			new TrimPattern("minecraft:sentry_armor_trim_smithing_template", "sentry"),
			new TrimPattern("minecraft:snout_armor_trim_smithing_template", "snout"),
			new TrimPattern("minecraft:dune_armor_trim_smithing_template", "dune"),
			new TrimPattern("minecraft:spire_armor_trim_smithing_template", "spire"),
			new TrimPattern("minecraft:tide_armor_trim_smithing_template", "tide"),
			new TrimPattern("minecraft:wild_armor_trim_smithing_template", "wild"),
			new TrimPattern("minecraft:rib_armor_trim_smithing_template", "rib"),
			new TrimPattern("minecraft:coast_armor_trim_smithing_template", "coast"),
			new TrimPattern("minecraft:shaper_armor_trim_smithing_template", "shaper"),
			new TrimPattern("minecraft:eye_armor_trim_smithing_template", "eye"),
			new TrimPattern("minecraft:vex_armor_trim_smithing_template", "vex"),
			new TrimPattern("minecraft:silence_armor_trim_smithing_template", "silence"),
			new TrimPattern("minecraft:wayfinder_armor_trim_smithing_template", "wayfinder"),
			new TrimPattern("minecraft:raiser_armor_trim_smithing_template", "raiser"),
			new TrimPattern("minecraft:host_armor_trim_smithing_template", "host"),
			new TrimPattern("minecraft:bolt_armor_trim_smithing_template", "bolt"),
			new TrimPattern("minecraft:flow_armor_trim_smithing_template", "flow"),
		], [
			new TrimMaterial("quartz", "§h", "minecraft:quartz"),
			new TrimMaterial("iron", "§i", "minecraft:iron_ingot"),
			new TrimMaterial("netherite", "§j", "minecraft:netherite_ingot"),
			new TrimMaterial("redstone", "§m", "minecraft:redstone"),
			new TrimMaterial("copper", "§n", "minecraft:copper_ingot"),
			new TrimMaterial("gold", "§p", "minecraft:gold_ingot"),
			new TrimMaterial("emerald", "§q", "minecraft:emerald"),
			new TrimMaterial("diamond", "§s", "minecraft:diamond"),
			new TrimMaterial("lapis", "§t", "minecraft:lapis_lazuli"),
			new TrimMaterial("amethyst", "§u", "minecraft:amethyst_shard"),
		])->enableEncodedCache();

		//data the client needs before StartGame since 1.26.50: block collision shapes, jigsaw structures and the
		//definitions of vanilla data-driven blocks (without them the client can't build its block registry and leaves)
		$voxelShapes = [];
		$jigsawStructures = [];
		$dataDrivenBlocks = [];
		foreach (array_diff(scandir($spawnDataDirectory = BEDROCK_DATA_PATH . 'spawn_data/'), ["..", "."]) as $protocol) {
			$directory = $spawnDataDirectory . $protocol . '/';
			if (Filesystem::resourceExists($directory . 'voxel_shapes.json')) {
				$voxelShapes[(int) $protocol] = self::loadVoxelShapes($directory . 'voxel_shapes.json')->enableEncodedCache();
			}
			if (Filesystem::resourceExists($directory . 'jigsaw_structures_data.nbt')) {
				$jigsawStructures[(int) $protocol] = JigsawStructureDataPacket::create(self::loadNetworkNbt($directory . 'jigsaw_structures_data.nbt'))->enableEncodedCache();
			}
			if (Filesystem::resourceExists($directory . 'data_driven_blocks.nbt')) {
				$dataDrivenBlocks[(int) $protocol] = self::loadDataDrivenBlocks($directory . 'data_driven_blocks.nbt');
			}
		}
		krsort($voxelShapes);
		krsort($jigsawStructures);
		krsort($dataDrivenBlocks);

		return new self(
			$biomeDefs,
			$actorIds,
			$trimDataPacket,
			$voxelShapes,
			$jigsawStructures,
			$dataDrivenBlocks
		);
	}

	private static function loadNetworkNbt(string $file) : CompoundTag
	{
		$root = (new NetworkLittleEndianNBTStream())->read(Filesystem::resourceGetContents($file));
		if (!($root instanceof CompoundTag)) {
			throw new AssumptionFailedError("$file should contain a CompoundTag");
		}
		return $root;
	}

	private static function loadVoxelShapes(string $file) : VoxelShapesPacket
	{
		$data = json_decode(Filesystem::resourceGetContents($file), true, 512, JSON_THROW_ON_ERROR);
		$shapes = [];
		foreach ($data["shapes"] as $shape) {
			$cells = $shape["cells"];
			$shapes[] = new SerializableVoxelShape(
				[new SerializableVoxelCells($cells["xSize"], $cells["ySize"], $cells["zSize"], $cells["storage"])],
				array_map('floatval', $shape["x"]),
				array_map('floatval', $shape["y"]),
				array_map('floatval', $shape["z"])
			);
		}
		$nameMap = [];
		foreach ($data["nameMap"] as $name => $id) {
			$nameMap[(string) $name] = (int) $id;
		}
		return VoxelShapesPacket::create($shapes, $nameMap, 0);
	}

	/**
	 * @return BlockPaletteEntry[]
	 */
	private static function loadDataDrivenBlocks(string $file) : array
	{
		$palette = self::loadNetworkNbt($file)->getListTag("blockPalette");
		if ($palette === null) {
			throw new AssumptionFailedError("$file should contain a blockPalette list");
		}
		$entries = [];
		foreach ($palette->getValue() as $entry) {
			if ($entry instanceof CompoundTag && ($states = $entry->getCompoundTag("states")) !== null) {
				//the definition is sent as a nameless root tag, like the vanilla server does
				$states = clone $states;
				$states->setName("");
				$entries[] = new BlockPaletteEntry($entry->getString("name"), $states);
			}
		}
		return $entries;
	}

	/**
	 * @param BiomeDefinitionListPacket[]       $biomeDefs
	 * @param AvailableActorIdentifiersPacket[] $availableActorIdentifiers
	 * @param VoxelShapesPacket[]               $voxelShapes      protocol => packet
	 * @param JigsawStructureDataPacket[]       $jigsawStructures protocol => packet
	 * @param BlockPaletteEntry[][]             $dataDrivenBlocks protocol => entries
	 */
	public function __construct(
		private array $biomeDefs,
		private array $availableActorIdentifiers,
		private TrimDataPacket $trimDataPacket,
		private array $voxelShapes = [],
		private array $jigsawStructures = [],
		private array $dataDrivenBlocks = []
	) {
	}

	/**
	 * Block collision shapes (1.26.50+), an empty packet for versions without the data
	 */
	public function getVoxelShapes(int $protocolVersion) : VoxelShapesPacket
	{
		foreach ($this->voxelShapes as $protocol => $packet) {
			if ($protocolVersion >= $protocol) {
				return $packet;
			}
		}
		return VoxelShapesPacket::create([], [], 0);
	}

	/**
	 * Jigsaw structure data (1.26.50+), null for versions which don't get it
	 */
	public function getJigsawStructureData(int $protocolVersion) : ?JigsawStructureDataPacket
	{
		foreach ($this->jigsawStructures as $protocol => $packet) {
			if ($protocolVersion >= $protocol) {
				return $packet;
			}
		}
		return null;
	}

	/**
	 * Vanilla data-driven blocks which have to be declared in StartGame (1.26.50+)
	 *
	 * @return BlockPaletteEntry[]
	 */
	public function getDataDrivenBlockPalette(int $protocolVersion) : array
	{
		foreach ($this->dataDrivenBlocks as $protocol => $entries) {
			if ($protocolVersion >= $protocol) {
				return $entries;
			}
		}
		return [];
	}

	public function getBiomeDefs(int $protocolVersion) : BiomeDefinitionListPacket
	{
		foreach ($this->biomeDefs as $protocol => $cache) {
			if ($protocolVersion >= $protocol) {
				return $cache;
			}
		}

		return BiomeDefinitionListPacket::create("", []);
	}

	public function getAvailableActorIdentifiers(int $protocolVersion) : AvailableActorIdentifiersPacket
	{
		foreach ($this->availableActorIdentifiers as $protocol => $cache) {
			if ($protocolVersion >= $protocol) {
				return $cache;
			}
		}

		return AvailableActorIdentifiersPacket::create("");
	}

	public function getTrimDataPacket() : TrimDataPacket
	{
		return $this->trimDataPacket;
	}
}
