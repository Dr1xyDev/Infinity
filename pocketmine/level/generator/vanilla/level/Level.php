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

/**
 * All Level related classes are here, like Generators, Populators, Noise, ...
 */

namespace pocketmine\level\generator\vanilla\level;

use ArrayOutOfBoundsException;
use InvalidArgumentException;
use InvalidStateException;
use pocketmine\level\generator\vanilla\block\Air;
use pocketmine\level\generator\vanilla\block\Block;
use pocketmine\level\generator\vanilla\block\BlockFactory;
use pocketmine\level\generator\vanilla\block\Liquid;
use pocketmine\level\generator\vanilla\block\UnknownBlock;
use pocketmine\level\generator\vanilla\entity\CreatureType;
use pocketmine\level\generator\vanilla\entity\Entity;
use pocketmine\level\generator\vanilla\entity\object\ExperienceOrb;
use pocketmine\level\generator\vanilla\entity\object\ItemEntity;
use pocketmine\level\generator\vanilla\event\block\BlockBreakEvent;
use pocketmine\level\generator\vanilla\event\block\BlockPlaceEvent;
use pocketmine\level\generator\vanilla\event\block\BlockUpdateEvent;
use pocketmine\level\generator\vanilla\event\level\ChunkLoadEvent;
use pocketmine\level\generator\vanilla\event\level\ChunkPopulateEvent;
use pocketmine\level\generator\vanilla\event\level\ChunkUnloadEvent;
use pocketmine\level\generator\vanilla\event\level\LevelSaveEvent;
use pocketmine\level\generator\vanilla\event\level\LevelUnloadEvent;
use pocketmine\level\generator\vanilla\event\level\SpawnChangeEvent;
use pocketmine\level\generator\vanilla\event\player\PlayerInteractEvent;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\item\ItemFactory;
use pocketmine\level\generator\vanilla\level\biome\Biome;
use pocketmine\level\generator\vanilla\level\biome\SpawnListEntry;
use pocketmine\level\generator\vanilla\level\format\Chunk;
use pocketmine\level\generator\vanilla\level\format\ChunkException;
use pocketmine\level\generator\vanilla\level\format\EmptySubChunk;
use pocketmine\level\generator\vanilla\level\format\io\exception\CorruptedChunkException;
use pocketmine\level\generator\vanilla\level\format\io\exception\UnsupportedChunkFormatException;
use pocketmine\level\generator\vanilla\level\format\io\WritableLevelProvider;
use pocketmine\level\generator\vanilla\level\format\SubChunk;
use pocketmine\level\generator\vanilla\level\generator\GeneratorManager;
use pocketmine\level\generator\vanilla\level\generator\GeneratorRegisterTask;
use pocketmine\level\generator\vanilla\level\generator\GeneratorUnregisterTask;
use pocketmine\level\generator\vanilla\level\generator\PopulationTask;
use pocketmine\level\generator\vanilla\level\light\BlockLightUpdate;
use pocketmine\level\generator\vanilla\level\light\LightPopulationTask;
use pocketmine\level\generator\vanilla\level\light\SkyLightUpdate;
use pocketmine\level\generator\vanilla\level\particle\DestroyBlockParticle;
use pocketmine\level\generator\vanilla\level\particle\Particle;
use pocketmine\level\generator\vanilla\level\sound\BlockPlaceSound;
use pocketmine\level\generator\vanilla\level\sound\Sound;
use pocketmine\level\generator\vanilla\math\AxisAlignedBB;
use pocketmine\level\generator\vanilla\math\Facing;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\math\VoxelRayTrace;
use pocketmine\level\generator\vanilla\nbt\tag\ListTag;
use pocketmine\level\generator\vanilla\nbt\tag\StringTag;
use pocketmine\level\generator\vanilla\network\mcpe\ChunkRequestTask;
use pocketmine\level\generator\vanilla\network\mcpe\convert\block\BlockProtocolConvertor;
use pocketmine\level\generator\vanilla\network\mcpe\convert\block\DynamicBlockStateResolver;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\AddActorPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\BlockActorDataPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\DataPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\GameRulesChangedPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\LevelEventPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\SetDifficultyPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\SetSpawnPositionPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\SetTimePacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\DimensionIds;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\UpdateBlockPacket;
use pocketmine\level\generator\vanilla\level\portal\PortalIndex;
use pocketmine\Player;
use pocketmine\level\generator\vanilla\scheduler\AsyncPool;
use pocketmine\Server;
use pocketmine\level\generator\vanilla\tile\Chest;
use pocketmine\level\generator\vanilla\tile\Container;
use pocketmine\level\generator\vanilla\tile\LootContainer;
use pocketmine\level\generator\vanilla\tile\Skull as TileSkull;
use pocketmine\level\generator\vanilla\tile\Spawnable;
use pocketmine\level\generator\vanilla\tile\Tile;
use pocketmine\level\generator\vanilla\timings\TimingsHandler;
use pocketmine\level\generator\vanilla\utils\AssumptionFailedError;
use pocketmine\level\generator\vanilla\utils\Random;
use pocketmine\level\generator\vanilla\utils\ReversePriorityQueue;
use pocketmine\level\generator\vanilla\utils\Utils;
use pocketmine\level\generator\vanilla\utils\WeightedRandomItem;
use TypeError;
use function abs;
use function array_filter;
use function array_map;
use function array_merge;
use function array_sum;
use function assert;
use function cos;
use function count;
use function floor;
use function get_class;
use function gettype;
use function in_array;
use function is_a;
use function is_array;
use function is_object;
use function max;
use function microtime;
use function min;
use function mt_rand;
use function spl_object_id;
use function strtolower;
use function trim;
use const INT32_MAX;
use const INT32_MIN;
use const M_PI;
use const PHP_INT_MAX;
use const PHP_INT_MIN;

/**
 * @phpstan-type ChunkPosHash int
 * @phpstan-type BlockPosHash int
 * @phpstan-type ChunkBlockPosHash int
 */
class Level implements ChunkManager
{
	//TODO: this could probably do with being a lot bigger
	private const BLOCK_CACHE_SIZE_CAP = 2048;

	public const int END_SPAWN_POINT_X = 100;
	public const int END_SPAWN_POINT_Y = 50;
	public const int END_SPAWN_POINT_Z = 0;

	private static int $levelIdCounter = 1;

	public const Y_MASK = 0xFF;

	public const Y_MAX = 256;
	public const Y_MIN = 0;

	public const MAX_POPULATION_RADIUS = 2;

	public const TIME_DAY = 1000;
	public const TIME_NOON = 6000;
	public const TIME_SUNSET = 12000;
	public const TIME_NIGHT = 13000;
	public const TIME_MIDNIGHT = 18000;
	public const TIME_SUNRISE = 23000;

	public const TIME_FULL = 24000;

	public const DIFFICULTY_PEACEFUL = 0;
	public const DIFFICULTY_EASY = 1;
	public const DIFFICULTY_NORMAL = 2;
	public const DIFFICULTY_HARD = 3;

	public const DEFAULT_TICKED_BLOCKS_PER_SUBCHUNK_PER_TICK = 3;

	public const float MAX_SECONDS_FOR_ENTITY_QUEUE_TICK = 0.020;

	/** @var Tile[] */
	private array $tiles = [];

	/**
	 * @var Player[] entity runtime ID => Player
	 * @phpstan-var array<int, Player>
	 */
	private array $players = [];

	/**
	 * @var Entity[] entity runtime ID => Entity
	 * @phpstan-var array<int, Entity>
	 */
	private array $entities = [];

	/**
	 * @var Block[][] chunkHash => [relativeBlockHash => Block]
	 * @phpstan-var array<ChunkPosHash, array<ChunkBlockPosHash, Block>>
	 */
	private array $blockCache = [];
	private int $blockCacheSize = 0;
	/**
	 * @var AxisAlignedBB[][][] chunkHash => [relativeBlockHash => AxisAlignedBB[]]
	 * @phpstan-var array<ChunkPosHash, array<ChunkBlockPosHash, list<AxisAlignedBB>>>
	 */
	private array $blockCollisionBoxCache = [];

	/** @var string[][] */
	private array $chunkCache = [];

	private int $sendTimeTicker = 0;

	private int $providerGarbageCollectionTicker = 0;

	/** @var LevelListener[] */
	private array $levelListeners = [];

	private int $worldHeight;

	/** @var ChunkLoader[] */
	private array $loaders = [];
	/** @var int[] */
	private array $loaderCounter = [];
	/** @var ChunkLoader[][] */
	private array $chunkLoaders = [];
	/** @var Player[][] */
	private array $playerLoaders = [];

	/**
	 * @var DataPacket[][]
	 * @phpstan-var array<ChunkPosHash, list<DataPacket>>
	 */
	private array $chunkPackets = [];

	/**
	 * @var float[] chunkHash => timestamp of request
	 * @phpstan-var array<ChunkPosHash, float>
	 */
	private array $unloadQueue = [];

	private int $time;
	public bool $stopTime = false;

	private float $sunAnglePercentage = 0.0;
	private int $skyLightReduction = 0;

	private string $folderName;
	private string $displayName;

	/**
	 * @var Chunk[]
	 * @phpstan-var array<ChunkPosHash, Chunk>
	 */
	private array $chunks = [];

	/**
	 * @var Vector3[][] chunkHash => [relativeBlockHash => Vector3]
	 * @phpstan-var array<ChunkPosHash, array<ChunkBlockPosHash, Vector3>>
	 */
	private array $changedBlocks = [];

	/** @phpstan-var ReversePriorityQueue<int, Vector3> */
	private ReversePriorityQueue $scheduledBlockUpdateQueue;
	/**
	 * @var int[] blockHash => tick delay
	 * @phpstan-var array<BlockPosHash, int>
	 */
	private array $scheduledBlockUpdateQueueIndex = [];

	/** @phpstan-var \SplQueue<int> */
	private \SplQueue $neighbourBlockUpdateQueue;

	private EntityUpdatePriorityQueue $entityUpdatePriorityQueue;

	/** @var Player[][][] */
	private array $chunkSendQueue = [];
	/** @var ChunkRequestTask[][] */
	private array $chunkSendTasks = [];

	/** @var bool[] */
	private array $chunkPopulationQueue = [];
	/** @var bool[] */
	private array $chunkPopulationLock = [];
	private int $chunkPopulationQueueSize = 2;
	/** Radius of the neighbour chunks handed to the populator (1 = 3x3) */
	private int $chunkPopulationRadius = 1;
	/** @var bool[] */
	private array $generatorRegisteredWorkers = [];

	private bool $autoSave;

	private int $sleepTicks = 0;

	private int $chunkTickRadius;
	/** @var int[] */
	private array $chunkTickList = [];
	private int $chunksPerTick;
	private bool $clearChunksOnTick;
	private int $tickedBlocksPerSubchunkPerTick = self::DEFAULT_TICKED_BLOCKS_PER_SUBCHUNK_PER_TICK;
	/**
	 * @var true[]
	 * @phpstan-var array<int, true>
	 */
	private array $randomTickBlocks = [];
	/**
	 * @var TimingsHandler[]
	 * @phpstan-var array<int, TimingsHandler>
	 */
	private array $randomTickBlocksTimings = [];

	public LevelTimings $timings;

	public float $tickRateTime = 0;

	private bool $doingTick = false;

	/** @phpstan-var class-string<generator\Generator> */
	private string $generator;

	private bool $closed = false;

	private ?BlockLightUpdate $blockLightUpdate = null;
	private ?SkyLightUpdate $skyLightUpdate = null;

	private \Logger $logger;

	private int $levelId;
	/** @var DataPacket[] */
	private array $globalPackets = [];

	protected int $dimension = DimensionIds::OVERWORLD;

	private ?PortalIndex $portalIndex = null;

	public Random $random;
	private AnimalSpawner $mobSpawner;

	private GameRules $gameRules;

	private bool $spawnPeacefulMobs = true;
	private bool $spawnHostileMobs = true;

	public static function chunkHash(int $x, int $z) : int
	{
		return (($x & 0xFFFFFFFF) << 32) | ($z & 0xFFFFFFFF);
	}

	public static function blockHash(int $x, int $y, int $z) : int
	{
		if ($y < Level::Y_MIN || $y >= Level::Y_MAX) {
			throw new InvalidArgumentException("Y coordinate $y is out of range!");
		}
		return (($x & 0xFFFFFFF) << 36) | (($y & Level::Y_MASK) << 28) | ($z & 0xFFFFFFF);
	}

	/**
	 * Computes a small index relative to chunk base from the given coordinates.
	 */
	public static function chunkBlockHash(int $x, int $y, int $z) : int
	{
		return ($y << 8) | (($z & 0xf) << 4) | ($x & 0xf);
	}

	public static function getBlockXYZ(int $hash, ?int &$x, ?int &$y, ?int &$z) : void
	{
		$x = $hash >> 36;
		$y = ($hash >> 28) & Level::Y_MASK; //it's always positive
		$z = ($hash & 0xFFFFFFF) << 36 >> 36;
	}

	public static function getXZ(int $hash, ?int &$x, ?int &$z) : void
	{
		$x = $hash >> 32;
		$z = ($hash & 0xFFFFFFFF) << 32 >> 32;
	}

	public static function getDifficultyFromString(string $str) : int
	{
		return match (strtolower(trim($str))) {
			"0", "peaceful", "p" => Level::DIFFICULTY_PEACEFUL,
			"1", "easy", "e" => Level::DIFFICULTY_EASY,
			"2", "normal", "n" => Level::DIFFICULTY_NORMAL,
			"3", "hard", "h" => Level::DIFFICULTY_HARD,
			default => -1,
		};

	}

	public static function getDimensionFromString(string $str) : int
	{
		return match (strtolower(trim($str))) {
			"nether", "hell" => DimensionIds::NETHER,
			"end", "ender" => DimensionIds::THE_END,
			default => DimensionIds::OVERWORLD,
		};
	}

	/**
	 * Init the default level data
	 */
	public function __construct(
		private Server $server,
		string $name, //TODO: this should be folderName (named arguments BC break)
		private WritableLevelProvider $provider,
		private AsyncPool $workerPool
	) {
		$this->folderName = $name;
		$this->levelId = self::$levelIdCounter++;

		$this->autoSave = $server->getAutoSave();

		$this->displayName = $this->provider->getLevelData()->getName();
		$this->logger = new \PrefixedLogger($server->getLogger(), "Level: $this->displayName");

		$this->worldHeight = $this->provider->getWorldHeight();

		$this->server->getLogger()->info($this->server->getLanguage()->translateString("pocketmine.level.preparing", [$this->displayName]));
		$generator = GeneratorManager::getGenerator($this->provider->getLevelData()->getGenerator()) ??
			throw new AssumptionFailedError("WorldManager should already have checked that the generator exists");
		$this->generator = $generator;

		$this->scheduledBlockUpdateQueue = new ReversePriorityQueue();
		$this->scheduledBlockUpdateQueue->setExtractFlags(\SplPriorityQueue::EXTR_BOTH);

		$this->neighbourBlockUpdateQueue = new \SplQueue();

		$this->entityUpdatePriorityQueue = new EntityUpdatePriorityQueue();

		$this->dimension = self::getDimensionFromString($this->provider->getLevelData()->getGenerator());

		$this->time = $this->provider->getLevelData()->getTime();

		$this->chunkTickRadius = min($this->server->getViewDistance(), max(1, (int) $this->server->getProperty("chunk-ticking.tick-radius", 4)));
		$this->chunksPerTick = (int) $this->server->getProperty("chunk-ticking.per-tick", 40);
		$this->chunkPopulationQueueSize = (int) $this->server->getProperty("chunk-generation.population-queue-size", 2);
		$this->chunkPopulationRadius = max(1, min(self::MAX_POPULATION_RADIUS, (int) $this->server->getProperty("chunk-generation.population-radius", 1)));
		$this->clearChunksOnTick = (bool) $this->server->getProperty("chunk-ticking.clear-tick-list", true);

		$this->tickedBlocksPerSubchunkPerTick = (int) $this->server->getProperty("chunk-ticking.blocks-per-subchunk-per-tick", self::DEFAULT_TICKED_BLOCKS_PER_SUBCHUNK_PER_TICK);

		$this->timings = new LevelTimings($this);
		$this->random = new Random();
		$this->mobSpawner = new AnimalSpawner();
		$this->gameRules = $this->provider->getLevelData()->getGameRules();

		$this->initRandomTickBlocksFromConfig();
	}

	private function initRandomTickBlocksFromConfig() : void
	{
		$dontTickBlocks = [];
		foreach (Server::getInstance()->getProperty("chunk-ticking.disable-block-ticking", []) as $name) {
			$name = (string) $name;
			$item = ItemFactory::fromStringSingle($name);

			try {
				$block = $item->getBlock();
			} catch (InvalidArgumentException $exception) {
				//TODO: we probably ought to log an error here
				continue;
			}

			if ($block->getId() !== Block::AIR) {
				$dontTickBlocks[$block->getTypeId()] = $name;
			}
		}

		foreach (BlockFactory::getAllKnownStates() as $state) {
			$dontTickName = $dontTickBlocks[$state->getTypeId()] ?? null;
			if ($dontTickName === null && $state->ticksRandomly()) {
				$this->randomTickBlocks[$state->getFullId()] = true;
				$this->randomTickBlocksTimings[$state->getFullId()] = $this->timings->getRandomTickBlocks($state::class);
			}
		}
	}

	public function getTickRateTime() : float
	{
		return $this->tickRateTime;
	}

	public function registerGeneratorToWorker(int $worker) : void
	{
		$this->logger->debug("Registering generator on worker $worker");
		$this->workerPool->submitTaskToWorker(new GeneratorRegisterTask($this, $this->generator, $this->provider->getLevelData()->getGeneratorOptions()), $worker);
		$this->generatorRegisteredWorkers[$worker] = true;
	}

	public function unregisterGenerator() : void
	{
		foreach ($this->workerPool->getRunningWorkers() as $i) {
			if (isset($this->generatorRegisteredWorkers[$i])) {
				$this->workerPool->submitTaskToWorker(new GeneratorUnregisterTask($this), $i);
			}
		}
		$this->generatorRegisteredWorkers = [];
	}

	public function getServer() : Server
	{
		return $this->server;
	}

	public function getLogger() : \Logger
	{
		return $this->logger;
	}

	final public function getProvider() : WritableLevelProvider
	{
		return $this->provider;
	}

	/**
	 * Returns the unique level identifier
	 */
	final public function getId() : int
	{
		return $this->levelId;
	}

	public function isClosed() : bool
	{
		return $this->closed;
	}

	public function close() : void
	{
		if ($this->closed) {
			throw new InvalidStateException("Tried to close a level which is already closed");
		}

		foreach ($this->chunks as $chunk) {
			$this->unloadChunk($chunk->getX(), $chunk->getZ(), false);
		}

		$this->save();
		$this->portalIndex?->save();

		$this->unregisterGenerator();

		$this->provider->close();
		$this->blockCache = [];
		$this->blockCacheSize = 0;
		$this->blockCollisionBoxCache = [];

		$this->closed = true;
	}

	public function addSound(Sound $sound, ?array $players = null)
	{
		if ($sound->isUseProtocol()) {
			$players = $players === null ? $this->getPlayers() : $players;
			$targets = [];
			foreach ($players as $player) {
				if ($player->isConnected()) {
					$targets[$player->getProtocolVersion()][] = $player;
				}
			}

			foreach ($targets as $protocolVersion => $players) {
				$sound->setProtocol($protocolVersion);

				$pk = $sound->encode();
				if (!is_array($pk)) {
					$pk = [$pk];
				}

				if (!empty($pk)) {
					$this->server->batchPackets($players, $pk, false);
				}
			}
		} else {
			$pk = $sound->encode();
			if (!is_array($pk)) {
				$pk = [$pk];
			}

			if (!empty($pk)) {
				if ($players === null) {
					foreach ($pk as $e) {
						$this->broadcastPacketToViewers($sound, $e);
					}
				} else {
					$this->server->batchPackets($players, $pk, false);
				}
			}
		}
	}

	public function addParticle(Particle $particle, ?array $players = null)
	{
		if ($particle->isUseProtocol()) {
			$players = $players === null ? $this->getPlayers() : $players;
			$targets = [];
			foreach ($players as $player) {
				if ($player->isConnected()) {
					$targets[$player->getProtocolVersion()][] = $player;
				}
			}

			foreach ($targets as $protocolVersion => $players) {
				$particle->setProtocol($protocolVersion);

				$pk = $particle->encode();
				if (!is_array($pk)) {
					$pk = [$pk];
				}

				if (!empty($pk)) {
					$this->server->batchPackets($players, $pk, false);
				}
			}
		} else {
			$pk = $particle->encode();
			if (!is_array($pk)) {
				$pk = [$pk];
			}

			if (!empty($pk)) {
				if ($players === null) {
					foreach ($pk as $e) {
						$this->broadcastPacketToViewers($particle, $e);
					}
				} else {
					$this->server->batchPackets($players, $pk, false);
				}
			}
		}
	}

	/**
	 * Broadcasts a LevelEvent to players in the area. This could be sound, particles, weather changes, etc.
	 *
	 * @param Vector3|null $pos If null, broadcasts to every player in the Level
	 */
	public function broadcastLevelEvent(?Vector3 $pos, int $evid, int $data = 0)
	{
		$pk = new LevelEventPacket();
		$pk->evid = $evid;
		$pk->data = $data;
		if ($pos !== null) {
			$pk->position = $pos->asVector3();
			$this->broadcastPacketToViewers($pos, $pk);
		} else {
			$pk->position = null;
			$this->broadcastGlobalPacket($pk);
		}
	}

	/**
	 * Broadcasts a LevelSoundEvent to players in the area.
	 *
	 * @param bool $disableRelativeVolume If true, all players receiving this sound-event will hear the sound at full volume regardless of distance
	 */
	public function broadcastLevelSoundEvent(Vector3 $pos, int $soundId, int $extraData = -1, int $entityTypeId = -1, bool $isBabyMob = false, bool $disableRelativeVolume = false)
	{
		$pk = new LevelSoundEventPacket();
		$pk->sound = $soundId;
		$pk->extraData = $extraData;
		$pk->entityType = AddActorPacket::LEGACY_ID_MAP_BC[$entityTypeId] ?? ":";
		$pk->isBabyMob = $isBabyMob;
		$pk->disableRelativeVolume = $disableRelativeVolume;
		$pk->position = $pos->asVector3();
		$this->broadcastPacketToViewers($pos, $pk);
	}

	public function getAutoSave() : bool
	{
		return $this->autoSave;
	}

	public function setAutoSave(bool $value)
	{
		$this->autoSave = $value;
	}

	/**
	 * @internal
	 * @see Server::unloadLevel()
	 *
	 * Unloads the current level from memory safely
	 *
	 * @param bool $force default false, force unload of default level
	 *
	 * @throws InvalidStateException if trying to unload a level during level tick
	 */
	public function unload(bool $force = false) : bool
	{
		if ($this->doingTick && !$force) {
			throw new InvalidStateException("Cannot unload a world during world tick");
		}

		$ev = new LevelUnloadEvent($this);

		if ($this === $this->server->getDefaultLevel() && !$force) {
			$ev->setCancelled(true);
		}

		$ev->call();

		if (!$force && $ev->isCancelled()) {
			return false;
		}

		$this->logger->info($this->server->getLanguage()->translateString("pocketmine.level.unloading", [$this->getName()]));
		$defaultLevel = $this->server->getDefaultLevel();
		foreach ($this->getPlayers() as $player) {
			if ($this === $defaultLevel || $defaultLevel === null) {
				$player->close($player->getLeaveMessage(), "Forced default world unload");
			} elseif ($defaultLevel instanceof Level) {
				$player->teleport($this->server->getDefaultLevel()->getSafeSpawn());
			}
		}

		foreach ($this->levelListeners as $listener) {
			$listener->onLevelUnloaded($this);
		}

		if ($this === $defaultLevel) {
			$this->server->setDefaultLevel(null);
		}

		$this->server->removeLevel($this);

		$this->close();

		return true;
	}

	/**
	 * @deprecated WARNING: This function has a misleading name. Contrary to what the name might imply, this function
	 * DOES NOT return players who are IN a chunk, rather, it returns players who can SEE the chunk.
	 *
	 * Returns a list of players who have the target chunk within their view distance.
	 *
	 * @return Player[]
	 */
	public function getChunkPlayers(int $chunkX, int $chunkZ) : array
	{
		return $this->playerLoaders[Level::chunkHash($chunkX, $chunkZ)] ?? [];
	}

	/**
	 * Gets the chunk loaders being used in a specific chunk
	 *
	 * @return ChunkLoader[]
	 * @phpstan-return array<int, ChunkLoader>
	 */
	public function getChunkLoaders(int $chunkX, int $chunkZ) : array
	{
		return $this->chunkLoaders[Level::chunkHash($chunkX, $chunkZ)] ?? [];
	}

	/**
	 * Returns an array of players who have the target position within their view distance.
	 *
	 * @return Player[]
	 */
	public function getViewersForPosition(Vector3 $pos) : array
	{
		return $this->getChunkPlayers($pos->getFloorX() >> Chunk::COORD_BIT_SIZE, $pos->getFloorZ() >> Chunk::COORD_BIT_SIZE);
	}

	public function addChunkPacket(int $chunkX, int $chunkZ, DataPacket $packet) : void
	{
		if (!isset($this->chunkPackets[$index = Level::chunkHash($chunkX, $chunkZ)])) {
			$this->chunkPackets[$index] = [$packet];
		} else {
			$this->chunkPackets[$index][] = $packet;
		}
	}

	/**
	 * Broadcasts a packet to every player who has the target position within their view distance.
	 */
	public function broadcastPacketToViewers(Vector3 $pos, DataPacket $packet) : void
	{
		$this->addChunkPacket($pos->getFloorX() >> Chunk::COORD_BIT_SIZE, $pos->getFloorZ() >> Chunk::COORD_BIT_SIZE, $packet);
	}

	/**
	 * Broadcasts a packet to every player in the level.
	 */
	public function broadcastGlobalPacket(DataPacket $packet) : void
	{
		$this->globalPackets[] = $packet;
	}

	/**
	 * @deprecated
	 * @see Level::broadcastGlobalPacket()
	 */
	public function addGlobalPacket(DataPacket $packet) : void
	{
		$this->globalPackets[] = $packet;
	}

	public function registerChunkLoader(ChunkLoader $loader, int $chunkX, int $chunkZ, bool $autoLoad = true) : void
	{
		$loaderId = spl_object_id($loader);

		if (!isset($this->chunkLoaders[$chunkHash = Level::chunkHash($chunkX, $chunkZ)])) {
			$this->chunkLoaders[$chunkHash] = [];
			$this->playerLoaders[$chunkHash] = [];
		} elseif (isset($this->chunkLoaders[$chunkHash][$loaderId])) {
			return;
		}

		$this->chunkLoaders[$chunkHash][$loaderId] = $loader;
		if ($loader instanceof Player) {
			$this->playerLoaders[$chunkHash][$loaderId] = $loader;
		}

		if (!isset($this->loaders[$loaderId])) {
			$this->loaderCounter[$loaderId] = 1;
			$this->loaders[$loaderId] = $loader;
		} else {
			++$this->loaderCounter[$loaderId];
		}

		$this->cancelUnloadChunkRequest($chunkX, $chunkZ);

		if ($autoLoad) {
			$this->loadChunk($chunkX, $chunkZ);
		}
	}

	public function unregisterChunkLoader(ChunkLoader $loader, int $chunkX, int $chunkZ) : void
	{
		$chunkHash = Level::chunkHash($chunkX, $chunkZ);
		$loaderId = spl_object_id($loader);
		if (isset($this->chunkLoaders[$chunkHash][$loaderId])) {
			unset($this->chunkLoaders[$chunkHash][$loaderId]);
			unset($this->playerLoaders[$chunkHash][$loaderId]);
			if (count($this->chunkLoaders[$chunkHash]) === 0) {
				unset($this->chunkLoaders[$chunkHash]);
				unset($this->playerLoaders[$chunkHash]);
				$this->unloadChunkRequest($chunkX, $chunkZ, true);
			}

			if (--$this->loaderCounter[$loaderId] === 0) {
				unset($this->loaderCounter[$loaderId]);
				unset($this->loaders[$loaderId]);
			}
		}
	}

	/**
	 * Registers a listener to receive events on a Level.
	 */
	public function registerLevelListener(LevelListener $listener) : void
	{
		$this->levelListeners[spl_object_id($listener)] = $listener;
	}

	/**
	 * Unregisters a level listener previously registered.
	 * @see Level::registerLevelListener()
	 */
	public function unregisterLevelListener(LevelListener $listener) : void
	{
		unset($this->levelListeners[spl_object_id($listener)]);
	}

	/**
	 * @internal
	 */
	public function sendTime(Player ...$targets) : void
	{
		$targets = count($targets) > 0 ? $targets : $this->players;
		if (empty($targets)) {
			return;
		}

		$pk = new SetTimePacket();
		$pk->time = $this->time & 0xffffffff; //avoid overflowing the field, since the packet uses an int32

		$this->server->broadcastPacket($targets, $pk);
	}

	public function sendGameRules(?array $rules = null, Player ...$targets) : void
	{
		$targets = count($targets) > 0 ? $targets : $this->players;
		if (empty($targets)) {
			return;
		}

		$pk = new GameRulesChangedPacket();
		$pk->gameRules = $rules ?? $this->gameRules->getRules();

		$this->server->broadcastPacket($targets, $pk);
	}

	public function isDoingTick() : bool
	{
		return $this->doingTick;
	}

	/**
	 * @internal
	 */
	public function doTick(int $currentTick) : void
	{
		if ($this->closed) {
			throw new InvalidStateException("Attempted to tick a world which has been closed");
		}

		$this->timings->doTick->startTiming();
		$this->doingTick = true;
		try {
			$this->actuallyDoTick($currentTick);
		} finally {
			$this->doingTick = false;
			$this->timings->doTick->stopTiming();
		}
	}

	protected function actuallyDoTick(int $currentTick) : void
	{
		if (!$this->stopTime && $this->gameRules->getBool(GameRules::RULE_DO_DAYLIGHT_CYCLE, true)) {
			//this simulates an overflow, as would happen in any language which doesn't do stupid things to var types
			if ($this->time === PHP_INT_MAX) {
				$this->time = PHP_INT_MIN;
			} else {
				$this->time++;
			}
		}

		$this->sunAnglePercentage = $this->computeSunAnglePercentage(); //Sun angle depends on the current time
		$this->skyLightReduction = $this->computeSkyLightReduction(); //Sky light reduction depends on the sun angle

		if (++$this->sendTimeTicker === 200) {
			$this->sendTime();
			$this->sendTimeTicker = 0;
		}

		if (!empty($dirty = $this->gameRules->getDirtyRules())) {
			$this->sendGameRules($dirty);
			$this->gameRules->clearDirtyRules();
		}

		$this->unloadChunks();
		if (++$this->providerGarbageCollectionTicker >= 6000) {
			$this->provider->doGarbageCollection();
			$this->providerGarbageCollectionTicker = 0;
		}

		//Do block updates
		$this->timings->scheduledBlockUpdates->startTiming();

		//Delayed updates
		while ($this->scheduledBlockUpdateQueue->count() > 0 && $this->scheduledBlockUpdateQueue->current()["priority"] <= $currentTick) {
			/** @var Vector3 $vec */
			$vec = $this->scheduledBlockUpdateQueue->extract()["data"];
			unset($this->scheduledBlockUpdateQueueIndex[Level::blockHash($vec->x, $vec->y, $vec->z)]);
			if (!$this->isInLoadedTerrain($vec)) {
				continue;
			}
			$block = $this->getBlock($vec);
			$block->onScheduledUpdate();
		}

		//Normal updates
		while ($this->neighbourBlockUpdateQueue->count() > 0) {
			$index = $this->neighbourBlockUpdateQueue->dequeue();
			Level::getBlockXYZ($index, $x, $y, $z);

			$block = $this->getBlockAt($x, $y, $z);
			$block->clearCaches(); //for blocks like fences, force recalculation of connected AABBs

			$ev = new BlockUpdateEvent($block);
			$ev->call();
			if (!$ev->isCancelled()) {
				$block->onNearbyBlockChange();
			}
		}

		$this->timings->scheduledBlockUpdates->stopTiming();

		$this->timings->entityTickAlways->startTiming();

		$timeAllowance = microtime(true) + self::MAX_SECONDS_FOR_ENTITY_QUEUE_TICK;

		foreach ($this->entityUpdatePriorityQueue->getAlwaysUpdateIds() as $id => $_) {
			$entity = $this->entities[$id] ?? null;
			if($entity === null) {
				$this->entityUpdatePriorityQueue->removeAny($id);
				continue;
			}

			try{
				if ($entity->isClosed() || $entity->isFlaggedForDespawn() || !$entity->onUpdate($currentTick)) {
					$this->entityUpdatePriorityQueue->removeAny($id);
				}

				if ($entity->isFlaggedForDespawn()) {
					$entity->close();
				}
			} catch (\Throwable $e) {
				$this->getServer()->getLogger()->logException($e);
				$entity->close();
			}
		}

		$this->timings->entityTickAlways->stopTiming();

		$this->timings->entityTickQueue->startTiming();

		$this->entityUpdatePriorityQueue->beginTick();
		while (microtime(true) < $timeAllowance) {
			$id = $this->entityUpdatePriorityQueue->pop();
			if ($id === null) {
				break;
			}

			$entity = $this->entities[$id] ?? null;
			if ($entity === null) {
				$this->entityUpdatePriorityQueue->removeAny($id);
				continue;
			}

			try{
				if ($entity->isClosed() || $entity->isFlaggedForDespawn() || !$entity->onUpdate($currentTick)) {
					$this->entityUpdatePriorityQueue->removeAny($id);
				} else {
					$this->entityUpdatePriorityQueue->markUpdated($id);
				}

				if ($entity->isFlaggedForDespawn()) {
					$entity->close();
				}
			} catch (\Throwable $e) {
				$this->getServer()->getLogger()->logException($e);
				$entity->close();
			}
		}

		$this->timings->entityTickQueue->stopTiming();

		$this->timings->randomChunkUpdates->startTiming();
		$this->tickChunks();
		$this->timings->randomChunkUpdates->stopTiming();

		if (($this->server->getSubmarineProperty("level.generic-auto-mob-spawning", false) && $this->gameRules->getBool(GameRules::RULE_DO_MOB_SPAWNING)) && $currentTick % 400 === 0) {
			$eligibleChunks = [];
			foreach ($this->players as $player) {
				[$cX, $cZ] = [$player->getFloorX() >> Chunk::COORD_BIT_SIZE, $player->getFloorZ() >> Chunk::COORD_BIT_SIZE];

				foreach ($player->getUsedChunks() as $chunkHash => $v) {
					Level::getXZ($chunkHash, $x, $z);

					if (abs($cX - $x) <= 8 && abs($cZ - $z) <= 8 && $x !== $cX && $z !== $cZ) {
						if (!isset($eligibleChunks[$chunkHash])) {
							$eligibleChunks[$chunkHash] = $chunkHash;
						}
					}
				}
			}

			$this->mobSpawner->findChunksForSpawning($this, $this->spawnHostileMobs, $this->spawnPeacefulMobs, $eligibleChunks);
		}

		$this->executeQueuedLightUpdates();

		if (count($this->changedBlocks) > 0) {
			if (count($this->players) > 0) {
				foreach ($this->changedBlocks as $index => $blocks) {
					if (empty($blocks)) { //blocks can be set normally and then later re-set with direct send
						continue;
					}
					unset($this->chunkCache[$index]);

					Level::getXZ($index, $chunkX, $chunkZ);

					if (count($blocks) > 512) {
						$chunk = $this->getChunk($chunkX, $chunkZ);
						foreach ($this->getChunkPlayers($chunkX, $chunkZ) as $p) {
							$p->onChunkChanged($chunk);
						}
					} else {
						$this->sendBlocks($this->getChunkPlayers($chunkX, $chunkZ), $blocks, UpdateBlockPacket::FLAG_ALL);
					}
				}
			} else {
				$this->chunkCache = [];
			}

			$this->changedBlocks = [];
		}

		$this->processChunkRequest();

		if ($this->sleepTicks > 0 && --$this->sleepTicks <= 0) {
			$this->checkSleep();
		}

		if (count($this->globalPackets) > 0) {
			if (count($this->players) > 0) {
				$this->server->batchPackets($this->players, $this->globalPackets);
			}
			$this->globalPackets = [];
		}

		foreach ($this->chunkPackets as $index => $entries) {
			Level::getXZ($index, $chunkX, $chunkZ);
			$chunkPlayers = $this->getChunkPlayers($chunkX, $chunkZ);
			if (count($chunkPlayers) > 0) {
				$this->server->batchPackets($chunkPlayers, $entries, false, false);
			}
		}

		$this->chunkPackets = [];
	}

	public function checkSleep() : void
	{
		if (count($this->players) === 0) {
			return;
		}

		$resetTime = true;
		foreach ($this->getPlayers() as $p) {
			if (!$p->isSleeping()) {
				$resetTime = false;
				break;
			}
		}

		if ($resetTime) {
			$time = $this->getTimeOfDay();

			if ($time >= Level::TIME_NIGHT && $time < Level::TIME_SUNRISE) {
				$this->setTime($this->getTime() + Level::TIME_FULL - $time);

				foreach ($this->getPlayers() as $p) {
					$p->stopSleep();
				}
			}
		}
	}

	public function setSleepTicks(int $ticks) : void
	{
		$this->sleepTicks = $ticks;
	}

	/**
	 * @param Player[]  $target
	 * @param Vector3[] $blocks
	 */
	public function sendBlocks(array $target, array $blocks, int $flags = UpdateBlockPacket::FLAG_NONE, bool $optimizeRebuilds = false) : void
	{
		if (count($target) === 0 || count($blocks) === 0) {
			return;
		}

		$resolvedBlocks = [];
		foreach ($blocks as $index => $block) {
			if (!($block instanceof Vector3)) {
				throw new TypeError("Expected Vector3 in blocks array, got " . (is_object($block) ? get_class($block) : gettype($block)));
			}
			if (!($block instanceof Block)) {
				$block = BlockFactory::fromFullBlock($this->getFullBlock((int) $block->x, (int) $block->y, (int) $block->z));
			}
			$resolvedBlocks[$index] = $block;
		}

		$targetsByProtocol = [];
		foreach ($target as $player) {
			$targetsByProtocol[$player->getProtocolVersion()][] = $player;
		}

		$convertor = BlockProtocolConvertor::getInstance();

		foreach ($targetsByProtocol as $protocol => $players) {
			$protocolBlocks = $resolvedBlocks;
			$positions = $blocks;
			$dynamicStates = null;
			if (DynamicBlockStateResolver::isSupported($protocol)) {
				$dynamicStates = DynamicBlockStateResolver::getInstance($protocol);
				//fences, panes and stairs next to the changed blocks may have to change their shape as well
				$seen = [];
				foreach ($positions as $pos) {
					$seen[Level::blockHash((int) $pos->x, (int) $pos->y, (int) $pos->z)] = true;
				}
				foreach ($positions as $pos) {
					foreach ([[1, 0], [-1, 0], [0, 1], [0, -1]] as [$dx, $dz]) {
						$nx = (int) $pos->x + $dx;
						$ny = (int) $pos->y;
						$nz = (int) $pos->z + $dz;
						$hash = Level::blockHash($nx, $ny, $nz);
						if (isset($seen[$hash]) || !$this->isChunkLoaded($nx >> Chunk::COORD_BIT_SIZE, $nz >> Chunk::COORD_BIT_SIZE)) {
							continue;
						}
						$seen[$hash] = true;
						$neighbour = $this->getFullBlock($nx, $ny, $nz);
						if ($dynamicStates->dependsOnNeighbours($dynamicStates->convert($neighbour))) {
							$neighbourBlock = BlockFactory::fromFullBlock($neighbour);
							$protocolBlocks[] = $neighbourBlock;
							$positions[] = new Vector3($nx, $ny, $nz);
						}
					}
				}
			}

			$packets = [];
			foreach ($protocolBlocks as $index => $block) {
				$pos = $positions[$index];
				$translated = $convertor->get($block, $protocol) ?? $block;

				$pk = new UpdateBlockPacket();
				$pk->x = (int) $pos->x;
				$pk->y = (int) $pos->y;
				$pk->z = (int) $pos->z;
				$pk->blockId = $translated->getId();
				$pk->blockMeta = $translated->getDamage();
				$pk->flags = $flags;
				if ($dynamicStates !== null && $dynamicStates->isDynamic($translated->getFullId())) {
					$pk->blockRuntimeId = $this->resolveNetworkBlockState($dynamicStates, $translated->getFullId(), $pk->x, $pk->y, $pk->z);
				}
				$packets[] = $pk;
			}

			if(count($players) === 1){
				$player = $players[0];
				foreach ($packets as $pk) {
					$player->sendDataPacket($pk);
				}
			} else {
				$this->server->batchPackets($players, $packets);
			}
		}
	}

	/**
	 * @param Vector3[] $blocks
	 * @phpstan-param list<Vector3> $blocks
	 *
	 * @return DataPacket[]
	 * @phpstan-return list<DataPacket>
	 */
	public function createBlockUpdatePackets(array $blocks, int $protocol) : array
	{
		$packets = [];

		foreach ($blocks as $b) {
			if (!($b instanceof Vector3)) {
				throw new TypeError("Expected Vector3 in blocks array, got " . (is_object($b) ? get_class($b) : gettype($b)));
			}

			$b = $b->floor();

			$fullBlock = $this->getBlockAt($b->x, $b->y, $b->z);
			$fullBlock = BlockProtocolConvertor::getInstance()->get($fullBlock, $protocol) ?? $fullBlock;
			$packet = new UpdateBlockPacket();
			$packet->x = $b->x;
			$packet->y = $b->y;
			$packet->z = $b->z;
			$packet->blockId = $fullBlock->getId();
			$packet->blockMeta = $fullBlock->getDamage();
			$packet->flags = UpdateBlockPacket::FLAG_NETWORK;
			$packet->dataLayerId = UpdateBlockPacket::DATA_LAYER_NORMAL;
			if (DynamicBlockStateResolver::isSupported($protocol)) {
				$dynamicStates = DynamicBlockStateResolver::getInstance($protocol);
				if ($dynamicStates->isDynamic($fullBlock->getFullId())) {
					$packet->blockRuntimeId = $this->resolveNetworkBlockState($dynamicStates, $fullBlock->getFullId(), (int) $b->x, (int) $b->y, (int) $b->z);
				}
			}
			$packets[] = $packet;

			$tile = $this->getTileAt($b->x, $b->y, $b->z);
			if ($tile instanceof Spawnable) {
				$packets[] = BlockActorDataPacket::create($b->x, $b->y, $b->z, $tile->getSerializedSpawnCompound($protocol));
			}
		}

		return $packets;
	}

	/**
	 * Network runtime ID of block states which depend on the position (neighbours, tile data) for newer clients, or
	 * null if the plain id:meta mapping applies. Never loads chunks.
	 */
	private function resolveNetworkBlockState(DynamicBlockStateResolver $resolver, int $fullState, int $x, int $y, int $z) : ?int
	{
		return $resolver->resolve(
			$fullState, $x, $y, $z,
			fn(int $x, int $y, int $z) : int => $this->isChunkLoaded($x >> Chunk::COORD_BIT_SIZE, $z >> Chunk::COORD_BIT_SIZE) ? $this->getFullBlock($x, $y, $z) : Block::AIR << Block::INTERNAL_METADATA_BITS,
			function (int $x, int $y, int $z) : ?int {
				if (!$this->isChunkLoaded($x >> Chunk::COORD_BIT_SIZE, $z >> Chunk::COORD_BIT_SIZE)) {
					return null;
				}
				$tile = $this->getTileAt($x, $y, $z);
				return $tile instanceof TileSkull ? $tile->getType() : null;
			}
		);
	}

	public function clearCache(bool $force = false) : void{
		if($force){
			$this->blockCache = [];
			$this->blockCacheSize = 0;
			$this->blockCollisionBoxCache = [];
			$this->chunkCache = [];
		}else{
			//Recalculate this when we're asked - blockCacheSize may be higher than the real size
			$this->blockCacheSize = 0;
			foreach($this->blockCache as $list){
				$this->blockCacheSize += count($list);
				if($this->blockCacheSize > self::BLOCK_CACHE_SIZE_CAP){
					$this->blockCache = [];
					$this->blockCacheSize = 0;
					break;
				}
			}

			$count = 0;
			foreach($this->blockCollisionBoxCache as $list){
				$count += count($list);
				if($count > self::BLOCK_CACHE_SIZE_CAP){
					//TODO: Is this really the best logic?
					$this->blockCollisionBoxCache = [];
					break;
				}
			}

			$chunkCacheCount = 0;
			foreach($this->chunkCache as $protocolCaches){
				$chunkCacheCount += count($protocolCaches);
			}
			if($chunkCacheCount > 512){
				$this->chunkCache = [];
			}
		}
	}

	private function trimBlockCache() : void{
		$before = $this->blockCacheSize;
		//Since PHP maintains key order, earliest in foreach should be the oldest entries
		//Older entries are less likely to be hot, so destroying these should usually have the lowest impact on performance
		foreach($this->blockCache as $chunkHash => $blocks){
			unset($this->blockCache[$chunkHash]);
			$this->blockCacheSize -= count($blocks);
			if($this->blockCacheSize < self::BLOCK_CACHE_SIZE_CAP){
				break;
			}
		}
	}

	public function clearChunkCache(int $chunkX, int $chunkZ) : void
	{
		unset($this->chunkCache[Level::chunkHash($chunkX, $chunkZ)]);
	}

	/**
	 * @return true[] fullID => dummy
	 * @phpstan-return array<int, true>
	 */
	public function getRandomTickedBlocks() : array
	{
		return $this->randomTickBlocks;
	}

	public function addRandomTickedBlock(Block $block) : void
	{
		if ($block instanceof UnknownBlock) {
			throw new InvalidArgumentException("Cannot do random-tick on unknown block");
		}
		$this->randomTickBlocks[$block->getFullId()] = true;
		$this->randomTickBlocksTimings[$block->getFullId()] = $this->timings->getRandomTickBlocks($block::class);
	}

	public function removeRandomTickedBlock(Block $block) : void
	{
		unset($this->randomTickBlocks[$block->getFullId()]);
		unset($this->randomTickBlocksTimings[$block->getFullId()]);
	}

	/**
	 * Returns the radius of chunks to be ticked around each player. This is referred to as "simulation distance" in the
	 * Minecraft: Bedrock world options screen.
	 */
	public function getChunkTickRadius() : int
	{
		return $this->chunkTickRadius;
	}

	/**
	 * Sets the radius of chunks ticked around each player. This may not take effect immediately, since each player
	 * needs to recalculate their tick radius.
	 */
	public function setChunkTickRadius(int $radius) : void
	{
		$this->chunkTickRadius = $radius;
	}

	private function tickChunks() : void{
		if ($this->chunksPerTick <= 0 || count($this->loaders) === 0) {
			$this->chunkTickList = [];
			return;
		}

		$chunksPerLoader = min(200, max(1, (int) ((($this->chunksPerTick - count($this->loaders)) / count($this->loaders)) + 0.5)));
		$randRange = 3 + $chunksPerLoader / 30;
		$randRange = (int) (min($randRange, $this->chunkTickRadius));

		foreach ($this->loaders as $loader) {
			$chunkX = (int) floor($loader->getX()) >> Chunk::COORD_BIT_SIZE;
			$chunkZ = (int) floor($loader->getZ()) >> Chunk::COORD_BIT_SIZE;

			$index = Level::chunkHash($chunkX, $chunkZ);
			$existingLoaders = max(0, $this->chunkTickList[$index] ?? 0);
			$this->chunkTickList[$index] = $existingLoaders + 1;
			for ($chunk = 0; $chunk < $chunksPerLoader; ++$chunk) {
				$dx = mt_rand(-$randRange, $randRange);
				$dz = mt_rand(-$randRange, $randRange);
				$hash = Level::chunkHash($dx + $chunkX, $dz + $chunkZ);
				if (!isset($this->chunkTickList[$hash]) && isset($this->chunks[$hash])) {
					$this->chunkTickList[$hash] = -1;
				}
			}
		}

		foreach ($this->chunkTickList as $index => $loaders) {
			Level::getXZ($index, $chunkX, $chunkZ);
			for ($cx = -1; $cx <= 1; ++$cx) {
				for ($cz = -1; $cz <= 1; ++$cz) {
					if (!isset($this->chunks[Level::chunkHash($chunkX + $cx, $chunkZ + $cz)])) {
						unset($this->chunkTickList[$index]);
						goto skip_to_next; //no "continue 3" thanks!
					}
				}
			}

			if ($loaders <= 0) {
				unset($this->chunkTickList[$index]);
			}

			$chunk = $this->chunks[$index];
			foreach ($chunk->getEntities() as $entity) {
				$entity->scheduleUpdate();
			}

			$this->timings->tickBlocks->startTiming();
			foreach ($chunk->getSubChunks() as $Y => $subChunk) {
				if (!($subChunk instanceof EmptySubChunk)) {
					$k = 0;
					for ($i = 0; $i < $this->tickedBlocksPerSubchunkPerTick; ++$i) {
						if (($i % 5) === 0) {
							//60 bits will be used by 5 blocks (12 bits each)
							$k = mt_rand(0, (1 << 60) - 1);
						}
						$x = $k & SubChunk::COORD_MASK;
						$y = ($k >> SubChunk::COORD_BIT_SIZE) & SubChunk::COORD_MASK;
						$z = ($k >> (SubChunk::COORD_BIT_SIZE * 2)) & SubChunk::COORD_MASK;
						$k >>= (SubChunk::COORD_BIT_SIZE * 3);

						$state = $subChunk->getFullBlock($x, $y, $z);

						if (isset($this->randomTickBlocks[$state])) {
							$blockTimings = $this->randomTickBlocksTimings[$state];

							$blockTimings->startTiming();
							$block = BlockFactory::fromFullBlock($state);
							$block->position(new Position($chunkX * Chunk::EDGE_LENGTH + $x, ($Y << SubChunk::COORD_BIT_SIZE) + $y, $chunkZ * Chunk::EDGE_LENGTH + $z, $this));
							$block->onRandomTick();
							$blockTimings->stopTiming();
						}
					}
				}
			}
			$this->timings->tickBlocks->stopTiming();

			skip_to_next: //dummy label to break out of nested loops
		}

		if ($this->clearChunksOnTick) {
			$this->chunkTickList = [];
		}
	}

	/**
	 * @return mixed[]
	 */
	public function __debugInfo() : array
	{
		return [];
	}

	public function save(bool $force = false) : bool
	{

		if (!$this->getAutoSave() && !$force) {
			return false;
		}

		(new LevelSaveEvent($this))->call();

		$timings = $this->timings->syncDataSave;
		$timings->startTiming();

		$this->provider->getLevelData()->setTime($this->time);
		$this->provider->getLevelData()->setGameRules($this->gameRules);
		$this->saveChunks();
		$this->provider->getLevelData()->save();
		$this->portalIndex?->save();

		$timings->stopTiming();

		return true;
	}

	/**
	 * Nether portals of this world, stored in portals.json in the world folder
	 */
	public function getPortalIndex() : PortalIndex
	{
		return $this->portalIndex ??= new PortalIndex(rtrim($this->provider->getPath(), "/\\") . DIRECTORY_SEPARATOR . PortalIndex::FILE_NAME);
	}

	public function saveChunks() : void
	{
		$this->timings->syncChunkSave->startTiming();
		try {
			foreach ($this->chunks as $chunk) {
				if (($chunk->hasChanged() || count($chunk->getTiles()) > 0 || count($chunk->getSavableEntities()) > 0) && $chunk->isGenerated()) {
					$this->provider->saveChunk($chunk);
					$chunk->setChanged(false);
				}
			}
		} finally {
			$this->timings->syncChunkSave->stopTiming();
		}
	}

	/**
	 * Schedules a block update to be executed after the specified number of ticks.
	 * Blocks will be updated with the scheduled update type.
	 */
	public function scheduleDelayedBlockUpdate(Vector3 $pos, int $delay) : void
	{
		if (
			!$this->isInWorld($pos->x, $pos->y, $pos->z) ||
			(isset($this->scheduledBlockUpdateQueueIndex[$index = Level::blockHash($pos->x, $pos->y, $pos->z)]) && $this->scheduledBlockUpdateQueueIndex[$index] <= $delay)
		) {
			return;
		}
		$this->scheduledBlockUpdateQueueIndex[$index] = $delay;
		$this->scheduledBlockUpdateQueue->insert(new Vector3((int) $pos->x, (int) $pos->y, (int) $pos->z), $delay + $this->server->getTick());
	}

	/**
	 * Schedules the blocks around the specified position to be updated at the end of this tick.
	 * Blocks will be updated with the normal update type.
	 */
	public function scheduleNeighbourBlockUpdates(Vector3 $pos) : void
	{
		$pos = $pos->floor();

		for ($i = 0; $i <= 5; ++$i) {
			$side = $pos->getSide($i);
			if ($this->isInWorld($side->x, $side->y, $side->z)) {
				$hash = Level::blockHash($side->x, $side->y, $side->z);
				$this->neighbourBlockUpdateQueue->enqueue($hash);
			}
		}
	}

	public function isLiquidInBoundingBox(AxisAlignedBB $bb, Liquid $material) : bool
	{
		$minX = (int) floor($bb->minX);
		$minY = (int) floor($bb->minY);
		$minZ = (int) floor($bb->minZ);
		$maxX = (int) floor($bb->maxX + 1);
		$maxY = (int) floor($bb->maxY + 1);
		$maxZ = (int) floor($bb->maxZ + 1);

		for ($x = $minX; $x < $maxX; ++$x) {
			for ($y = $minY; $y < $maxY; ++$y) {
				for ($z = $minZ; $z < $maxZ; ++$z) {
					$block = $this->getBlockAt($x, $y, $z);

					if ($block instanceof $material) {
						$j2 = $block->getDamage();
						$d0 = $y + 1;

						if ($j2 < 8) {
							$d0 -= $j2 / 8;
						}

						if ($d0 >= $bb->minY) {
							return true;
						}
					}
				}
			}
		}

		return false;
	}

	/**
	 * @return Block[]
	 * @phpstan-return list<Block>
	 */
	public function getCollisionBlocks(AxisAlignedBB $bb, bool $targetFirst = false) : array
	{
		$minX = (int) floor($bb->minX - 1);
		$minY = (int) floor($bb->minY - 1);
		$minZ = (int) floor($bb->minZ - 1);
		$maxX = (int) floor($bb->maxX + 1);
		$maxY = (int) floor($bb->maxY + 1);
		$maxZ = (int) floor($bb->maxZ + 1);

		$collides = [];

		if ($targetFirst) {
			for ($z = $minZ; $z <= $maxZ; ++$z) {
				for ($x = $minX; $x <= $maxX; ++$x) {
					for ($y = $minY; $y <= $maxY; ++$y) {
						$block = $this->getBlockAt($x, $y, $z);
						if (!$block->canPassThrough() && $block->collidesWithBB($bb)) {
							return [$block];
						}
					}
				}
			}
		} else {
			for ($z = $minZ; $z <= $maxZ; ++$z) {
				for ($x = $minX; $x <= $maxX; ++$x) {
					for ($y = $minY; $y <= $maxY; ++$y) {
						$block = $this->getBlockAt($x, $y, $z);
						if (!$block->canPassThrough() && $block->collidesWithBB($bb)) {
							$collides[] = $block;
						}
					}
				}
			}
		}

		return $collides;
	}

	/**
	 * @return AxisAlignedBB[]
	 * @phpstan-return list<AxisAlignedBB>
	 */
	public function getBlockCollisionBoxes(AxisAlignedBB $bb) : array
	{
		$minX = (int) floor($bb->minX - 1);
		$minY = (int) floor($bb->minY - 1);
		$minZ = (int) floor($bb->minZ - 1);
		$maxX = (int) floor($bb->maxX + 1);
		$maxY = (int) floor($bb->maxY + 1);
		$maxZ = (int) floor($bb->maxZ + 1);

		$collides = [];

		for ($z = $minZ; $z <= $maxZ; ++$z) {
			for ($x = $minX; $x <= $maxX; ++$x) {
				$chunkPosHash = Level::chunkHash($x >> Chunk::COORD_BIT_SIZE, $z >> Chunk::COORD_BIT_SIZE);
				for ($y = $minY; $y <= $maxY; ++$y) {
					$block = $this->getBlockAt($x, $y, $z);
					if (!$block->canPassThrough()) {
						$relativeBlockHash = Level::chunkBlockHash($x, $y, $z);
						$boxes = $this->blockCollisionBoxCache[$chunkPosHash][$relativeBlockHash] ??= $block->getCollisionBoxes();
						foreach ($boxes as $blockBB) {
							if ($blockBB->intersectsWith($bb)) {
								$collides[] = $blockBB;
							}
						}
					}
				}
			}
		}

		return $collides;
	}

	/**
	 * @return AxisAlignedBB[]
	 */
	public function getCollisionCubes(Entity $entity, AxisAlignedBB $bb, bool $entities = true) : array
	{
		$collides = $this->getBlockCollisionBoxes($bb);
		if ($entities) {
			foreach ($this->getCollidingEntities($bb->expandedCopy(0.25, 0.25, 0.25), $entity) as $ent) {
				$collides[] = clone $ent->boundingBox;
			}
		}

		return $collides;
	}

	/**
	 * Computes the percentage of a circle away from noon the sun is currently at. This can be multiplied by 2 * M_PI to
	 * get an angle in radians, or by 360 to get an angle in degrees.
	 */
	public function computeSunAnglePercentage() : float
	{
		$timeProgress = ($this->time % self::TIME_FULL) / self::TIME_FULL;

		//0.0 needs to be high noon, not dusk
		$sunProgress = $timeProgress + ($timeProgress < 0.25 ? 0.75 : -0.25);

		//Offset the sun progress to be above the horizon longer at dusk and dawn
		//this is roughly an inverted sine curve, which pushes the sun progress back at dusk and forwards at dawn
		$diff = (((1 - ((cos($sunProgress * M_PI) + 1) / 2)) - $sunProgress) / 3);

		return $sunProgress + $diff;
	}

	/**
	 * Returns the percentage of a circle away from noon the sun is currently at.
	 */
	public function getSunAnglePercentage() : float
	{
		return $this->sunAnglePercentage;
	}

	/**
	 * Returns the current sun angle in radians.
	 */
	public function getSunAngleRadians() : float
	{
		return $this->sunAnglePercentage * 2 * M_PI;
	}

	/**
	 * Returns the current sun angle in degrees.
	 */
	public function getSunAngleDegrees() : float
	{
		return $this->sunAnglePercentage * 360.0;
	}

	/**
	 * Computes how many points of sky light is subtracted based on the current time. Used to offset raw chunk sky light
	 * to get a real light value.
	 */
	public function computeSkyLightReduction() : int
	{
		$percentage = max(0, min(1, -(cos($this->getSunAngleRadians()) * 2 - 0.5)));

		//TODO: check rain and thunder level

		return (int) ($percentage * 11);
	}

	/**
	 * Returns how many points of sky light is subtracted based on the current time.
	 */
	public function getSkyLightReduction() : int
	{
		return $this->skyLightReduction;
	}

	/**
	 * Returns the highest available level of any type of light at the given coordinates, adjusted for the current
	 * weather and time of day.
	 */
	public function getFullLight(Vector3 $pos) : int
	{
		return $this->getFullLightAt($pos->x, $pos->y, $pos->z);
	}

	/**
	 * Returns the highest available level of any type of light at the given coordinates, adjusted for the current
	 * weather and time of day.
	 */
	public function getFullLightAt(int $x, int $y, int $z) : int
	{
		$skyLight = $this->getRealBlockSkyLightAt($x, $y, $z);
		if($skyLight < 15){
			return max($skyLight, $this->getBlockLightAt($x, $y, $z));
		}else{
			return $skyLight;
		}
	}

	/**
	 * Returns the highest level of any type of light at, or adjacent to, the given coordinates, adjusted for the
	 * current weather and time of day.
	 */
	public function getHighestAdjacentFullLightAt(int $x, int $y, int $z) : int{
		return $this->getHighestAdjacentLight($x, $y, $z, $this->getFullLightAt(...));
	}

	/**
	 * Returns the highest potential level of any type of light at the target coordinates.
	 * This is not affected by weather or time of day.
	 */
	public function getPotentialLightAt(int $x, int $y, int $z) : int{
		return max($this->getPotentialBlockSkyLightAt($x, $y, $z), $this->getBlockLightAt($x, $y, $z));
	}

	/**
	 * Returns the highest potential level of sky light at the target coordinates, regardless of the time of day or
	 * weather conditions.
	 *
	 * @return int 0-15
	 */
	public function getPotentialBlockSkyLightAt(int $x, int $y, int $z) : int{
		if(!$this->isInWorld($x, $y, $z)){
			return $y >= self::Y_MAX ? 15 : 0;
		}
		if(($chunk = $this->getChunk($x >> Chunk::COORD_BIT_SIZE, $z >> Chunk::COORD_BIT_SIZE)) !== null && $chunk->isLightPopulated() === true){
			return $chunk->getSubChunk($y >> Chunk::COORD_BIT_SIZE)->getBlockSkyLight($x & SubChunk::COORD_MASK, $y & SubChunk::COORD_MASK, $z & SubChunk::COORD_MASK);
		}
		return 0; //TODO: this should probably throw instead (light not calculated yet)
	}

	/**
	 * Returns the sky light level at the specified coordinates, offset by the current time and weather.
	 *
	 * @return int 0-15
	 */
	public function getRealBlockSkyLightAt(int $x, int $y, int $z) : int
	{
		$light = $this->getPotentialBlockSkyLightAt($x, $y, $z) - $this->skyLightReduction;
		return $light < 0 ? 0 : $light;
	}

	/**
	 * Sets the raw block skylight level.
	 *
	 * @param int $level 0-15
	 */
	public function setBlockSkyLightAt(int $x, int $y, int $z, int $level) : void
	{
		$this->getChunk($x >> Chunk::COORD_BIT_SIZE, $z >> Chunk::COORD_BIT_SIZE, true)->setBlockSkyLight($x & 0x0f, $y, $z & 0x0f, $level & 0x0f);
	}

	/**
	 * Gets the raw block light level
	 *
	 * @return int 0-15
	 */
	public function getBlockLightAt(int $x, int $y, int $z) : int
	{
		return $this->getChunk($x >> Chunk::COORD_BIT_SIZE, $z >> Chunk::COORD_BIT_SIZE, true)->getBlockLight($x & 0x0f, $y, $z & 0x0f);
	}

	public function updateAllLight(Vector3 $pos) : void
	{
		$this->updateBlockSkyLight($pos->x, $pos->y, $pos->z);
		$this->updateBlockLight($pos->x, $pos->y, $pos->z);
	}

	/**
	 * @phpstan-param \Closure(int $x, int $y, int $z) : int $lightGetter
	 */
	private function getHighestAdjacentLight(int $x, int $y, int $z, \Closure $lightGetter) : int{
		$max = 0;
		foreach(Facing::OFFSET as [$offsetX, $offsetY, $offsetZ]){
			$x1 = $x + $offsetX;
			$y1 = $y + $offsetY;
			$z1 = $z + $offsetZ;
			if(
				!$this->isInWorld($x1, $y1, $z1) ||
				($chunk = $this->getChunk($x1 >> Chunk::COORD_BIT_SIZE, $z1 >> Chunk::COORD_BIT_SIZE)) === null ||
				$chunk->isLightPopulated() !== true
			){
				continue;
			}
			$max = max($max, $lightGetter($x1, $y1, $z1));
		}
		return $max;
	}

	/**
	 * Returns the highest potential level of sky light in the positions adjacent to the specified block coordinates.
	 */
	public function getHighestAdjacentPotentialBlockSkyLight(int $x, int $y, int $z) : int{
		return $this->getHighestAdjacentLight($x, $y, $z, $this->getPotentialBlockSkyLightAt(...));
	}

	/**
	 * Returns the highest block sky light available in the positions adjacent to the given coordinates, adjusted for
	 * the world's current time of day and weather conditions.
	 */
	public function getHighestAdjacentRealBlockSkyLight(int $x, int $y, int $z) : int{
		return $this->getHighestAdjacentPotentialBlockSkyLight($x, $y, $z) - $this->skyLightReduction;
	}

	/**
	 * @deprecated
	 */
	public function getHighestAdjacentBlockSkyLight(int $x, int $y, int $z) : int{
		return $this->getHighestAdjacentPotentialBlockSkyLight($x, $y, $z);
	}

	public function updateBlockSkyLight(int $x, int $y, int $z) : void
	{
		$this->timings->doBlockSkyLightUpdates->startTiming();

		$chunk = $this->getChunk($x >> Chunk::COORD_BIT_SIZE, $z >> Chunk::COORD_BIT_SIZE, true);
		$oldHeightMap = $this->getHeightMap($x, $z);
		$sourceId = $chunk->getBlockId($x & 0x0f, $y, $z & 0x0f);

		$yPlusOne = $y + 1;

		if ($yPlusOne === $oldHeightMap) { //Block changed directly beneath the heightmap. Check if a block was removed or changed to a different light-filter.
			$newHeightMap = $chunk->recalculateHeightMapColumn($x & 0x0f, $z & 0x0f);
		} elseif ($yPlusOne > $oldHeightMap) { //Block changed above the heightmap.
			if (BlockFactory::$lightFilter[$sourceId] > 1 || BlockFactory::$diffusesSkyLight[$sourceId]) {
				$this->setHeightMap($x, $z, $yPlusOne);
				$newHeightMap = $yPlusOne;
			} else { //Block changed which has no effect on direct sky light, for example placing or removing glass.
				$this->timings->doBlockSkyLightUpdates->stopTiming();
				return;
			}
		} else { //Block changed below heightmap
			$newHeightMap = $oldHeightMap;
		}

		if ($this->skyLightUpdate === null) {
			$this->skyLightUpdate = new SkyLightUpdate($this);
		}
		if ($newHeightMap > $oldHeightMap) { //Heightmap increase, block placed, remove sky light
			for ($i = $y; $i >= $oldHeightMap; --$i) {
				$this->skyLightUpdate->setAndUpdateLight($x, $i, $z, 0); //Remove all light beneath, adjacent recalculation will handle the rest.
			}
		} elseif ($newHeightMap < $oldHeightMap) { //Heightmap decrease, block changed or removed, add sky light
			for ($i = $y; $i >= $newHeightMap; --$i) {
				$this->skyLightUpdate->setAndUpdateLight($x, $i, $z, 15);
			}
		} else { //No heightmap change, block changed "underground"
			$this->skyLightUpdate->setAndUpdateLight($x, $y, $z, max(0, $this->getHighestAdjacentPotentialBlockSkyLight($x, $y, $z) - BlockFactory::$lightFilter[$sourceId]));
		}

		$this->timings->doBlockSkyLightUpdates->stopTiming();
	}

	/**
	 * Returns the highest block light level available in the positions adjacent to the specified block coordinates.
	 */
	public function getHighestAdjacentBlockLight(int $x, int $y, int $z) : int
	{
		return max([
			($this->getChunk($x + 1 >> Chunk::COORD_BIT_SIZE, $z >> Chunk::COORD_BIT_SIZE, true)->getBlockLight($x + 1 & 0x0f, $y, $z & 0x0f)),
			($this->getChunk($x - 1 >> Chunk::COORD_BIT_SIZE, $z >> Chunk::COORD_BIT_SIZE, true)->getBlockLight($x - 1 & 0x0f, $y, $z & 0x0f)),
			($this->getChunk($x >> Chunk::COORD_BIT_SIZE, $z >> Chunk::COORD_BIT_SIZE, true)->getBlockLight($x & 0x0f, $y + 1, $z & 0x0f)),
			($this->getChunk($x >> Chunk::COORD_BIT_SIZE, $z >> Chunk::COORD_BIT_SIZE, true)->getBlockLight($x & 0x0f, $y - 1, $z & 0x0f)),
			($this->getChunk($x >> Chunk::COORD_BIT_SIZE, $z + 1 >> Chunk::COORD_BIT_SIZE, true)->getBlockLight($x & 0x0f, $y, $z + 1 & 0x0f)),
			($this->getChunk($x >> Chunk::COORD_BIT_SIZE, $z - 1 >> Chunk::COORD_BIT_SIZE, true)->getBlockLight($x & 0x0f, $y, $z - 1 & 0x0f))
		]);
	}

	public function updateBlockLight(int $x, int $y, int $z) : void
	{
		$this->timings->doBlockLightUpdates->startTiming();

		$id = ($this->getChunk($x >> Chunk::COORD_BIT_SIZE, $z >> Chunk::COORD_BIT_SIZE, true)->getBlockId($x & 0x0f, $y, $z & 0x0f));
		$newLevel = max(BlockFactory::$light[$id], $this->getHighestAdjacentBlockLight($x, $y, $z) - BlockFactory::$lightFilter[$id]);

		if ($this->blockLightUpdate === null) {
			$this->blockLightUpdate = new BlockLightUpdate($this);
		}
		$this->blockLightUpdate->setAndUpdateLight($x, $y, $z, $newLevel);

		$this->timings->doBlockLightUpdates->stopTiming();
	}

	private function executeQueuedLightUpdates() : void
	{
		if ($this->blockLightUpdate !== null) {
			$this->timings->doBlockLightUpdates->startTiming();
			$this->blockLightUpdate->execute();
			$this->blockLightUpdate = null;
			$this->timings->doBlockLightUpdates->stopTiming();
		}

		if ($this->skyLightUpdate !== null) {
			$this->timings->doBlockSkyLightUpdates->startTiming();
			$this->skyLightUpdate->execute();
			$this->skyLightUpdate = null;
			$this->timings->doBlockSkyLightUpdates->stopTiming();
		}
	}

	public function isInWorld(int $x, int $y, int $z) : bool
	{
		return (
			$x <= INT32_MAX && $x >= INT32_MIN &&
			$y < $this->worldHeight && $y >= self::Y_MIN &&
			$z <= INT32_MAX && $z >= INT32_MIN
		);
	}

	public function isFullBlock(Vector3 $pos) : bool
	{
		if ($pos instanceof Block) {
			if ($pos->isSolid()) {
				return true;
			}
			$bb = $pos->getBoundingBox();
		} else {
			$bb = $this->getBlock($pos)->getBoundingBox();
		}

		return $bb !== null && $bb->getAverageEdgeLength() >= 1;
	}

	/**
	 * @return int bitmap, (id << 4) | data
	 */
	public function getFullBlock(int $x, int $y, int $z) : int
	{
		$chunk = $this->getChunk($x >> Chunk::COORD_BIT_SIZE, $z >> Chunk::COORD_BIT_SIZE);
		if ($chunk === null) {
			return Block::AIR << Block::INTERNAL_METADATA_BITS;
		}

		return $chunk->getFullBlock($x & 0x0f, $y, $z & 0x0f);
	}

	/**
	 * Gets the Block object at the Vector3 location. This method wraps around {@link getBlockAt}, converting the
	 * vector components to integers.
	 *
	 * Note: If you're using this for performance-sensitive code, and you're guaranteed to be supplying ints in the
	 * specified vector, consider using {@link getBlockAt} instead for better performance.
	 *
	 * @param bool $cached     Whether to use the block cache for getting the block (faster, but may be inaccurate)
	 * @param bool $addToCache Whether to cache the block object created by this method call.
	 */
	public function getBlock(Vector3 $pos, bool $cached = true, bool $addToCache = true) : Block
	{
		return $this->getBlockAt((int) floor($pos->x), (int) floor($pos->y), (int) floor($pos->z), $cached, $addToCache);
	}

	/**
	 * Gets the Block object at the specified coordinates.
	 *
	 * Note for plugin developers: If you are using this method a lot (thousands of times for many positions for
	 * example), you may want to set addToCache to false to avoid using excessive amounts of memory.
	 *
	 * @param bool $cached     Whether to use the block cache for getting the block (faster, but may be inaccurate)
	 * @param bool $addToCache Whether to cache the block object created by this method call.
	 */
	public function getBlockAt(int $x, int $y, int $z, bool $cached = true, bool $addToCache = true) : Block
	{
		$relativeBlockHash = null;
		$chunkHash = Level::chunkHash($x >> Chunk::COORD_BIT_SIZE, $z >> Chunk::COORD_BIT_SIZE);

		if ($this->isInWorld($x, $y, $z)) {
			$relativeBlockHash = Level::chunkBlockHash($x, $y, $z);

			if ($cached && isset($this->blockCache[$chunkHash][$relativeBlockHash])) {
				return $this->blockCache[$chunkHash][$relativeBlockHash];
			}

			$chunk = $this->chunks[$chunkHash] ?? null;
			if ($chunk !== null) {
				$block = BlockFactory::fromFullBlock($chunk->getFullBlock($x & Chunk::COORD_MASK, $y, $z & Chunk::COORD_MASK));
			} else {
				$addToCache = false;
				$block = Block::get(Block::AIR);
			}
		} else {
			$block = Block::get(Block::AIR);
		}

		$block->x = $x;
		$block->y = $y;
		$block->z = $z;
		$block->level = $this;

		if($addToCache && $relativeBlockHash !== null){
			$this->blockCache[$chunkHash][$relativeBlockHash] = $block;

			if(++$this->blockCacheSize >= self::BLOCK_CACHE_SIZE_CAP){
				$this->trimBlockCache();
			}
		}

		return $block;
	}

	/**
	 * Sets the block at the given Vector3 coordinates.
	 *
	 * @throws InvalidArgumentException if the position is out of the world bounds
	 */
	public function setBlock(Vector3 $pos, Block $block, bool $direct = false, bool $update = true) : bool
	{
		return $this->setBlockAt((int) floor($pos->x), (int) floor($pos->y), (int) floor($pos->z), $block, $direct, $update);
	}

	/**
	 * Sets the block at the given coordinates.
	 *
	 * If $update is true, it'll get the neighbour blocks (6 sides) and update them, and also update local lighting.
	 * If you are doing big changes, you might want to set this to false, then update manually.
	 *
	 * @throws InvalidArgumentException if the position is out of the world bounds
	 */
	public function setBlockAt(int $x, int $y, int $z, Block $block, bool $direct = false, bool $update = true) : bool
	{
		if (!$this->isInWorld($x, $y, $z)) {
			return false;
		}
		$chunkX = $x >> Chunk::COORD_BIT_SIZE;
		$chunkZ = $z >> Chunk::COORD_BIT_SIZE;
		if (!$this->loadChunk($chunkX, $chunkZ)) { //current expected behaviour is to try to load the terrain synchronously
			return false;
		}

		try {
			$this->timings->setBlock->startTiming();

			if ($this->getChunk($x >> Chunk::COORD_BIT_SIZE, $z >> Chunk::COORD_BIT_SIZE, true)->setFullBlock($x & Chunk::COORD_MASK, $y, $z & Chunk::COORD_MASK, $block->getFullId())) {

				$block = clone $block;

				$block->position(new Position($x, $y, $z, $this));
				$block->clearCaches();

				$chunkHash = Level::chunkHash($chunkX, $chunkZ);
				$relativeBlockHash = Level::chunkBlockHash($x, $y, $z);

				unset($this->blockCache[$chunkHash][$relativeBlockHash]);
				$this->blockCacheSize--;
				unset($this->blockCollisionBoxCache[$chunkHash][$relativeBlockHash]);
				//blocks like fences have collision boxes that reach into neighbouring blocks, so we need to invalidate the
				//caches for those blocks as well
				foreach(Facing::OFFSET as [$offsetX, $offsetY, $offsetZ]){
					$sideChunkPosHash = Level::chunkHash(($x + $offsetX) >> Chunk::COORD_BIT_SIZE, ($z + $offsetZ) >> Chunk::COORD_BIT_SIZE);
					$sideChunkBlockHash = Level::chunkBlockHash($x + $offsetX, $y + $offsetY, $z + $offsetZ);
					unset($this->blockCollisionBoxCache[$sideChunkPosHash][$sideChunkBlockHash]);
					if ($sideChunkPosHash !== $chunkHash) {
						//neighbour-dependent block states (1.26.50+) of the adjacent chunk may depend on this block
						unset($this->chunkCache[$sideChunkPosHash]);
					}
				}

				if ($direct) {
					$this->sendBlocks($this->getChunkPlayers($chunkX, $chunkZ), [$block], UpdateBlockPacket::FLAG_ALL_PRIORITY);
					unset($this->chunkCache[$chunkHash], $this->changedBlocks[$chunkHash][$relativeBlockHash]);
				} else {
					if (!isset($this->changedBlocks[$chunkHash])) {
						$this->changedBlocks[$chunkHash] = [];
					}

					$this->changedBlocks[$chunkHash][$relativeBlockHash] = $block;
				}

				foreach ($this->getChunkLoaders($chunkX, $chunkZ) as $loader) {
					$loader->onBlockChanged($block);
				}

				if ($update) {
					$this->updateAllLight($block);

					$ev = new BlockUpdateEvent($block);
					$ev->call();
					if (!$ev->isCancelled()) {
						foreach ($this->getNearbyEntities(new AxisAlignedBB($block->x - 1, $block->y - 1, $block->z - 1, $block->x + 2, $block->y + 2, $block->z + 2)) as $entity) {
							$entity->onNearbyBlockChange();
						}
						$ev->getBlock()->onNearbyBlockChange();
						$this->scheduleNeighbourBlockUpdates(new Vector3($x, $y, $z));
					}
				}

				return true;
			}
		} finally {
			$this->timings->setBlock->stopTiming();
		}

		return false;
	}

	/**
	 * @return ItemEntity|null
	 */
	public function dropItem(Vector3 $source, Item $item, ?Vector3 $motion = null, int $delay = 10)
	{
		$motion = $motion ?? new Vector3(Utils::getRandomFloat() * 0.2 - 0.1, 0.2, Utils::getRandomFloat() * 0.2 - 0.1);
		$itemTag = $item->nbtSerialize();
		$itemTag->setName("Item");

		if (!$item->isNull()) {
			$nbt = Entity::createBaseNBT($source, $motion, Utils::getRandomFloat() * 360, 0);
			$nbt->setShort("Health", 5);
			$nbt->setShort("PickupDelay", $delay);
			$nbt->setTag($itemTag);
			$itemEntity = Entity::createEntity("Item", $this, $nbt);

			if ($itemEntity instanceof ItemEntity) {
				$itemEntity->spawnToAll();

				return $itemEntity;
			}
		}
		return null;
	}

	/**
	 * Drops XP orbs into the world for the specified amount, splitting the amount into several orbs if necessary.
	 *
	 * @return ExperienceOrb[]
	 */
	public function dropExperience(Vector3 $pos, int $amount) : array
	{
		/** @var ExperienceOrb[] $orbs */
		$orbs = [];

		foreach (ExperienceOrb::splitIntoOrbSizes($amount) as $split) {
			$nbt = Entity::createBaseNBT(
				$pos,
				null,
				Utils::getRandomFloat() * 360,
				0
			);
			$nbt->setShort(ExperienceOrb::TAG_VALUE_PC, $split);

			$orb = Entity::createEntity("XPOrb", $this, $nbt);
			if ($orb === null) {
				continue;
			}

			$orb->setMotion(new Vector3((Utils::getRandomFloat() * 0.2 - 0.1) * 2, Utils::getRandomFloat() * 0.4, (Utils::getRandomFloat() * 0.2 - 0.1) * 2));
			$orb->spawnToAll();
			if ($orb instanceof ExperienceOrb) {
				$orbs[] = $orb;
			}
		}

		return $orbs;
	}

	/**
	 * Tries to break a block using a item, including Player time checks if available
	 * It'll try to lower the durability if Item is a tool, and set it to Air if broken.
	 *
	 * @param Item $item reference parameter (if null, can break anything)
	 * @phpstan-param-out Item $item
	 */
	public function useBreakOn(Vector3 $vector, ?Item &$item = null, ?Player $player = null, bool $createParticles = false) : bool
	{
		$vector = $vector->floor();
		$target = $this->getBlock($vector);
		$affectedBlocks = $target->getAffectedBlocks();

		if ($item === null) {
			$item = ItemFactory::get(Item::AIR, 0, 0);
		}

		$drops = [];
		if ($player === null || !$player->isCreative()) {
			$drops = array_merge(...array_map(function (Block $block) use ($item) : array { return $block->getDrops($item); }, $affectedBlocks));
		}

		$xpDrop = 0;
		if ($player !== null && !$player->isCreative()) {
			$xpDrop = array_sum(array_map(function (Block $block) use ($item) : int { return $block->getXpDropForTool($item); }, $affectedBlocks));
		}

		if ($player !== null) {
			$ev = new BlockBreakEvent($player, $target, $item, $player->isCreative(), $drops, $xpDrop);

			if ($target instanceof Air || ($player->isSurvival() && !$target->isBreakable($item)) || $player->isSpectator()) {
				$ev->setCancelled();
			}

			if ($player->isAdventure(true) && !$ev->isCancelled()) {
				$tag = $item->getNamedTagEntry("CanDestroy");
				$canBreak = false;
				if ($tag instanceof ListTag) {
					foreach ($tag as $v) {
						if ($v instanceof StringTag) {
							$entry = ItemFactory::fromStringSingle($v->getValue());
							if ($entry->getBlock()->isSameType($target)) {
								$canBreak = true;
								break;
							}
						}
					}
				}

				$ev->setCancelled(!$canBreak);
			}

			$ev->call();
			if ($ev->isCancelled()) {
				return false;
			}

			$drops = $ev->getDrops();
			$xpDrop = $ev->getXpDropAmount();

		} elseif (!$target->isBreakable($item)) {
			return false;
		}

		foreach ($affectedBlocks as $t) {
			$this->destroyBlockInternal($t, $item, $player, $createParticles);
		}

		$item->onDestroyBlock($target);

		if (count($drops) > 0) {
			$dropPos = $target->add(0.5, 0.5, 0.5);
			foreach ($drops as $drop) {
				if (!$drop->isNull()) {
					$this->dropItem($dropPos, $drop);
				}
			}
		}

		if ($xpDrop > 0) {
			$this->dropExperience($target->add(0.5, 0.5, 0.5), $xpDrop);
		}

		return true;
	}

	private function destroyBlockInternal(Block $target, Item $item, ?Player $player = null, bool $createParticles = false) : void
	{
		if ($createParticles) {
			$this->addParticle(new DestroyBlockParticle($target->add(0.5, 0.5, 0.5), $target));
		}

		$target->onBreak($item, $player);

		$tile = $this->getTile($target);
		if ($tile !== null) {
			if ($tile instanceof Container) {
				if ($tile instanceof Chest) {
					$tile->unpair();
				}
				if ($tile instanceof LootContainer) {
					$tile->unpackLootTable($player);
				}

				$tile->getInventory()->dropContents($this, $target);
			}

			$tile->close();
		}
	}

	/**
	 * Uses a item on a position and face, placing it or activating the block
	 *
	 * @param Player|null $player    default null
	 * @param bool        $playSound Whether to play a block-place sound if the block was placed successfully.
	 */
	public function useItemOn(Vector3 $vector, Item &$item, int $face, ?Vector3 $clickVector = null, ?Player $player = null, bool $playSound = false) : bool
	{
		$blockClicked = $this->getBlock($vector);
		$blockReplace = $blockClicked->getSide($face);

		if ($clickVector === null) {
			$clickVector = new Vector3(0.0, 0.0, 0.0);
		} else {
			$clickVector = new Vector3(
				min(1.0, max(0.0, $clickVector->x)),
				min(1.0, max(0.0, $clickVector->y)),
				min(1.0, max(0.0, $clickVector->z))
			);
		}

		if (!$this->isInWorld($blockReplace->x, $blockReplace->y, $blockReplace->z)) {
			//TODO: build height limit messages for custom world heights and mcregion cap
			return false;
		}
		if ($blockClicked->getId() === Block::AIR) {
			return false;
		}

		if ($player !== null) {
			$ev = new PlayerInteractEvent($player, $item, $blockClicked, $clickVector, $face, PlayerInteractEvent::RIGHT_CLICK_BLOCK);
			if ($player->isSpectator()) {
				$ev->setCancelled(); //set it to cancelled so plugins can bypass this
			}

			$ev->call();
			if (!$ev->isCancelled()) {
				if ((!$player->isSneaking() || $item->isNull()) && $blockClicked->onActivate($item, $player)) {
					return true;
				}

				if ($item->onActivate($player, $blockReplace, $blockClicked, $face, $clickVector)) {
					return true;
				}
			} else {
				return false;
			}
		} elseif ($blockClicked->onActivate($item, $player)) {
			return true;
		}

		if ($item->canBePlaced()) {
			$hand = $item->getBlock();
			$hand->position($blockReplace);
		} else {
			return false;
		}

		if ($hand->canBePlacedAt($blockClicked, $clickVector, $face, true)) {
			$blockReplace = $blockClicked;
			$hand->position($blockReplace);
		} elseif (!$hand->canBePlacedAt($blockReplace, $clickVector, $face, false)) {
			return false;
		}

		if ($hand->isSolid()) {
			foreach ($hand->getCollisionBoxes() as $collisionBox) {
				if (count($collidingEntities = $this->getCollidingEntities($collisionBox)) > 0) {
					$collidingEntities = array_filter($collidingEntities, function (Entity $entity) : bool {
						return !($entity instanceof ItemEntity);
					});

					if (count($collidingEntities) > 0) {
						return false; // Entity in block
					}
				}
			}
		}

		if ($player !== null) {
			$ev = new BlockPlaceEvent($player, $hand, $blockReplace, $blockClicked, $item);
			if ($player->isSpectator()) {
				$ev->setCancelled();
			}

			if ($player->isAdventure(true) && !$ev->isCancelled()) {
				$canPlace = false;
				$tag = $item->getNamedTagEntry("CanPlaceOn");
				if ($tag instanceof ListTag) {
					foreach ($tag as $v) {
						if ($v instanceof StringTag) {
							$entry = ItemFactory::fromStringSingle($v->getValue());
							if ($entry->getBlock()->isSameType($blockClicked)) {
								$canPlace = true;
								break;
							}
						}
					}
				}

				$ev->setCancelled(!$canPlace);
			}

			$ev->call();
			if ($ev->isCancelled()) {
				return false;
			}
		}

		if (!$hand->place($item, $blockReplace, $blockClicked, $face, $clickVector, $player)) {
			return false;
		}

		if ($playSound) {
			$this->addSound(new BlockPlaceSound($hand, $hand));
		}

		$item->pop();

		return true;
	}

	public function getEntity(int $entityId) : ?Entity
	{
		return $this->entities[$entityId] ?? null;
	}

	/**
	 * Returns a list of all the entities in this world, indexed by their entity runtime IDs
	 *
	 * @return Entity[]
	 * @phpstan-return array<int, Entity>
	 */
	public function getEntities() : array
	{
		return $this->entities;
	}

	/**
	 * Returns all collidable entities whose bounding boxes intersect the given bounding box.
	 * If an entity is given, it will be excluded from the result.
	 * If a non-collidable entity is given, the result will be empty.
	 *
	 * This function is the same as {@link Level::getNearbyEntities()}, but with additional collidability filters.
	 *
	 * @return Entity[]
	 * @phpstan-return array<int, Entity>
	 */
	public function getCollidingEntities(AxisAlignedBB $bb, ?Entity $entity = null) : array
	{
		$nearby = [];

		if ($entity === null || $entity->canCollide) {
			$minX = ((int) floor($bb->minX - 2)) >> Chunk::COORD_BIT_SIZE;
			$maxX = ((int) floor($bb->maxX + 2)) >> Chunk::COORD_BIT_SIZE;
			$minZ = ((int) floor($bb->minZ - 2)) >> Chunk::COORD_BIT_SIZE;
			$maxZ = ((int) floor($bb->maxZ + 2)) >> Chunk::COORD_BIT_SIZE;

			for ($x = $minX; $x <= $maxX; ++$x) {
				for ($z = $minZ; $z <= $maxZ; ++$z) {
					foreach ((($chunk = $this->chunks[Level::chunkHash($x, $z)] ?? null) !== null ? $chunk->getEntities() : []) as $ent) {
						/** @var Entity|null $entity */
						if ($ent->canBeCollidedWith() && ($entity === null || ($ent !== $entity && $entity->canCollideWith($ent))) && $ent->boundingBox->intersectsWith($bb)) {
							$nearby[] = $ent;
						}
					}
				}
			}
		}

		return $nearby;
	}

	/**
	 * Returns all entities whose bounding boxes intersect the given bounding box, excluding the given entity.
	 *
	 * @return Entity[]
	 * @phpstan-return array<int, Entity>
	 */
	public function getNearbyEntities(AxisAlignedBB $bb, ?Entity $entity = null) : array
	{
		$nearby = [];

		$minX = ((int) floor($bb->minX - 2)) >> Chunk::COORD_BIT_SIZE;
		$maxX = ((int) floor($bb->maxX + 2)) >> Chunk::COORD_BIT_SIZE;
		$minZ = ((int) floor($bb->minZ - 2)) >> Chunk::COORD_BIT_SIZE;
		$maxZ = ((int) floor($bb->maxZ + 2)) >> Chunk::COORD_BIT_SIZE;

		for ($x = $minX; $x <= $maxX; ++$x) {
			for ($z = $minZ; $z <= $maxZ; ++$z) {
				foreach ((($chunk = $this->chunks[Level::chunkHash($x, $z)] ?? null) !== null ? $chunk->getEntities() : []) as $ent) {
					if ($ent !== $entity && $ent->boundingBox->intersectsWith($bb)) {
						$nearby[] = $ent;
					}
				}
			}
		}

		return $nearby;
	}

	/**
	 * Returns the closest Entity to the specified position, within the given radius.
	 *
	 * @param string $entityType  Class of entity to use for instanceof
	 * @param bool   $includeDead Whether to include entitites which are dead
	 * @phpstan-template TEntity of Entity
	 * @phpstan-param class-string<TEntity> $entityType
	 *
	 * @return Entity|null an entity of type $entityType, or null if not found
	 * @phpstan-return TEntity
	 */
	public function getNearestEntity(Vector3 $pos, float $maxDistance, string $entityType = Entity::class, bool $includeDead = false) : ?Entity
	{
		assert(is_a($entityType, Entity::class, true));

		$minX = ((int) floor($pos->x - $maxDistance)) >> Chunk::COORD_BIT_SIZE;
		$maxX = ((int) floor($pos->x + $maxDistance)) >> Chunk::COORD_BIT_SIZE;
		$minZ = ((int) floor($pos->z - $maxDistance)) >> Chunk::COORD_BIT_SIZE;
		$maxZ = ((int) floor($pos->z + $maxDistance)) >> Chunk::COORD_BIT_SIZE;

		$currentTargetDistSq = $maxDistance ** 2;

		/** @var Entity|null $currentTarget */
		$currentTarget = null;

		for ($x = $minX; $x <= $maxX; ++$x) {
			for ($z = $minZ; $z <= $maxZ; ++$z) {
				foreach ((($chunk = $this->chunks[Level::chunkHash($x, $z)] ?? null) !== null ? $chunk->getEntities() : []) as $entity) {
					if (!($entity instanceof $entityType) || $entity->isClosed() || $entity->isFlaggedForDespawn() || (!$includeDead && !$entity->isAlive())) {
						continue;
					}

					$distSq = $entity->distanceSquared($pos);
					if ($distSq < $currentTargetDistSq) {
						$currentTargetDistSq = $distSq;
						$currentTarget = $entity;
					}
				}
			}
		}

		return $currentTarget;
	}

	/**
	 * Returns a list of the Tile entities in this level
	 *
	 * @return Tile[]
	 */
	public function getTiles() : array
	{
		return $this->tiles;
	}

	public function getTileById(int $tileId) : ?Tile
	{
		return $this->tiles[$tileId] ?? null;
	}

	/**
	 * Returns a list of the players in this level
	 *
	 * @return Player[]
	 */
	public function getPlayers() : array
	{
		return $this->players;
	}

	/**
	 * Returns the Tile in a position, or null if not found.
	 *
	 * Note: This method wraps getTileAt(). If you're guaranteed to be passing integers, and you're using this method
	 * in performance-sensitive code, consider using getTileAt() instead of this method for better performance.
	 */
	public function getTile(Vector3 $pos) : ?Tile
	{
		return $this->getTileAt((int) floor($pos->x), (int) floor($pos->y), (int) floor($pos->z));
	}

	/**
	 * Returns the tile at the specified x,y,z coordinates, or null if it does not exist.
	 */
	public function getTileAt(int $x, int $y, int $z) : ?Tile
	{
		$chunk = $this->getChunk($x >> Chunk::COORD_BIT_SIZE, $z >> Chunk::COORD_BIT_SIZE);

		if ($chunk !== null) {
			return $chunk->getTile($x & 0x0f, $y, $z & 0x0f);
		}

		return null;
	}

	public function getBiomeId(int $x, int $z) : int
	{
		return $this->getChunk($x >> Chunk::COORD_BIT_SIZE, $z >> Chunk::COORD_BIT_SIZE, true)->getBiomeId($x & 0x0f, $z & 0x0f);
	}

	public function getBiome(int $x, int $z) : Biome
	{
		return Biome::getBiome($this->getBiomeId($x, $z));
	}

	public function setBiomeId(int $x, int $z, int $biomeId) : void
	{
		$this->getChunk($x >> Chunk::COORD_BIT_SIZE, $z >> Chunk::COORD_BIT_SIZE, true)->setBiomeId($x & 0x0f, $z & 0x0f, $biomeId);
	}

	public function getHeightMap(int $x, int $z) : int
	{
		$chunk = $this->getChunk($x >> Chunk::COORD_BIT_SIZE, $z >> Chunk::COORD_BIT_SIZE);
		if ($chunk === null) {
			return 0;
		}

		return $chunk->getHeightMap($x & 0x0f, $z & 0x0f);
	}

	public function setHeightMap(int $x, int $z, int $value) : void
	{
		$chunk = $this->getChunk($x >> Chunk::COORD_BIT_SIZE, $z >> Chunk::COORD_BIT_SIZE);
		if ($chunk !== null) {
			$chunk->setHeightMap($x & 0x0f, $z & 0x0f, $value);
		}
	}

	/**
	 * Return dimension of Level
	 */
	public function getDimension() : int
	{
		return $this->dimension;
	}

	/**
	 * Sets dimension of Level
	 */
	public function setDimension(int $dimension) : void
	{
		if ($dimension > 2 || $dimension < 0) {
			throw new ArrayOutOfBoundsException("Dimension must be 0-2");
		}

		$this->dimension = $dimension;
	}

	/**
	 * @return Chunk[]
	 */
	public function getChunks() : array
	{
		return $this->chunks;
	}

	public function getChunk(int $chunkX, int $chunkZ, bool $create = false) : ?Chunk
	{
		if (isset($this->chunks[$index = Level::chunkHash($chunkX, $chunkZ)])) {
			return $this->chunks[$index];
		} elseif ($this->loadChunk($chunkX, $chunkZ, $create)) {
			return $this->chunks[$index];
		}

		return null;
	}

	/**
	 * Returns the chunk containing the given Vector3 position
	 */
	public function getChunkAtPosition(Vector3 $pos, bool $create = false) : ?Chunk
	{
		return $this->getChunk($pos->getFloorX() >> Chunk::COORD_BIT_SIZE, $pos->getFloorZ() >> Chunk::COORD_BIT_SIZE, $create);
	}

	/**
	 * @return Entity[] entity runtime ID => Entity
	 * @phpstan-return array<int, Entity>
	 */
	public function getChunkEntities(int $chunkX, int $chunkZ) : array
	{
		return ($chunk = $this->getChunk($chunkX, $chunkZ)) !== null ? $chunk->getEntities() : [];
	}

	/**
	 * Gives a list of the Tile entities on a given chunk
	 *
	 * @return Tile[]
	 */
	public function getChunkTiles(int $chunkX, int $chunkZ) : array
	{
		return ($chunk = $this->getChunk($chunkX, $chunkZ)) !== null ? $chunk->getTiles() : [];
	}

	/**
	 * Returns the chunks adjacent to the specified chunk.
	 *
	 * @return Chunk[]
	 */
	/**
	 * Returns the chunks around the given one, indexed by (z + radius) * (2 * radius + 1) + (x + radius) of the offset.
	 * The center chunk is not included.
	 *
	 * @return (Chunk|null)[]
	 */
	public function getAdjacentChunks(int $x, int $z, int $radius = 1) : array
	{
		$result = [];
		$size = $radius * 2 + 1;
		for ($xx = 0; $xx < $size; ++$xx) {
			for ($zz = 0; $zz < $size; ++$zz) {
				if ($xx === $radius && $zz === $radius) {
					continue; //center chunk
				}
				$result[$zz * $size + $xx] = $this->getChunk($x + $xx - $radius, $z + $zz - $radius, false);
			}
		}

		return $result;
	}

	public function getChunkPopulationRadius() : int
	{
		return $this->chunkPopulationRadius;
	}

	public function setChunk(int $chunkX, int $chunkZ, ?Chunk $chunk = null, bool $deleteEntitiesAndTiles = true) : void
	{
		if ($chunk === null) {
			return;
		}

		$chunk->setX($chunkX);
		$chunk->setZ($chunkZ);

		$chunkHash = Level::chunkHash($chunkX, $chunkZ);
		$oldChunk = $this->getChunk($chunkX, $chunkZ, false);
		if ($oldChunk !== null && $oldChunk !== $chunk) {
			if ($deleteEntitiesAndTiles) {
				foreach ($oldChunk->getEntities() as $player) {
					if (!($player instanceof Player)) {
						continue;
					}
					$chunk->addEntity($player);
					$oldChunk->removeEntity($player);
					$player->chunk = $chunk;
				}
				//TODO: this causes chunkloaders to receive false "unloaded" notifications
				$this->unloadChunk($chunkX, $chunkZ, false, false);
			} else {
				foreach ($oldChunk->getEntities() as $entity) {
					$chunk->addEntity($entity);
					$oldChunk->removeEntity($entity);
					$entity->chunk = $chunk;
				}

				foreach ($oldChunk->getTiles() as $tile) {
					$chunk->addTile($tile);
					$oldChunk->removeTile($tile);
				}
			}
		}

		$this->chunks[$chunkHash] = $chunk;

		$this->blockCacheSize -= count($this->blockCache[$chunkHash] ?? []);
		unset($this->blockCache[$chunkHash]);
		unset($this->blockCollisionBoxCache[$chunkHash]);
		unset($this->chunkCache[$chunkHash]);
		unset($this->changedBlocks[$chunkHash]);
		if (isset($this->chunkSendTasks[$chunkHash])) { //invalidate pending caches
			foreach ($this->chunkSendTasks[$chunkHash] as $protocol => $chunkTask) {
				$chunkTask->cancelRun();
			}
			unset($this->chunkSendTasks[$chunkHash]);
		}

		$chunk->setChanged();

		if (!$this->isChunkInUse($chunkX, $chunkZ)) {
			$this->unloadChunkRequest($chunkX, $chunkZ);
		} else {
			foreach ($this->getChunkLoaders($chunkX, $chunkZ) as $loader) {
				$loader->onChunkChanged($chunk);
			}
		}
	}

	/**
	 * Gets the highest block Y value at a specific $x and $z
	 *
	 * @return int|null 0-255, or null if the column is empty
	 * @throws LevelException if the terrain is not generated
	 */
	public function getHighestBlockAt(int $x, int $z) : ?int
	{
		return $this->getChunk($x >> Chunk::COORD_BIT_SIZE, $z >> Chunk::COORD_BIT_SIZE, true)->getHighestBlockAt($x & 0x0f, $z & 0x0f);
	}

	public function canBlockSeeSky(Vector3 $pos) : bool
	{
		return $this->getHighestBlockAt($pos->getFloorX(), $pos->getFloorZ()) < $pos->getY();
	}

	/**
	 * Returns whether the given position is in a loaded area of terrain.
	 */
	public function isInLoadedTerrain(Vector3 $pos) : bool
	{
		return (isset($this->chunks[Level::chunkHash(($pos->getFloorX() >> Chunk::COORD_BIT_SIZE), ($pos->getFloorZ() >> Chunk::COORD_BIT_SIZE))]));
	}

	public function isChunkLoaded(int $x, int $z) : bool
	{
		return isset($this->chunks[Level::chunkHash($x, $z)]);
	}

	public function isChunkGenerated(int $x, int $z) : bool
	{
		$chunk = $this->getChunk($x, $z);
		return $chunk !== null && $chunk->isGenerated();
	}

	public function isChunkPopulated(int $x, int $z) : bool
	{
		$chunk = $this->getChunk($x, $z);
		return $chunk !== null && $chunk->isPopulated();
	}

	/**
	 * Returns a Position pointing to the spawn
	 */
	public function getSpawnLocation() : Position
	{
		return Position::fromObject($this->provider->getLevelData()->getSpawn(), $this);
	}

	/**
	 * Sets the level spawn location
	 */
	public function setSpawnLocation(Vector3 $pos) : void
	{
		$previousSpawn = $this->getSpawnLocation();
		$this->provider->getLevelData()->setSpawn($pos);
		(new SpawnChangeEvent($this, $previousSpawn))->call();

		$location = Position::fromObject($pos, $this);
		foreach ($this->players as $player) {
			$pk = new SetSpawnPositionPacket();
			$pk->spawnType = SetSpawnPositionPacket::TYPE_WORLD_SPAWN;
			$pk->x = $pk->x2 = $location->getFloorX();
			$pk->y = $pk->y2 = $location->getFloorY();
			$pk->z = $pk->z2 = $location->getFloorZ();
			$pk->dimension = $this->getDimension();
			$pk->spawnForced = false;
			$player->sendDataPacket($pk);
		}
	}

	public function requestChunk(int $x, int $z, Player $player) : void
	{
		$index = Level::chunkHash($x, $z);
		if (!isset($this->chunkSendQueue[$index])) {
			$this->chunkSendQueue[$index] = [];
		}

		$this->chunkSendQueue[$index][$player->getChunkProtocol()][spl_object_id($player)] = $player;
	}

	private function sendChunkFromCache(int $x, int $z, int $protocol) : void
	{
		if (isset($this->chunkSendQueue[$index = Level::chunkHash($x, $z)][$protocol])) {
			foreach ($this->chunkSendQueue[$index][$protocol] as $player) {
				/** @var Player $player */
				if ($player->isConnected() && isset($player->usedChunks[$index]) && isset($this->chunkCache[$index][$protocol])) {
					$player->sendChunk($x, $z, $this->chunkCache[$index][$protocol]);
				}
			}
			unset($this->chunkSendQueue[$index][$protocol]);
		}
	}

	private function processChunkRequest() : void
	{
		if (count($this->chunkSendQueue) > 0) {
			$this->timings->syncChunkSend->startTiming();

			foreach ($this->chunkSendQueue as $index => $protocolPlayers) {
				foreach ($protocolPlayers as $protocol => $players) {
					Level::getXZ($index, $x, $z);
					if (isset($this->chunkSendTasks[$index][$protocol])) {
						if ($this->chunkSendTasks[$index][$protocol]->isCrashed()) {
							unset($this->chunkSendTasks[$index][$protocol]);
							$this->server->getLogger()->error("Failed to prepare chunk $x $z for sending to players with protocol $protocol, retrying");
						} else {
							//Not ready for sending yet
							continue;
						}
					}
					if (isset($this->chunkCache[$index][$protocol])) {
						$this->sendChunkFromCache($x, $z, $protocol);
						continue;
					}
					$this->timings->syncChunkSendPrepare->startTiming();

					$chunk = $this->chunks[$index] ?? null;
					if (!($chunk instanceof Chunk)) {
						throw new ChunkException("Invalid Chunk sent");
					}
					assert($chunk->getX() === $x && $chunk->getZ() === $z, "Chunk coordinate mismatch: expected $x $z, but chunk has coordinates " . $chunk->getX() . " " . $chunk->getZ() . ", did you forget to clone a chunk before setting?");

					$this->workerPool->submitTask($task = new ChunkRequestTask($this, $this->getDimension(), $chunk, $protocol));
					$this->chunkSendTasks[$index][$protocol] = $task;

					$this->timings->syncChunkSendPrepare->stopTiming();
				}
			}

			$this->timings->syncChunkSend->stopTiming();
		}
	}

	public function chunkRequestCallback(int $x, int $z, int $protocol, string $buffer) : void
	{
		$this->timings->syncChunkSend->startTiming();

		$index = Level::chunkHash($x, $z);
		unset($this->chunkSendTasks[$index][$protocol]);

		$this->chunkCache[$index][$protocol] = $buffer;
		$this->sendChunkFromCache($x, $z, $protocol);
		if (!$this->server->getMemoryManager()->canUseChunkCache()) {
			unset($this->chunkCache[$index][$protocol]);
		}

		$this->timings->syncChunkSend->stopTiming();
	}

	/**
	 * @throws InvalidArgumentException
	 */
	public function addEntity(Entity $entity) : void
	{
		if ($entity->isClosed()) {
			throw new InvalidArgumentException("Attempted to add a garbage closed Entity to world");
		}
		if ($entity->getLevel() !== $this) {
			throw new LevelException("Invalid Entity world");
		}

		if ($entity instanceof Player) {
			$this->players[$entity->getId()] = $entity;
		}
		$this->entities[$entity->getId()] = $entity;
	}

	/**
	 * Removes the entity from the world index
	 *
	 * @throws InvalidArgumentException
	 */
	public function removeEntity(Entity $entity) : void
	{
		if ($entity->getLevel() !== $this) {
			throw new LevelException("Invalid Entity world");
		}

		if ($entity instanceof Player) {
			unset($this->players[$entity->getId()]);
			$this->checkSleep();
		}

		unset($this->entities[$entity->getId()]);
		$this->entityUpdatePriorityQueue->removeAny($entity->getId());
	}

	public function scheduleEntityUpdate(Entity $entity) : void
	{
		if ($entity->getLevel() !== $this || $entity->isClosed()) {
			return;
		}

		if ($entity->shouldAlwaysUpdate()) {
			$this->entityUpdatePriorityQueue->addAlways($entity->getId());
		} else {
			$this->entityUpdatePriorityQueue->add($entity->getId());
		}
	}

	/**
	 * @internal Tiles are now bound with blocks, and their creation is automatic. They should not be directly added.
	 * @throws InvalidArgumentException
	 */
	public function addTile(Tile $tile) : void
	{
		if ($tile->isClosed()) {
			throw new InvalidArgumentException("Attempted to add a garbage closed Tile to world");
		}
		if ($tile->getLevel() !== $this) {
			throw new LevelException("Invalid Tile world");
		}

		$chunkX = $tile->getFloorX() >> Chunk::COORD_BIT_SIZE;
		$chunkZ = $tile->getFloorZ() >> Chunk::COORD_BIT_SIZE;

		if (isset($this->chunks[$hash = Level::chunkHash($chunkX, $chunkZ)])) {
			$this->chunks[$hash]->addTile($tile);
		} else {
			throw new InvalidStateException("Attempted to create tile " . get_class($tile) . " in unloaded chunk $chunkX $chunkZ");
		}

		$this->tiles[$tile->getId()] = $tile;
		$this->clearChunkCache($chunkX, $chunkZ);
	}

	/**
	 * @internal Tiles are now bound with blocks, and their removal is automatic. They should not be directly removed.
	 * @throws InvalidArgumentException
	 */
	public function removeTile(Tile $tile) : void
	{
		if ($tile->getLevel() !== $this) {
			throw new LevelException("Invalid Tile world");
		}

		unset($this->tiles[$tile->getId()]);

		$chunkX = $tile->getFloorX() >> Chunk::COORD_BIT_SIZE;
		$chunkZ = $tile->getFloorZ() >> Chunk::COORD_BIT_SIZE;

		if (isset($this->chunks[$hash = Level::chunkHash($chunkX, $chunkZ)])) {
			$this->chunks[$hash]->removeTile($tile);
		}
		$this->clearChunkCache($chunkX, $chunkZ);
	}

	public function isChunkInUse(int $x, int $z) : bool
	{
		return isset($this->chunkLoaders[$index = Level::chunkHash($x, $z)]) && count($this->chunkLoaders[$index]) > 0;
	}

	/**
	 * Attempts to load a chunk from the world provider (if not already loaded). If the chunk is already loaded, it is
	 * returned directly.
	 */
	public function loadChunk(int $x, int $z, bool $create = true) : bool
	{
		if (isset($this->chunks[$chunkHash = Level::chunkHash($x, $z)])) {
			return true;
		}

		$this->timings->syncChunkLoad->startTiming();

		$this->cancelUnloadChunkRequest($x, $z);

		$this->timings->syncChunkLoadData->startTiming();

		$chunk = null;

		try {
			$chunk = $this->provider->loadChunk($x, $z);
		} catch (CorruptedChunkException | UnsupportedChunkFormatException $e) {
			$this->logger->critical("Failed to load chunk x=$x z=$z: " . $e->getMessage());
		}

		if ($chunk === null && $create) {
			$chunk = new Chunk($x, $z);
		}

		$this->timings->syncChunkLoadData->stopTiming();

		if ($chunk === null) {
			$this->timings->syncChunkLoad->stopTiming();
			return false;
		}

		$this->chunks[$chunkHash] = $chunk;

		$this->blockCacheSize -= count($this->blockCache[$chunkHash] ?? []);
		unset($this->blockCache[$chunkHash]);
		unset($this->blockCollisionBoxCache[$chunkHash]);

		$chunk->initChunk($this);

		(new ChunkLoadEvent($this, $chunk, !$chunk->isGenerated()))->call();

		if (!$chunk->isLightPopulated() && $chunk->isPopulated() && $this->getServer()->getProperty("chunk-ticking.light-updates", false)) {
			$this->workerPool->submitTask(new LightPopulationTask($this, $chunk));
		}

		if ($this->isChunkInUse($x, $z)) {
			foreach ($this->getChunkLoaders($x, $z) as $loader) {
				$loader->onChunkLoaded($chunk);
			}
		} else {
			$this->server->getLogger()->debug("Newly loaded chunk $x $z has no loaders registered, will be unloaded at next available opportunity");
			$this->unloadChunkRequest($x, $z);
		}

		$this->timings->syncChunkLoad->stopTiming();

		return true;
	}

	private function queueUnloadChunk(int $x, int $z) : void
	{
		$this->unloadQueue[$index = Level::chunkHash($x, $z)] = microtime(true);
		unset($this->chunkTickList[$index]);
	}

	public function unloadChunkRequest(int $x, int $z, bool $safe = true) : bool
	{
		if (($safe && $this->isChunkInUse($x, $z)) || $this->isSpawnChunk($x, $z)) {
			return false;
		}

		$this->queueUnloadChunk($x, $z);

		return true;
	}

	public function cancelUnloadChunkRequest(int $x, int $z) : void
	{
		unset($this->unloadQueue[Level::chunkHash($x, $z)]);
	}

	public function unloadChunk(int $x, int $z, bool $safe = true, bool $trySave = true) : bool
	{
		if ($safe && $this->isChunkInUse($x, $z)) {
			return false;
		}

		if (!$this->isChunkLoaded($x, $z)) {
			return true;
		}

		$this->timings->doChunkUnload->startTiming();

		$chunkHash = Level::chunkHash($x, $z);

		$chunk = $this->chunks[$chunkHash] ?? null;

		if ($chunk !== null) {
			if ($chunk->isProtected()) {
				$this->cancelUnloadChunkRequest($x, $z);
				return false;
			}

			$ev = new ChunkUnloadEvent($this, $chunk);
			$ev->call();
			if ($ev->isCancelled()) {
				$this->timings->doChunkUnload->stopTiming();

				return false;
			}

			if ($trySave && $this->getAutoSave() && $chunk->isGenerated()) {
				if ($chunk->hasChanged() || count($chunk->getTiles()) > 0 || count($chunk->getSavableEntities()) > 0) {
					$this->timings->syncChunkSave->startTiming();
					try {
						$this->provider->saveChunk($chunk);
					} finally {
						$this->timings->syncChunkSave->stopTiming();
					}
				}
			}

			foreach ($this->getChunkLoaders($x, $z) as $loader) {
				$loader->onChunkUnloaded($chunk);
			}

			$chunk->onUnload();
		}

		unset($this->chunks[$chunkHash]);
		unset($this->chunkTickList[$chunkHash]);
		unset($this->chunkCache[$chunkHash]);
		$this->blockCacheSize -= count($this->blockCache[$chunkHash] ?? []);
		unset($this->blockCache[$chunkHash]);
		unset($this->blockCollisionBoxCache[$chunkHash]);
		unset($this->changedBlocks[$chunkHash]);
		unset($this->chunkSendQueue[$chunkHash]);
		unset($this->chunkSendTasks[$chunkHash]);

		$this->timings->doChunkUnload->stopTiming();

		return true;
	}

	/**
	 * Returns whether the chunk at the specified coordinates is a spawn chunk
	 */
	public function isSpawnChunk(int $X, int $Z) : bool
	{
		$spawn = $this->getSpawnLocation();
		$spawnX = $spawn->x >> Chunk::COORD_BIT_SIZE;
		$spawnZ = $spawn->z >> Chunk::COORD_BIT_SIZE;

		return abs($X - $spawnX) <= 1 && abs($Z - $spawnZ) <= 1;
	}

	/**
	 * Returns a safe spawn position near the given position, or near the world's spawn position if not provided.
	 * This function will throw an exception if the terrain is not already generated in advance.
	 *
	 * @throws LevelException if the terrain is not generated
	 */
	public function getSafeSpawn(?Vector3 $spawn = null) : Position
	{
		if (!($spawn instanceof Vector3) || $spawn->y < 1) {
			$spawn = $this->getSpawnLocation();
		}

		$max = $this->worldHeight;
		$v = $spawn->floor();
		$chunk = $this->getChunkAtPosition($v, false);
		$x = (int) $v->x;
		$z = (int) $v->z;
		$y = (int) min($max - 2, $v->y);
		$wasAir = $this->getBlockAt($x, $y - 1, $z)->getId() === Block::AIR; //TODO: bad hack, clean up
		for (; $y > self::Y_MIN; --$y) {
			if ($this->getBlockAt($x, $y, $z)->isFullCube()) {
				if ($wasAir) {
					$y++;
				}
				break;
			} else {
				$wasAir = true;
			}
		}

		for (; $y >= self::Y_MIN && $y < $max; ++$y) {
			if (!$this->getBlockAt($x, $y + 1, $z)->isFullCube()) {
				if (!$this->getBlockAt($x, $y, $z)->isFullCube()) {
					return new Position($spawn->x, $y === (int) $spawn->y ? $spawn->y : $y, $spawn->z, $this);
				}
			} else {
				++$y;
			}
		}

		return new Position($spawn->x, $y, $spawn->z, $this);
	}

	/**
	 * Gets the current time
	 */
	public function getTime() : int
	{
		return $this->time;
	}

	/**
	 * Returns the current time of day
	 */
	public function getTimeOfDay() : int
	{
		return $this->time % self::TIME_FULL;
	}

	/**
	 * Returns the World display name.
	 * WARNING: This is NOT guaranteed to be unique. Multiple worlds at runtime may share the same display name.
	 */
	public function getName() : string
	{
		return $this->displayName;
	}

	/**
	 * Returns the World folder name. This will not change at runtime and will be unique to a world per runtime.
	 */
	public function getFolderName() : string
	{
		return $this->folderName;
	}

	/**
	 * Sets the current time on the world
	 */
	public function setTime(int $time) : void
	{
		$this->time = $time;
		$this->sendTime();
	}

	/**
	 * Stops the time for the world, will not save the lock state to disk
	 */
	public function stopTime() : void
	{
		$this->stopTime = true;
		$this->sendTime();
	}

	/**
	 * Start the time again, if it was stopped
	 */
	public function startTime() : void
	{
		$this->stopTime = false;
		$this->sendTime();
	}

	/**
	 * Gets the world seed
	 */
	public function getSeed() : int
	{
		return $this->provider->getLevelData()->getSeed();
	}

	/**
	 * @internal
	 */
	public function getWorldHeight() : int
	{
		return $this->worldHeight;
	}

	public function getDifficulty() : int
	{
		return $this->provider->getLevelData()->getDifficulty();
	}

	public function setDifficulty(int $difficulty) : void
	{
		if ($difficulty < 0 || $difficulty > 3) {
			throw new InvalidArgumentException("Invalid difficulty level $difficulty");
		}
		$this->provider->getLevelData()->setDifficulty($difficulty);

		$this->sendDifficulty();
	}

	public function sendDifficulty(Player ...$targets) : void
	{
		if (count($targets) === 0) {
			$targets = $this->getPlayers();
		}

		$pk = new SetDifficultyPacket();
		$pk->difficulty = $this->getDifficulty();
		$this->server->broadcastPacket($targets, $pk);
	}

	public function populateChunk(int $x, int $z, bool $force = false) : bool
	{
		if (isset($this->chunkPopulationQueue[$index = Level::chunkHash($x, $z)]) || (count($this->chunkPopulationQueue) >= $this->chunkPopulationQueueSize && !$force)) {
			return false;
		}
		for ($xx = -$this->chunkPopulationRadius; $xx <= $this->chunkPopulationRadius; ++$xx) {
			for ($zz = -$this->chunkPopulationRadius; $zz <= $this->chunkPopulationRadius; ++$zz) {
				if (isset($this->chunkPopulationLock[Level::chunkHash($x + $xx, $z + $zz)])) {
					return false;
				}
			}
		}

		$chunk = $this->getChunk($x, $z, true);
		if (!$chunk->isPopulated()) {
			$this->timings->population->startTiming();

			$this->chunkPopulationQueue[$index] = true;
			for ($xx = -$this->chunkPopulationRadius; $xx <= $this->chunkPopulationRadius; ++$xx) {
				for ($zz = -$this->chunkPopulationRadius; $zz <= $this->chunkPopulationRadius; ++$zz) {
					$this->chunkPopulationLock[Level::chunkHash($x + $xx, $z + $zz)] = true;
				}
			}

			$task = new PopulationTask($this, $chunk);
			$workerId = $this->workerPool->selectWorker();
			if (!isset($this->generatorRegisteredWorkers[$workerId])) {
				$this->registerGeneratorToWorker($workerId);
			}
			$this->workerPool->submitTaskToWorker($task, $workerId);

			$this->timings->population->stopTiming();
			return false;
		}

		return true;
	}

	public function generateChunkCallback(int $x, int $z, ?Chunk $chunk) : void
	{
		$timings = $this->timings->chunkPopulationCompletion;
		$timings->startTiming();

		if (isset($this->chunkPopulationQueue[$index = Level::chunkHash($x, $z)])) {
			for ($xx = -$this->chunkPopulationRadius; $xx <= $this->chunkPopulationRadius; ++$xx) {
				for ($zz = -$this->chunkPopulationRadius; $zz <= $this->chunkPopulationRadius; ++$zz) {
					unset($this->chunkPopulationLock[Level::chunkHash($x + $xx, $z + $zz)]);
				}
			}
			unset($this->chunkPopulationQueue[$index]);

			if ($chunk !== null) {
				$chunk->initChunk($this);

				$oldChunk = $this->getChunk($x, $z, false);
				$this->setChunk($x, $z, $chunk, false);
				if (($oldChunk === null || !$oldChunk->isPopulated()) && $chunk->isPopulated()) {
					(new ChunkPopulateEvent($this, $chunk))->call();

					foreach($this->getChunkLoaders($x, $z) as $loader){
						$loader->onChunkPopulated($chunk);
					}
				}
			}
		} elseif (isset($this->chunkPopulationLock[$index])) {
			unset($this->chunkPopulationLock[$index]);
			if ($chunk !== null) {
				$this->setChunk($x, $z, $chunk, false);
			}
		} elseif ($chunk !== null) {
			$this->setChunk($x, $z, $chunk, false);
		}

		$timings->stopTiming();
	}

	public function doChunkGarbageCollection() : void
	{
		$this->timings->doChunkGC->startTiming();

		foreach ($this->chunks as $index => $chunk) {
			if (!isset($this->unloadQueue[$index])) {
				Level::getXZ($index, $X, $Z);
				if (!$this->isSpawnChunk($X, $Z)) {
					$this->unloadChunkRequest($X, $Z, true);
				}
			}
			$chunk->collectGarbage();
		}

		$this->provider->doGarbageCollection();

		$this->timings->doChunkGC->stopTiming();
	}

	public function unloadChunks(bool $force = false) : void
	{
		if (count($this->unloadQueue) > 0) {
			$maxUnload = 96;
			$now = microtime(true);
			foreach ($this->unloadQueue as $index => $time) {
				Level::getXZ($index, $X, $Z);

				if (!$force) {
					if ($maxUnload <= 0) {
						break;
					} elseif ($time > ($now - 30)) {
						continue;
					}
				}

				//If the chunk can't be unloaded, it stays on the queue
				if ($this->unloadChunk($X, $Z, true)) {
					unset($this->unloadQueue[$index]);
					--$maxUnload;
				}
			}
		}
	}

	public function canSeeSky(Vector3 $pos) : bool
	{
		return $pos->y >= $this->getHighestBlockAt($pos->getFloorX(), $pos->getFloorZ());
	}

	public function getSpawnListEntryForTypeAt(CreatureType $creatureType, Vector3 $pos) : ?SpawnListEntry{
		// TODO: get level provider's possible creatures
		$possibleCreatures = $this->getBiome($pos->x, $pos->z)->getSpawnableList($creatureType);

		if (count($possibleCreatures) === 0) {
			return null;
		}

		/** @var SpawnListEntry|null $possible */
		$possible = WeightedRandomItem::getRandomItem($this->random, $possibleCreatures, WeightedRandomItem::getTotalWeight($possibleCreatures));

		return $possible;
	}

	public function canCreatureTypeSpawnHere(CreatureType $creatureType, SpawnListEntry $entry, Vector3 $pos) : bool{
		// TODO: get level provider's possible creatures
		$possibleCreatures = $this->getBiome($pos->x, $pos->z)->getSpawnableList($creatureType);

		return count($possibleCreatures) !== 0 && in_array($entry, $possibleCreatures, true);
	}

	public function getBlockDensity(Vector3 $vec, AxisAlignedBB $bb) : float
	{
		$dX = 1 / (($bb->maxX - $bb->minX) * 2 + 1);
		$dY = 1 / (($bb->maxY - $bb->minY) * 2 + 1);
		$dZ = 1 / (($bb->maxZ - $bb->minZ) * 2 + 1);

		$fX = (1 - floor(1 / $dX) * $dX) / 2;
		$fZ = (1 - floor(1 / $dZ) * $dZ) / 2;

		if ($dX >= 0 && $dY >= 0 && $dZ >= 0) {
			$blocksDensity = 0;
			$totalBlocks = 0;

			for ($vX = 0; $vX <= 1; $vX += $dX) {
				for ($vY = 0; $vY <= 1; $vY += $dY) {
					for ($vZ = 0; $vZ <= 1; $vZ += $dZ) {
						$x = $bb->minX + ($bb->maxX - $bb->minX) * $vX;
						$y = $bb->minY + ($bb->maxY - $bb->minY) * $vY;
						$z = $bb->minZ + ($bb->maxZ - $bb->minZ) * $vZ;

						$start = new Vector3($x + $fX, $y, $z + $fZ);
						$block = $this->getBlock($vec);

						if ($block->calculateIntercept($start, $vec) !== null) {
							++$blocksDensity;
						} else {
							foreach (VoxelRayTrace::betweenPoints($start, $vec) as $blockPos) {
								$block = $this->getBlockAt($blockPos->x, $blockPos->y, $blockPos->z);

								if ($block->calculateIntercept($start, $vec) !== null) {
									++$blocksDensity;
									break;
								}
							}
						}

						++$totalBlocks;
					}
				}
			}

			return $blocksDensity / $totalBlocks;
		}

		return 0;
	}

	public function isDayTime() : bool
	{
		return $this->getSunAngleDegrees() < 90 || $this->getSunAngleDegrees() > 270;
	}

	public function getGameRules() : GameRules
	{
		return $this->gameRules;
	}

	public function getSpawnPeacefulMobs() : bool
	{
		return $this->spawnPeacefulMobs;
	}

	public function getSpawnHostileMobs() : bool
	{
		return $this->spawnHostileMobs;
	}

	public function setSpawnPeacefulMobs(bool $spawnPeacefulMobs) : void
	{
		$this->spawnPeacefulMobs = $spawnPeacefulMobs;
	}

	public function setSpawnHostileMobs(bool $spawnHostileMobs) : void
	{
		$this->spawnHostileMobs = $spawnHostileMobs;
	}
}
