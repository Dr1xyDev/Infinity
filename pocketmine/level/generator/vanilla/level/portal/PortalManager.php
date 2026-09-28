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

namespace pocketmine\level\generator\vanilla\level\portal;

use pocketmine\level\generator\vanilla\block\Block;
use pocketmine\level\generator\vanilla\block\BlockFactory;
use pocketmine\level\generator\vanilla\block\BlockIds;
use pocketmine\level\generator\vanilla\block\Liquid;
use pocketmine\level\generator\vanilla\entity\Entity;
use pocketmine\level\generator\vanilla\level\format\Chunk;
use pocketmine\level\generator\vanilla\level\Level;
use pocketmine\level\generator\vanilla\level\Position;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\DimensionIds;
use pocketmine\Player;
use pocketmine\Server;
use function abs;
use function floor;
use function max;
use function min;
use const PHP_INT_MAX;

/**
 * Links nether portals between the overworld and the nether (coordinates are scaled 1:8),
 * builds the arrival platform of the end and sends entities leaving the end back to their spawn.
 */
final class PortalManager
{
	public const DEFAULT_SEARCH_RADIUS_OVERWORLD = 128;
	public const DEFAULT_SEARCH_RADIUS_NETHER = 16;
	public const DEFAULT_CREATE_RADIUS = 16;

	/** Blocks between the nether roof and the top of a created portal */
	private const NETHER_ROOF_Y = 122;
	/** Lowest height a portal is forced to when no place was found (vanilla behaviour) */
	private const FORCED_MIN_Y_OVERWORLD = 70;
	private const FORCED_MIN_Y_NETHER = 32;

	/** @var bool[] full block => whether a portal may replace it */
	private static array $replaceableCache = [];
	/** @var bool[] full block => whether a portal can stand on it */
	private static array $groundCache = [];

	private function __construct()
	{
		//NOOP
	}

	public static function getDimensionLevel(Server $server, int $dimension) : ?Level
	{
		$level = match ($dimension) {
			DimensionIds::NETHER => $server->getNetherLevel(),
			DimensionIds::THE_END => $server->getTheEndLevel(),
			default => $server->getDefaultLevel(),
		};
		return $level !== null && !$level->isClosed() ? $level : null;
	}

	/**
	 * Factor to multiply horizontal coordinates with when travelling between the dimensions
	 */
	public static function getCoordinateScale(int $fromDimension, int $toDimension) : float
	{
		return ($fromDimension === DimensionIds::NETHER ? 8 : 1) / ($toDimension === DimensionIds::NETHER ? 8 : 1);
	}

	/**
	 * Radius searched for an existing portal around the scaled position in the target world
	 */
	public static function getSearchRadius(Level $target) : int
	{
		$server = $target->getServer();
		if ($target->getDimension() === DimensionIds::NETHER) {
			return max(0, (int) $server->getSubmarineProperty("dimensions.nether.portal-search-radius-nether", self::DEFAULT_SEARCH_RADIUS_NETHER));
		}
		return max(0, (int) $server->getSubmarineProperty("dimensions.nether.portal-search-radius-overworld", self::DEFAULT_SEARCH_RADIUS_OVERWORLD));
	}

	/**
	 * Radius searched for a free place when a new portal has to be built
	 */
	public static function getCreateRadius() : int
	{
		return max(0, (int) Server::getInstance()->getSubmarineProperty("dimensions.nether.portal-create-radius", self::DEFAULT_CREATE_RADIUS));
	}

	/**
	 * Starts moving an entity to another dimension. Returns null if the dimension is not available.
	 */
	public static function startTravel(Entity $entity, int $dimension) : ?PortalTravel
	{
		$source = $entity->getLevel();
		$target = self::getDimensionLevel(Server::getInstance(), $dimension);
		if ($source === null || $target === null || $target === $source) {
			return null;
		}

		if ($dimension === DimensionIds::THE_END) {
			return new PortalTravel(PortalTravel::MODE_END_SPAWN, $target, new Vector3(Level::END_SPAWN_POINT_X, Level::END_SPAWN_POINT_Y, Level::END_SPAWN_POINT_Z));
		}

		if ($source->getDimension() === DimensionIds::THE_END) {
			//leaving the end: players go back to their spawn point, everything else to the world spawn
			$spawn = $target->getSpawnLocation();
			if ($entity instanceof Player) {
				$playerSpawn = $entity->getSpawn();
				if ($playerSpawn->getLevel() !== null && !$playerSpawn->getLevel()->isClosed()) {
					$spawn = $playerSpawn;
				}
			}
			return new PortalTravel(PortalTravel::MODE_SPAWN, $spawn->getLevel() ?? $target, $spawn->asVector3());
		}

		//nether portal: remember the portal we come from so the way back finds it
		$axis = PortalShape::AXIS_X;
		$sourcePortal = PortalShape::find($source, $entity->getFloorX(), $entity->getFloorY(), $entity->getFloorZ());
		if ($sourcePortal !== null && $sourcePortal->isLit($source)) {
			$source->getPortalIndex()->add($sourcePortal);
			$axis = $sourcePortal->getAxis();
		}

		$scale = self::getCoordinateScale($source->getDimension(), $target->getDimension());
		[$minY, $maxY] = self::getPortalYRange($target);
		$center = new Vector3(
			(int) floor($entity->x * $scale),
			max($minY, min($maxY, $entity->getFloorY())),
			(int) floor($entity->z * $scale)
		);

		return new PortalTravel(PortalTravel::MODE_NETHER_PORTAL, $target, $center, $axis);
	}

	/**
	 * Heights the inside bottom of a created portal may have
	 *
	 * @return int[] [min, max]
	 */
	public static function getPortalYRange(Level $level) : array
	{
		$top = $level->getDimension() === DimensionIds::NETHER ? self::NETHER_ROOF_Y : $level->getWorldHeight() - 1;
		//the frame is 5 blocks high and starts one block below the inside
		return [Level::Y_MIN + 2, min($level->getWorldHeight() - 1, $top) - PortalShape::MIN_HEIGHT - 1];
	}

	/**
	 * Looks up the portal index of the world, loading the chunks of the candidates from the disk if needed.
	 * Portals which don't exist anymore are dropped from the index.
	 */
	public static function findIndexedPortal(Level $level, Vector3 $center, int $radius) : ?PortalShape
	{
		$index = $level->getPortalIndex();
		foreach ($index->getNearby($center, $radius) as [$x, $y, $z, $axis]) {
			if (!$level->loadChunk($x >> Chunk::COORD_BIT_SIZE, $z >> Chunk::COORD_BIT_SIZE, false)) {
				//the terrain is gone (world reset)
				$index->remove($x, $y, $z);
				continue;
			}
			//the frame may reach into the neighbour chunks
			[$dx, $dz] = $axis === PortalShape::AXIS_Z ? [0, 1] : [1, 0];
			for ($chunkX = ($x - $dx) >> Chunk::COORD_BIT_SIZE, $maxChunkX = ($x + $dx * (PortalShape::MAX_WIDTH + 1)) >> Chunk::COORD_BIT_SIZE; $chunkX <= $maxChunkX; ++$chunkX) {
				for ($chunkZ = ($z - $dz) >> Chunk::COORD_BIT_SIZE, $maxChunkZ = ($z + $dz * (PortalShape::MAX_WIDTH + 1)) >> Chunk::COORD_BIT_SIZE; $chunkZ <= $maxChunkZ; ++$chunkZ) {
					$level->loadChunk($chunkX, $chunkZ, false);
				}
			}

			$shape = PortalShape::find($level, $x, $y, $z, $axis);
			if ($shape === null || !$shape->isLit($level)) {
				$index->remove($x, $y, $z);
				continue;
			}
			if ($shape->getX() !== $x || $shape->getY() !== $y || $shape->getZ() !== $z) {
				$index->remove($x, $y, $z);
			}
			$index->add($shape);
			return $shape;
		}
		return null;
	}

	/**
	 * Looks for portal blocks in the loaded and populated chunks around the position. This finds portals
	 * which were built before the index existed or were placed by plugins.
	 */
	public static function scanLoadedChunks(Level $level, Vector3 $center, int $radius) : ?PortalShape
	{
		$cx = $center->getFloorX();
		$cy = $center->getFloorY();
		$cz = $center->getFloorZ();
		$minChunkX = ($cx - $radius) >> Chunk::COORD_BIT_SIZE;
		$maxChunkX = ($cx + $radius) >> Chunk::COORD_BIT_SIZE;
		$minChunkZ = ($cz - $radius) >> Chunk::COORD_BIT_SIZE;
		$maxChunkZ = ($cz + $radius) >> Chunk::COORD_BIT_SIZE;

		$best = null;
		$bestDistance = PHP_INT_MAX;
		foreach ($level->getChunks() as $chunk) {
			$chunkX = $chunk->getX();
			$chunkZ = $chunk->getZ();
			if ($chunkX < $minChunkX || $chunkX > $maxChunkX || $chunkZ < $minChunkZ || $chunkZ > $maxChunkZ || !$chunk->isPopulated()) {
				continue;
			}
			for ($subY = 0, $height = $chunk->getHeight(); $subY < $height; ++$subY) {
				$layers = $chunk->getSubChunk($subY)->getBlockLayers();
				if (!isset($layers[0])) {
					continue;
				}
				$layer = $layers[0];
				$hasPortal = false;
				foreach ($layer->getPalette() as $fullState) {
					if (($fullState >> Block::INTERNAL_METADATA_BITS) === BlockIds::PORTAL) {
						$hasPortal = true;
						break;
					}
				}
				if (!$hasPortal) {
					continue;
				}
				for ($x = 0; $x < Chunk::EDGE_LENGTH; ++$x) {
					$worldX = ($chunkX << Chunk::COORD_BIT_SIZE) | $x;
					if (abs($worldX - $cx) > $radius) {
						continue;
					}
					for ($z = 0; $z < Chunk::EDGE_LENGTH; ++$z) {
						$worldZ = ($chunkZ << Chunk::COORD_BIT_SIZE) | $z;
						if (abs($worldZ - $cz) > $radius) {
							continue;
						}
						for ($y = 0; $y < Chunk::EDGE_LENGTH; ++$y) {
							if (($layer->get($x, $y, $z) >> Block::INTERNAL_METADATA_BITS) !== BlockIds::PORTAL) {
								continue;
							}
							$worldY = ($subY << Chunk::COORD_BIT_SIZE) | $y;
							$distance = ($worldX - $cx) ** 2 + ($worldY - $cy) ** 2 + ($worldZ - $cz) ** 2;
							if ($distance < $bestDistance) {
								$bestDistance = $distance;
								$best = [$worldX, $worldY, $worldZ];
							}
						}
					}
				}
			}
		}

		if ($best === null) {
			return null;
		}
		$shape = PortalShape::find($level, $best[0], $best[1], $best[2]);
		if ($shape === null) {
			return null;
		}
		if (($level->getBlockAt($shape->getX(), $shape->getY(), $shape->getZ())->getDamage()) !== $shape->getAxis()) {
			//portals lit by older versions have no axis, fix them so they are shown the right way
			$shape->light($level);
		}
		$level->getPortalIndex()->add($shape);
		return $shape;
	}

	/**
	 * Builds a new portal at the closest suitable place around the position, or at the position itself
	 * with an obsidian platform if there is none, like vanilla does
	 */
	public static function createPortal(Level $level, Vector3 $center, int $radius, int $preferredAxis) : PortalShape
	{
		$cx = $center->getFloorX();
		$cy = $center->getFloorY();
		$cz = $center->getFloorZ();
		[$minY, $maxY] = self::getPortalYRange($level);
		$axes = $preferredAxis === PortalShape::AXIS_Z ? [PortalShape::AXIS_Z, PortalShape::AXIS_X] : [PortalShape::AXIS_X, PortalShape::AXIS_Z];

		$best = null;
		$bestDistance = PHP_INT_MAX;
		for ($dx = -$radius; $dx <= $radius; ++$dx) {
			for ($dz = -$radius; $dz <= $radius; ++$dz) {
				$horizontal = $dx * $dx + $dz * $dz;
				if ($horizontal >= $bestDistance) {
					continue;
				}
				$x = $cx + $dx;
				$z = $cz + $dz;
				$chunk = self::getPopulatedChunk($level, $x, $z);
				if ($chunk === null) {
					continue;
				}
				$lx = $x & Chunk::COORD_MASK;
				$lz = $z & Chunk::COORD_MASK;
				for ($y = $maxY; $y >= $minY; --$y) {
					$distance = $horizontal + ($y - $cy) ** 2;
					if ($distance >= $bestDistance) {
						continue;
					}
					if ($chunk->getFullBlock($lx, $y, $lz) !== (BlockIds::AIR << Block::INTERNAL_METADATA_BITS) || !self::isGround($chunk->getFullBlock($lx, $y - 1, $lz))) {
						continue;
					}
					foreach ($axes as $axis) {
						if (self::canBuildAt($level, $x, $y, $z, $axis)) {
							$best = [$x, $y, $z, $axis];
							$bestDistance = $distance;
							break;
						}
					}
				}
			}
		}

		if ($best !== null) {
			$shape = PortalShape::create($best[3], $best[0], $best[1], $best[2]);
			$shape->build($level, false);
		} else {
			$forcedMin = $level->getDimension() === DimensionIds::NETHER ? self::FORCED_MIN_Y_NETHER : self::FORCED_MIN_Y_OVERWORLD;
			$y = max(min($forcedMin, $maxY), min($maxY - 8, $cy));
			$shape = PortalShape::create($preferredAxis, $cx, $y, $cz);
			$shape->build($level, true);
		}

		$level->getPortalIndex()->add($shape);
		return $shape;
	}

	private static function getPopulatedChunk(Level $level, int $x, int $z) : ?Chunk
	{
		$chunk = $level->getChunk($x >> Chunk::COORD_BIT_SIZE, $z >> Chunk::COORD_BIT_SIZE);
		return $chunk !== null && $chunk->isPopulated() ? $chunk : null;
	}

	/**
	 * Whether a portal with the minimal size fits here: the frame and a block of space on both sides
	 * need to be free, and everything must stand on solid ground
	 */
	private static function canBuildAt(Level $level, int $x, int $y, int $z, int $axis) : bool
	{
		[$dx, $dz] = $axis === PortalShape::AXIS_X ? [1, 0] : [0, 1];
		for ($w = -1; $w <= PortalShape::MIN_WIDTH; ++$w) {
			for ($d = -1; $d <= 1; ++$d) {
				$bx = $x + $dx * $w + $dz * $d;
				$bz = $z + $dz * $w + $dx * $d;
				$chunk = self::getPopulatedChunk($level, $bx, $bz);
				if ($chunk === null) {
					return false;
				}
				$lx = $bx & Chunk::COORD_MASK;
				$lz = $bz & Chunk::COORD_MASK;
				if (!self::isGround($chunk->getFullBlock($lx, $y - 1, $lz))) {
					return false;
				}
				for ($h = 0; $h <= PortalShape::MIN_HEIGHT; ++$h) {
					if (!self::isReplaceable($chunk->getFullBlock($lx, $y + $h, $lz))) {
						return false;
					}
				}
			}
		}
		return true;
	}

	private static function isGround(int $fullState) : bool
	{
		if (!isset(self::$groundCache[$fullState])) {
			$block = BlockFactory::fromFullBlock($fullState);
			self::$groundCache[$fullState] = $block->isSolid() && !($block instanceof Liquid) && $block->getId() !== BlockIds::BEDROCK;
		}
		return self::$groundCache[$fullState];
	}

	private static function isReplaceable(int $fullState) : bool
	{
		if (!isset(self::$replaceableCache[$fullState])) {
			$block = BlockFactory::fromFullBlock($fullState);
			self::$replaceableCache[$fullState] = $block->getId() === BlockIds::AIR || ($block->canBeReplaced() && !($block instanceof Liquid));
		}
		return self::$replaceableCache[$fullState];
	}

	/**
	 * Rebuilds the obsidian platform of the end, like vanilla does every time something arrives there
	 */
	public static function prepareEndSpawn(Level $end) : Position
	{
		$x = Level::END_SPAWN_POINT_X;
		$y = Level::END_SPAWN_POINT_Y;
		$z = Level::END_SPAWN_POINT_Z;
		if ((bool) $end->getServer()->getSubmarineProperty("dimensions.the-end.create-spawn-platform", true)) {
			$obsidian = BlockFactory::get(BlockIds::OBSIDIAN);
			$air = BlockFactory::get(BlockIds::AIR);
			for ($dx = -2; $dx <= 2; ++$dx) {
				for ($dz = -2; $dz <= 2; ++$dz) {
					for ($dy = -1; $dy < 3; ++$dy) {
						$block = $dy === -1 ? $obsidian : $air;
						if ($end->getBlockAt($x + $dx, $y + $dy, $z + $dz)->getId() !== $block->getId()) {
							$end->setBlock(new Vector3($x + $dx, $y + $dy, $z + $dz), $block);
						}
					}
				}
			}
		}
		return new Position($x + 0.5, $y, $z + 0.5, $end);
	}
}
