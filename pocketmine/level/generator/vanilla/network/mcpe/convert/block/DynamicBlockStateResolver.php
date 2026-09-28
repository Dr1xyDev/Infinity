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

namespace pocketmine\level\generator\vanilla\network\mcpe\convert\block;

use pocketmine\level\generator\vanilla\block\Block;
use pocketmine\level\generator\vanilla\block\BlockFactory;
use pocketmine\level\generator\vanilla\level\format\Chunk;
use pocketmine\level\generator\vanilla\level\Level;
use pocketmine\level\generator\vanilla\level\Position;
use pocketmine\level\generator\vanilla\nbt\tag\CompoundTag;
use pocketmine\level\generator\vanilla\nbt\tag\IntTag;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ProtocolInfo;
use pocketmine\level\generator\vanilla\tile\Skull as TileSkull;
use function str_contains;
use function str_ends_with;

/**
 * Network block states which legacy id:meta can't express, computed per position when chunks and block updates are
 * sent:
 *  - 1.21.40+: every mob head type is a separate block, while legacy skulls keep the type in their tile
 *  - 1.26.50+: the client no longer works out fence/pane/bars/tripwire connections and stair corners by itself, they
 *    are block state properties ("minecraft:connection_*", "minecraft:corner") computed from the neighbouring blocks
 *    (vanilla rules)
 */
final class DynamicBlockStateResolver
{
	public const MIN_PROTOCOL_SKULL_TYPES = ProtocolInfo::PROTOCOL_748;
	public const MIN_PROTOCOL_CONNECTIONS = ProtocolInfo::PROTOCOL_2193;

	/** Network block names of the mob heads, indexed by tile\Skull::TYPE_* */
	private const SKULL_BLOCK_NAMES = [
		TileSkull::TYPE_SKELETON => "minecraft:skeleton_skull",
		TileSkull::TYPE_WITHER_SKELETON => "minecraft:wither_skeleton_skull",
		TileSkull::TYPE_ZOMBIE => "minecraft:zombie_head",
		TileSkull::TYPE_PLAYER => "minecraft:player_head",
		TileSkull::TYPE_CREEPER => "minecraft:creeper_head",
		TileSkull::TYPE_DRAGON => "minecraft:dragon_head",
		TileSkull::TYPE_PIGLIN => "minecraft:piglin_head",
	];

	private const KIND_SKULL = 9;
	private const KIND_NONE = 0;
	private const KIND_WOODEN_FENCE = 1;
	private const KIND_NETHER_FENCE = 2;
	private const KIND_PANE = 3;
	private const KIND_TRIPWIRE = 4;
	private const KIND_STAIRS = 5;
	//only relevant as neighbours
	private const KIND_FENCE_GATE = 6;
	private const KIND_WALL = 7;
	private const KIND_TRIPWIRE_HOOK = 8;

	private const DIRECTIONS = [
		//name, dx, dz
		["east", 1, 0],
		["north", 0, -1],
		["south", 0, 1],
		["west", -1, 0],
	];

	/** Marker for palette entries which already are network runtime IDs, see ChunkSerializer */
	public const RUNTIME_ID_MARKER = 0x40000000;
	public const RUNTIME_ID_MASK = 0x3fffffff;

	/** @var self[] */
	private static array $instances = [];

	/** @var int[] fullState => kind */
	private array $kinds = [];
	/** @var bool[] fullState => sturdy full-cube */
	private array $sturdy = [];
	/** @var int[] fullState => fullState for this protocol */
	private array $converted = [];
	/** @var int[][] skull type => facing => runtime ID */
	private array $skullRuntimeIds = [];

	public static function isSupported(int $protocol) : bool
	{
		return $protocol >= self::MIN_PROTOCOL_SKULL_TYPES;
	}

	public static function getInstance(int $protocol) : self
	{
		return self::$instances[$protocol] ??= new self($protocol, RuntimeBlockMapping::getInstance($protocol));
	}

	private function __construct(
		private int $protocol,
		private RuntimeBlockMapping $mapping
	) {
	}

	/**
	 * Applies BlockProtocolConvertor, like the chunk serializer does
	 */
	public function convert(int $fullState) : int
	{
		if (!isset($this->converted[$fullState])) {
			$block = BlockFactory::fromFullBlock($fullState);
			$this->converted[$fullState] = (BlockProtocolConvertor::getInstance()->get($block, $this->protocol) ?? $block)->getFullId();
		}
		return $this->converted[$fullState];
	}

	private function getKind(int $fullState) : int
	{
		if (isset($this->kinds[$fullState])) {
			return $this->kinds[$fullState];
		}

		if (($fullState >> Block::INTERNAL_METADATA_BITS) === Block::SKULL_BLOCK) {
			return $this->kinds[$fullState] = self::KIND_SKULL;
		}

		$name = $this->mapping->toName($this->mapping->toRuntimeId($fullState));
		$kind = match (true) {
			$name === "minecraft:nether_brick_fence" => self::KIND_NETHER_FENCE,
			str_ends_with($name, "_fence") => self::KIND_WOODEN_FENCE,
			str_contains($name, "glass_pane"), str_ends_with($name, "_bars") => self::KIND_PANE,
			$name === "minecraft:trip_wire" => self::KIND_TRIPWIRE,
			str_ends_with($name, "_stairs") => self::KIND_STAIRS,
			$name === "minecraft:fence_gate", str_ends_with($name, "_fence_gate") => self::KIND_FENCE_GATE,
			str_ends_with($name, "_wall") => self::KIND_WALL,
			$name === "minecraft:tripwire_hook" => self::KIND_TRIPWIRE_HOOK,
			default => self::KIND_NONE
		};
		return $this->kinds[$fullState] = $kind;
	}

	/**
	 * Whether the network state of the (already converted) block depends on its position (neighbours or tile)
	 */
	public function isDynamic(int $fullState) : bool
	{
		$kind = $this->getKind($fullState);
		if ($kind === self::KIND_SKULL) {
			return true;
		}
		return $this->protocol >= self::MIN_PROTOCOL_CONNECTIONS && $kind >= self::KIND_WOODEN_FENCE && $kind <= self::KIND_STAIRS;
	}

	private function getSkullRuntimeId(int $type, int $facing) : ?int
	{
		if (!isset($this->skullRuntimeIds[$type][$facing])) {
			$name = self::SKULL_BLOCK_NAMES[$type] ?? null;
			if ($name === null) {
				return null;
			}
			$nbt = new CompoundTag();
			$nbt->setString("name", $name);
			$nbt->setTag(new CompoundTag("states", [new IntTag("facing_direction", $facing)]));
			$runtimeId = $this->mapping->fromNbtBlock($nbt);
			$this->skullRuntimeIds[$type][$facing] = $this->mapping->toName($runtimeId) === $name ? $runtimeId : -1;
		}
		$runtimeId = $this->skullRuntimeIds[$type][$facing];
		return $runtimeId >= 0 ? $runtimeId : null;
	}

	/**
	 * Full, sturdy cubes which fences and panes attach to (vanilla excludes a few full blocks)
	 */
	private function isSturdy(int $fullState) : bool
	{
		if (isset($this->sturdy[$fullState])) {
			return $this->sturdy[$fullState];
		}

		$sturdy = false;
		$name = $this->mapping->toName($this->mapping->toRuntimeId($fullState));
		if (
			($fullState >> Block::INTERNAL_METADATA_BITS) !== Block::AIR &&
			!str_contains($name, "leaves") && !str_contains($name, "pumpkin") && !str_contains($name, "melon") &&
			!str_contains($name, "shulker_box") && $name !== "minecraft:barrier"
		) {
			try {
				$block = BlockFactory::fromFullBlock($fullState);
				if ($block->isSolid()) {
					$block->position(new Position(0, 0, 0, null));
					$bb = $block->getBoundingBox();
					$sturdy = $bb !== null &&
						$bb->minX <= 0.0001 && $bb->minY <= 0.0001 && $bb->minZ <= 0.0001 &&
						$bb->maxX >= 0.9999 && $bb->maxY >= 0.9999 && $bb->maxZ >= 0.9999;
				}
			} catch (\Throwable) {
				//some blocks look at their neighbours to compute their shape, they aren't full cubes anyway
				$sturdy = false;
			}
		}
		return $this->sturdy[$fullState] = $sturdy;
	}

	/**
	 * @param \Closure      $getFullBlock (int $x, int $y, int $z) : int, returns the raw full state at world coordinates
	 * @param \Closure|null $getSkullType (int $x, int $y, int $z) : ?int, returns the type of the skull tile there
	 * @phpstan-param \Closure(int, int, int) : int $getFullBlock
	 * @phpstan-param (\Closure(int, int, int) : ?int)|null $getSkullType
	 *
	 * @return int|null the network runtime ID of the position-dependent state, null if the plain mapping applies
	 */
	public function resolve(int $fullState, int $x, int $y, int $z, \Closure $getFullBlock, ?\Closure $getSkullType = null) : ?int
	{
		$kind = $this->getKind($fullState);
		if ($kind === self::KIND_SKULL) {
			$type = $getSkullType !== null ? $getSkullType($x, $y, $z) : null;
			return $type === null ? null : $this->getSkullRuntimeId($type, $fullState & 0x07);
		}
		if ($this->protocol < self::MIN_PROTOCOL_CONNECTIONS) {
			return null;
		}

		$states = [];
		switch ($kind) {
			case self::KIND_WOODEN_FENCE:
			case self::KIND_NETHER_FENCE:
			case self::KIND_PANE:
			case self::KIND_TRIPWIRE:
				foreach (self::DIRECTIONS as [$direction, $dx, $dz]) {
					$neighbour = $this->convert($getFullBlock($x + $dx, $y, $z + $dz));
					$states["minecraft:connection_" . $direction] = $this->connects($kind, $neighbour, $dx, $dz) ? 1 : 0;
				}
				break;
			case self::KIND_STAIRS:
				$states["minecraft:corner"] = $this->getStairShape($fullState, $x, $y, $z, $getFullBlock);
				break;
			default:
				return null;
		}

		return $this->mapping->getDynamicStateVariant($this->mapping->toRuntimeId($fullState), $states);
	}

	private function connects(int $kind, int $neighbour, int $dx, int $dz) : bool
	{
		$neighbourKind = $this->getKind($neighbour);
		if ($kind === self::KIND_TRIPWIRE) {
			if ($neighbourKind === self::KIND_TRIPWIRE) {
				return true;
			}
			//hooks attach when they face the wire: 0 south, 1 west, 2 north, 3 east
			return $neighbourKind === self::KIND_TRIPWIRE_HOOK && ($neighbour & 0x03) === match (true) {
				$dx === 1 => 1,
				$dx === -1 => 3,
				$dz === 1 => 2,
				default => 0
			};
		}

		if ($kind === self::KIND_PANE) {
			return $neighbourKind === self::KIND_PANE || $neighbourKind === self::KIND_WALL || $this->isSturdy($neighbour);
		}

		//fences: same family, gates in line with the fence, or sturdy blocks
		if ($neighbourKind === $kind) {
			return true;
		}
		if ($neighbourKind === self::KIND_FENCE_GATE) {
			//gate direction (0 south, 1 west, 2 north, 3 east) must be perpendicular to the connection
			$gateAlongZ = ($neighbour & 0x01) === 0;
			return $dx !== 0 ? $gateAlongZ : !$gateAlongZ;
		}
		return $this->isSturdy($neighbour);
	}

	/**
	 * Vanilla stair shape algorithm. Legacy stairs meta: bits 0-1 weirdo_direction (0 east, 1 west, 2 south, 3 north,
	 * the side the stairs ascend to), bit 2 upside down.
	 *
	 * @phpstan-param \Closure(int, int, int) : int $getFullBlock
	 */
	private function getStairShape(int $fullState, int $x, int $y, int $z, \Closure $getFullBlock) : string
	{
		$facing = $fullState & 0x03;
		$half = $fullState & 0x04;

		[$fx, $fz] = self::stairVector($facing);
		$front = $this->convert($getFullBlock($x + $fx, $y, $z + $fz));
		if ($this->getKind($front) === self::KIND_STAIRS && ($front & 0x04) === $half) {
			$frontFacing = $front & 0x03;
			if (self::stairAxis($frontFacing) !== self::stairAxis($facing) && $this->canTakeShape($facing, $half, self::stairOpposite($frontFacing), $x, $y, $z, $getFullBlock)) {
				return $frontFacing === self::stairCounterClockwise($facing) ? "outer_left" : "outer_right";
			}
		}

		$back = $this->convert($getFullBlock($x - $fx, $y, $z - $fz));
		if ($this->getKind($back) === self::KIND_STAIRS && ($back & 0x04) === $half) {
			$backFacing = $back & 0x03;
			if (self::stairAxis($backFacing) !== self::stairAxis($facing) && $this->canTakeShape($facing, $half, $backFacing, $x, $y, $z, $getFullBlock)) {
				return $backFacing === self::stairCounterClockwise($facing) ? "inner_left" : "inner_right";
			}
		}

		return "none";
	}

	/**
	 * @phpstan-param \Closure(int, int, int) : int $getFullBlock
	 */
	private function canTakeShape(int $facing, int $half, int $direction, int $x, int $y, int $z, \Closure $getFullBlock) : bool
	{
		[$dx, $dz] = self::stairVector($direction);
		$other = $this->convert($getFullBlock($x + $dx, $y, $z + $dz));
		return $this->getKind($other) !== self::KIND_STAIRS || ($other & 0x03) !== $facing || ($other & 0x04) !== $half;
	}

	/** @return int[] */
	private static function stairVector(int $direction) : array
	{
		return match ($direction) {
			0 => [1, 0], //east
			1 => [-1, 0], //west
			2 => [0, 1], //south
			default => [0, -1] //north
		};
	}

	private static function stairAxis(int $direction) : int
	{
		return $direction <= 1 ? 0 : 1;
	}

	private static function stairOpposite(int $direction) : int
	{
		return $direction ^ 0x01;
	}

	private static function stairCounterClockwise(int $direction) : int
	{
		//north -> west -> south -> east -> north
		return match ($direction) {
			3 => 1,
			1 => 2,
			2 => 0,
			default => 3
		};
	}

	/**
	 * Computes the neighbour-dependent states of every such block of a chunk.
	 *
	 * @return int[] (subChunkIndex << 12 | x << 8 | z << 4 | y) => network runtime ID
	 */
	public function computeChunk(Level $level, Chunk $chunk) : array
	{
		$result = [];
		$baseX = $chunk->getX() << Chunk::COORD_BIT_SIZE;
		$baseZ = $chunk->getZ() << Chunk::COORD_BIT_SIZE;
		$getFullBlock = static function (int $x, int $y, int $z) use ($level, $chunk, $baseX, $baseZ) : int {
			$lx = $x - $baseX;
			$lz = $z - $baseZ;
			if ($lx >= 0 && $lx < 16 && $lz >= 0 && $lz < 16) {
				return $chunk->getFullBlock($lx, $y, $lz);
			}
			//only look at chunks which are already loaded, this must never load or generate anything
			$cx = $x >> Chunk::COORD_BIT_SIZE;
			$cz = $z >> Chunk::COORD_BIT_SIZE;
			if (!$level->isChunkLoaded($cx, $cz)) {
				return Block::AIR << Block::INTERNAL_METADATA_BITS;
			}
			return $level->getChunk($cx, $cz)->getFullBlock($x & Chunk::COORD_MASK, $y, $z & Chunk::COORD_MASK);
		};

		//mob heads: their type is only known from the tile
		foreach ($chunk->getTiles() as $tile) {
			if (!($tile instanceof TileSkull)) {
				continue;
			}
			$x = $tile->getFloorX();
			$y = $tile->getFloorY();
			$z = $tile->getFloorZ();
			$fullState = $this->convert($chunk->getFullBlock($x & Chunk::COORD_MASK, $y, $z & Chunk::COORD_MASK));
			if ($this->getKind($fullState) !== self::KIND_SKULL) {
				continue;
			}
			$runtimeId = $this->getSkullRuntimeId($tile->getType(), $fullState & 0x07);
			if ($runtimeId !== null) {
				$result[(($y >> 4) << 12) | (($x & 0x0f) << 8) | (($z & 0x0f) << 4) | ($y & 0x0f)] = $runtimeId;
			}
		}

		if ($this->protocol < self::MIN_PROTOCOL_CONNECTIONS) {
			return $result;
		}

		for ($subY = 0, $height = $chunk->getHeight(); $subY < $height; ++$subY) {
			$layers = $chunk->getSubChunk($subY)->getBlockLayers();
			if (!isset($layers[0])) {
				continue;
			}
			$layer = $layers[0];

			$dynamic = false;
			foreach ($layer->getPalette() as $fullState) {
				if ($this->dependsOnNeighbours($this->convert($fullState))) {
					$dynamic = true;
					break;
				}
			}
			if (!$dynamic) {
				continue;
			}

			for ($x = 0; $x < 16; ++$x) {
				for ($z = 0; $z < 16; ++$z) {
					for ($y = 0; $y < 16; ++$y) {
						$fullState = $this->convert($layer->get($x, $y, $z));
						if (!$this->dependsOnNeighbours($fullState)) {
							continue;
						}
						$runtimeId = $this->resolve($fullState, $baseX + $x, ($subY << 4) + $y, $baseZ + $z, $getFullBlock);
						if ($runtimeId !== null) {
							$result[($subY << 12) | ($x << 8) | ($z << 4) | $y] = $runtimeId;
						}
					}
				}
			}
		}

		return $result;
	}

	/**
	 * Whether the (already converted) block state depends on the neighbouring blocks for this protocol
	 */
	public function dependsOnNeighbours(int $fullState) : bool
	{
		if ($this->protocol < self::MIN_PROTOCOL_CONNECTIONS) {
			return false;
		}
		$kind = $this->getKind($fullState);
		return $kind >= self::KIND_WOODEN_FENCE && $kind <= self::KIND_STAIRS;
	}
}
