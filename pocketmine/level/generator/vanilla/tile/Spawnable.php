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

namespace pocketmine\level\generator\vanilla\tile;

use pocketmine\level\generator\vanilla\level\ChunkManager;
use pocketmine\level\generator\vanilla\level\format\Chunk;
use pocketmine\level\generator\vanilla\level\Level;
use pocketmine\level\generator\vanilla\nbt\NetworkLittleEndianNBTStream;
use pocketmine\level\generator\vanilla\nbt\tag\CompoundTag;
use pocketmine\level\generator\vanilla\nbt\tag\IntTag;
use pocketmine\level\generator\vanilla\nbt\tag\StringTag;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\BlockActorDataPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ProtocolInfo;
use pocketmine\Player;

abstract class Spawnable extends Tile
{
	/** @var string[] */
	private array $spawnCompoundCache = [];
	/** @var NetworkLittleEndianNBTStream|null */
	private static $nbtWriter = null;

	public function __construct(ChunkManager $level, CompoundTag $nbt){
		parent::__construct($level, $nbt);

		if ($level instanceof Level) {
			$this->spawnToAll();
		}
	}

	public function createSpawnPacket(int $protocolVersion = ProtocolInfo::CURRENT_PROTOCOL) : BlockActorDataPacket{
		return BlockActorDataPacket::create($this->x, $this->y, $this->z, $this->getSerializedSpawnCompound($protocolVersion));
	}

	public function spawnTo(Player $player) : bool
	{
		if ($this->closed) {
			return false;
		}

		$player->dataPacket($this->createSpawnPacket($player->getProtocolVersion()));

		return true;
	}

	public function spawnToAll() : void
	{
		if ($this->closed) {
			return;
		}

		/** @var Player[][] $protocolPlayers */
		$protocolPlayers = [];
		foreach ($this->level->getChunkPlayers($this->getFloorX() >> Chunk::COORD_BIT_SIZE, $this->getFloorZ() >> Chunk::COORD_BIT_SIZE) as $player) {
			$protocolPlayers[$player->getProtocolVersion()][] = $player;
		}

		foreach ($protocolPlayers as $protocolVersion => $players) {
			$packet = $this->createSpawnPacket($protocolVersion);

			foreach ($players as $player) {
				$player->sendDataPacket($packet);
			}
		}
	}

	/**
	 * Performs actions needed when the tile is modified, such as clearing caches and respawning the tile to players.
	 * WARNING: This MUST be called to clear spawn-compound and chunk caches when the tile's spawn compound has changed!
	 */
	protected function onChanged() : void
	{
		if ($this->level instanceof Level) {
			$this->spawnCompoundCache = [];
			$this->spawnToAll();

			$this->level->clearChunkCache($this->getFloorX() >> Chunk::COORD_BIT_SIZE, $this->getFloorZ() >> Chunk::COORD_BIT_SIZE);
		}
	}

	/**
	 * Returns encoded NBT (varint, little-endian) used to spawn this tile to clients. Uses cache where possible,
	 * populates cache if it is null.
	 *
	 * @phpstan-return string
	 */
	final public function getSerializedSpawnCompound(int $protocolVersion = ProtocolInfo::CURRENT_PROTOCOL) : string
	{
		if (!isset($this->spawnCompoundCache[$protocolVersion])) {
			if (self::$nbtWriter === null) {
				self::$nbtWriter = new NetworkLittleEndianNBTStream();
			}

			$this->spawnCompoundCache[$protocolVersion] = self::$nbtWriter->write($this->getSpawnCompound($protocolVersion));
		}

		return $this->spawnCompoundCache[$protocolVersion];
	}

	final public function getSpawnCompound(int $protocolVersion = ProtocolInfo::CURRENT_PROTOCOL) : CompoundTag
	{
		$nbt = new CompoundTag("", [
			new StringTag(self::TAG_ID, static::getSaveId()),
			new IntTag(self::TAG_X, $this->x),
			new IntTag(self::TAG_Y, $this->y),
			new IntTag(self::TAG_Z, $this->z)
		]);
		$this->addAdditionalSpawnData($nbt, $protocolVersion);
		return $nbt;
	}

	/**
	 * An extension to getSpawnCompound() for
	 * further modifying the generic tile NBT.
	 */
	abstract protected function addAdditionalSpawnData(CompoundTag $nbt, int $protocolVersion) : void;

	/**
	 * Called when a player updates a block entity's NBT data
	 * for example when writing on a sign.
	 *
	 * @return bool indication of success, will respawn the tile to the player if false.
	 */
	public function updateCompoundTag(CompoundTag $nbt, Player $player) : bool{
		return false;
	}
}
