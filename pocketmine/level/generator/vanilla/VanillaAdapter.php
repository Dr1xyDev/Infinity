<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | | | | (_| | |__| |  __/
 * |_|   \_\_|\___|_|\___|_|\__|_| |_| |_|_| |_|\__,_|\____|_| |_|\___|
 *
 * Adaptador que expone la generacion vanilla completa (arbol SMC vendorizado
 * bajo pocketmine\level\generator\vanilla) a traves de la API Generator
 * legacy de Infinity, para que el flujo GenerationTask/PopulationTask existente
 * la use sin cambios.
 *
*/

declare(strict_types=1);

namespace pocketmine\level\generator\vanilla;

use pocketmine\level\format\FullChunk;
use pocketmine\level\generator\Generator as LegacyGenerator;
use pocketmine\level\generator\vanilla\level\VanillaChunkManager;
use pocketmine\level\generator\vanilla\block\BlockFactory;
use pocketmine\level\generator\vanilla\level\generator\dimension\Nether;
use pocketmine\level\generator\vanilla\level\generator\dimension\Overworld;
use pocketmine\level\generator\vanilla\level\generator\dimension\TheEnd;
use pocketmine\level\generator\vanilla\level\generator\Generator as VanillaGeneratorBase;
use pocketmine\level\generator\vanilla\nbt\tag\CompoundTag;
use pocketmine\level\generator\vanilla\utils\Random as VanillaRandom;
use pocketmine\math\Vector3;
use pocketmine\utils\Random as LegacyRandom;

class VanillaAdapter extends LegacyGenerator{

	public const TYPE_OVERWORLD = 0;
	public const TYPE_NETHER = 1;
	public const TYPE_END = 2;

	/** @var int */
	private $dimType;

	/** @var VanillaChunkManager|null */
	private $manager;

	/** @var VanillaGeneratorBase|null */
	private $impl;

	public function __construct(array $settings = []){
		parent::__construct($settings);
		$type = $settings["dimension"] ?? $settings["preset"] ?? "overworld";
		$this->dimType = match(strtolower((string) $type)){
			"nether", "hell" => self::TYPE_NETHER,
			"end", "the_end" => self::TYPE_END,
			default => self::TYPE_OVERWORLD,
		};
	}

	public function getDimensionType() : int{
		return $this->dimType;
	}

	/**
	 * Factoria para el registro del level-type por defecto del server.
	 * "DEFAULT" y variantes de nether/end mapean a la dimension vanilla correcta.
	 */
	public static function fromServerLevelType(string $levelType) : string{
		return match(strtoupper($levelType)){
			"NETHER", "HELL" => VanillaAdapterNether::class,
			"END", "THE_END" => VanillaAdapterEnd::class,
			default => self::class,
		};
	}

	/** @var \pocketmine\level\ChunkManager */
	protected $level;

	/** @var LegacyRandom */
	protected $random;

	public function init(\pocketmine\level\ChunkManager $legacyManager, LegacyRandom $random) : void{
		$this->level = $legacyManager;
		$this->random = $random;

		BlockFactory::init(); // asegura el registro de bloques vanilla (idempotente)

		$seed = (int) $legacyManager->getSeed();
		$worldHeight = 128; // altura legacy de Infinity

		// Resolvedor de chunks vecinos legacy (para populateChunk)
		$provider = null;
		if($legacyManager instanceof \pocketmine\level\SimpleChunkManager){
			$provider = static function(int $cx, int $cz) use ($legacyManager) : ?FullChunk{
				return $legacyManager->getChunk($cx, $cz);
			};
		}

		$this->manager = new VanillaChunkManager($seed, $worldHeight, $provider);

		$implClass = match($this->dimType){
			self::TYPE_NETHER => Nether::class,
			self::TYPE_END => TheEnd::class,
			default => Overworld::class,
		};

		$this->impl = new $implClass();
		$this->impl->init($this->manager, new VanillaRandom($seed));
	}

	private function ensureChunkWrapped(int $chunkX, int $chunkZ) : GenChunk{
		$gen = $this->manager->getGenChunk($chunkX, $chunkZ);
		if($gen === null){
			$legacy = $this->level->getChunk($chunkX, $chunkZ);
			if($legacy === null){
				throw new \RuntimeException("Chunk legacy $chunkX,$chunkZ no disponible para el generador vanilla");
			}
			$this->manager->setChunkFromLegacy($chunkX, $chunkZ, $legacy);
			$gen = $this->manager->getGenChunk($chunkX, $chunkZ);
		}
		return $gen;
	}

	public function generateChunk($chunkX, $chunkZ) : void{
		$chunkX = (int) $chunkX;
		$chunkZ = (int) $chunkZ;
		$this->ensureChunkWrapped($chunkX, $chunkZ);
		$this->impl->generateChunk($chunkX, $chunkZ);
	}

	public function populateChunk($chunkX, $chunkZ) : void{
		$chunkX = (int) $chunkX;
		$chunkZ = (int) $chunkZ;
		$this->ensureChunkWrapped($chunkX, $chunkZ);
		$this->impl->populateChunk($chunkX, $chunkZ);
	}

	public function getSettings() : array{
		return [];
	}

	public function getName() : string{
		return "Vanilla" . match($this->dimType){
			self::TYPE_NETHER => "Nether",
			self::TYPE_END => "End",
			default => "Overworld",
		};
	}

	public function getSpawn() : Vector3{
		return new Vector3(0, 0, 0);
	}

	public function getWaterHeight() : int{
		return 63;
	}

	/**
	 * Tiles/entidades NBT pendientes del manager vanilla (cofres y spawners
	 * de estructuras, etc.) para que el hilo principal los materialice.
	 *
	 * @return CompoundTag[]
	 */
	public function takePendingTiles() : array{
		return $this->manager !== null ? $this->manager->takePendingTiles() : [];
	}

	/**
	 * @return CompoundTag[]
	 */
	public function takePendingEntities() : array{
		return $this->manager !== null ? $this->manager->takePendingEntities() : [];
	}
}
