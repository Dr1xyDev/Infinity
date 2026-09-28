<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | | | | (_| | |__| |  __/
 * |_|   \_\_|\___|_|\___|_|\__|_| |_| |_|_| |_|\__,_|\____|_| |_|\___|
 *
 * Puente entre el formato de chunk legacy de Infinity y el formato usado
 * por la generacion vanilla (arbol SMC vendorizado bajo este namespace).
 *
*/

declare(strict_types=1);

namespace pocketmine\level\generator\vanilla;

use pocketmine\level\generator\vanilla\block\Block;
use pocketmine\level\generator\vanilla\block\BlockFactory;
use pocketmine\level\generator\vanilla\level\format\Chunk;
use pocketmine\level\generator\vanilla\nbt\tag\CompoundTag;

/**
 * Chunk vanilla que delega la lectura/escritura de bloques en un chunk
 * legacy de Infinity (FullChunk con secciones de 16 bloques, ids de 8 bits).
 *
 * Los bloques con estados fuera del rango legacy (id > 255) se ignoran de
 * forma segura para no corromper el mundo.
 */
class GenChunk extends Chunk{

	public const VANILLA_META_BITS = Block::INTERNAL_METADATA_BITS; // 8
	public const VANILLA_META_MASK = (1 << self::VANILLA_META_BITS) - 1;

	/** @var \pocketmine\level\format\FullChunk|null */
	private $legacyChunk;

	/** @var int altura maxima del mundo legacy */
	private $maxY;

	/** @var CompoundTag[] */
	private $collectedTiles = [];

	/** @var CompoundTag[] */
	private $collectedEntities = [];

	/** @var bool[] mapa fullId vanilla => bool(escribe legacy) */
	private $writableCache = [];

	public function __construct(\pocketmine\level\format\FullChunk $legacyChunk, int $maxY){
		parent::__construct($legacyChunk->getX(), $legacyChunk->getZ());
		$this->legacyChunk = $legacyChunk;
		$this->maxY = $maxY;
	}

	public function getLegacyChunk() : \pocketmine\level\format\FullChunk{
		return $this->legacyChunk;
	}

	/** @return CompoundTag[] */
	public function takeCollectedTiles() : array{
		try{ return $this->collectedTiles; } finally{ $this->collectedTiles = []; }
	}

	/** @return CompoundTag[] */
	public function takeCollectedEntities() : array{
		try{ return $this->collectedEntities; } finally{ $this->collectedEntities = []; }
	}

	public function addNBTTile(CompoundTag $nbt) : void{
		$this->collectedTiles[] = $nbt;
	}

	public function addNBTEntity(CompoundTag $nbt) : void{
		$this->collectedEntities[] = $nbt;
	}

	public function getMaxY() : int{
		return $this->maxY;
	}

	/**
	 * Devuelve id y meta legacy para un fullId vanilla, o null si el estado
	 * no tiene representacion legacy.
	 *
	 * @return int[]|null [id, meta]
	 */
	private function mapToLegacy(int $fullId) : ?array{
		$id = $fullId >> self::VANILLA_META_BITS;
		$meta = $fullId & self::VANILLA_META_MASK;

		if($id > 0xff){
			// Estados modernos sin equivalente legacy: se omiten.
			// El terreno vanilla clasico (0-255) no se ve afectado.
			return null;
		}
		return [$id, $meta];
	}

	public function getFullBlock(int $x, int $y, int $z) : int{
		if($y < 0 or $y > $this->maxY){
			return Block::AIR << self::VANILLA_META_BITS;
		}
		$full = $this->legacyChunk->getFullBlock($x, $y, $z);
		return ((($full >> 4) & 0xff) << self::VANILLA_META_BITS) | ($full & 0xf);
	}

	public function setFullBlock(int $x, int $y, int $z, int $fullId) : bool{
		if($y < 0 or $y > $this->maxY){
			return false;
		}
		$mapped = $this->mapToLegacy($fullId);
		if($mapped === null){
			return false;
		}
		$this->legacyChunk->setBlock($x, $y, $z, $mapped[0], $mapped[1]);
		return true;
	}

	public function getBlockId(int $x, int $y, int $z) : int{
		if($y < 0 or $y > $this->maxY){
			return 0;
		}
		return $this->legacyChunk->getBlockId($x, $y, $z);
	}

	public function setBlockId(int $x, int $y, int $z, int $id) : void{
		if($y >= 0 and $y <= $this->maxY and $id <= 0xff){
			$this->legacyChunk->setBlockId($x, $y, $z, $id);
		}
	}

	public function getBlockData(int $x, int $y, int $z) : int{
		if($y < 0 or $y > $this->maxY){
			return 0;
		}
		return $this->legacyChunk->getBlockData($x, $y, $z);
	}

	public function setBlockData(int $x, int $y, int $z, int $data) : void{
		if($y >= 0 and $y <= $this->maxY){
			$this->legacyChunk->setBlockData($x, $y, $z, $data & 0xf);
		}
	}

	public function getBiomeId(int $x, int $z) : int{
		return $this->legacyChunk->getBiomeId($x, $z);
	}

	public function setBiomeId(int $x, int $z, int $biomeId) : void{
		$this->legacyChunk->setBiomeId($x, $z, $biomeId & 0xff);
	}

	public function getHeightMap(int $x, int $z) : int{
		return $this->legacyChunk->getHeightMap($x, $z);
	}

	public function setHeightMap(int $x, int $z, int $value) : void{
		$this->legacyChunk->setHeightMap($x, $z, min($value, 127));
	}

	public function getHighestBlockAt(int $x, int $z) : int{
		return $this->legacyChunk->getHighestBlockAt($x, $z);
	}

	public function hasChanged() : bool{
		return $this->legacyChunk->hasChanged();
	}

	public function setChanged(bool $value = true) : void{
		$this->legacyChunk->setChanged($value);
	}
}
