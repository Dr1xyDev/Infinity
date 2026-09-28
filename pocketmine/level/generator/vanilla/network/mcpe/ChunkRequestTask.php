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

namespace pocketmine\level\generator\vanilla\network\mcpe;

use pocketmine\level\generator\vanilla\block\BlockFactory;
use pocketmine\level\generator\vanilla\level\format\Chunk;
use pocketmine\level\generator\vanilla\level\format\io\FastChunkSerializer;
use pocketmine\level\generator\vanilla\level\Level;
use pocketmine\level\generator\vanilla\network\mcpe\compression\NetworkCompression;
use pocketmine\level\generator\vanilla\network\mcpe\convert\block\BlockProtocolConvertor;
use pocketmine\level\generator\vanilla\network\mcpe\convert\block\DynamicBlockStateResolver;
use pocketmine\level\generator\vanilla\network\mcpe\convert\block\RuntimeBlockMapping;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\LevelChunkPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ProtocolInfo;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\serializer\PacketBatch;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\ChunkPosition;
use pocketmine\level\generator\vanilla\network\mcpe\serializer\ChunkSerializer;
use pocketmine\level\generator\vanilla\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\level\generator\vanilla\thread\NonThreadSafeValue;
use pocketmine\level\generator\vanilla\tile\Spawnable;
use pocketmine\level\generator\vanilla\utils\BinaryStream;
use function pack;
use function strlen;
use function unpack;

class ChunkRequestTask extends AsyncTask
{
	protected int $levelId;
	protected string $chunk;
	protected int $chunkX;
	protected int $chunkZ;
	protected int $dimensionId;
	protected string $tiles;
	protected int $compressionLevel;
	protected int $protocol;
	/** @phpstan-var NonThreadSafeValue<BlockProtocolConvertor> */
	protected NonThreadSafeValue $blockProtocolConvertor;
	/** packed (position key, runtime ID) pairs of neighbour-dependent states, see DynamicBlockStateResolver */
	protected string $dynamicStates = "";

	public function __construct(Level $level, int $dimensionId, Chunk $chunk, int $protocol)
	{
		$this->levelId = $level->getId();
		$this->compressionLevel = $level->getServer()->networkCompressionLevel;

		$this->chunkX = $chunk->getX();
		$this->chunkZ = $chunk->getZ();
		$this->dimensionId = $dimensionId;
		$this->chunk = FastChunkSerializer::serializeTerrain($chunk);

		$tiles = "";
		foreach ($chunk->getTiles() as $tile) {
			if ($tile instanceof Spawnable) {
				$tiles .= $tile->getSerializedSpawnCompound($protocol);
			}
		}
		$this->tiles = $tiles;

		$this->protocol = $protocol;
		$this->blockProtocolConvertor = new NonThreadSafeValue(BlockProtocolConvertor::getInstance());

		if (DynamicBlockStateResolver::isSupported($protocol)) {
			//needs the neighbouring chunks, which are only available on the main thread
			$dynamicStates = "";
			foreach (DynamicBlockStateResolver::getInstance($protocol)->computeChunk($level, $chunk) as $key => $runtimeId) {
				$dynamicStates .= pack("VV", $key, $runtimeId);
			}
			$this->dynamicStates = $dynamicStates;
		}
	}

	public function onRun() : void
	{
		BlockFactory::init();

		$chunk = FastChunkSerializer::deserializeTerrain($this->chunk);
		$dimensionId = $this->dimensionId;

		$protocol = $this->protocol;

		$blockProtocolConvertor = $this->blockProtocolConvertor->deserialize();
		$cacheFullBlocks = [];
		if ($protocol >= ProtocolInfo::PROTOCOL_407) {
			$runtimeBlockMapping = RuntimeBlockMapping::getInstance($protocol);
			$blockLegacyToRuntime = function (int $fullId) use ($protocol, $blockProtocolConvertor, $runtimeBlockMapping, &$cacheFullBlocks) : int {
				if (!isset($cacheFullBlocks[$fullId])) {
					$block = BlockFactory::fromFullBlock($fullId);
					$blockProtocol = $blockProtocolConvertor->get($block, $protocol) ?? $block;
					$cacheFullBlocks[$fullId] = $blockProtocol->getFullId();
				}

				return $runtimeBlockMapping->toRuntimeId($cacheFullBlocks[$fullId]);
			};
		} else {
			$blockLegacyToRuntime = function (int $fullId) use ($protocol, $blockProtocolConvertor, &$cacheFullBlocks) : int {
				if (!isset($cacheFullBlocks[$fullId])) {
					$block = BlockFactory::fromFullBlock($fullId);
					$blockProtocol = $blockProtocolConvertor->get($block, $protocol) ?? $block;
					$cacheFullBlocks[$fullId] = $blockProtocol->getFullId();
				}

				return $cacheFullBlocks[$fullId];
			};
		}

		unset($cacheFullBlocks);

		$biomeLegacyToRuntime = function (int $biomeId) : int {
			return $biomeId; //TODO: old version
		};

		$pk = LevelChunkPacket::create(
			new ChunkPosition($this->chunkX, $this->chunkZ),
			$dimensionId,
			ChunkSerializer::getSubChunkCount($chunk, $dimensionId, $protocol),
			false,
			null,
			ChunkSerializer::serializeFullChunk($chunk, $blockLegacyToRuntime, $biomeLegacyToRuntime, $dimensionId, $protocol, $this->unpackDynamicStates()) . $this->tiles
		);
		$pk->setProtocol($protocol);

		$stream = new BinaryStream();
		PacketBatch::encodePackets($stream, [$pk], $protocol);

		$this->setResult(NetworkCompression::compress($stream->getBuffer(), $protocol, $this->compressionLevel));
	}

	/**
	 * @return int[]
	 */
	private function unpackDynamicStates() : array
	{
		$result = [];
		for ($offset = 0, $length = strlen($this->dynamicStates); $offset < $length; $offset += 8) {
			[, $key, $runtimeId] = unpack("V2", $this->dynamicStates, $offset);
			$result[$key] = $runtimeId;
		}
		return $result;
	}

	public function onCompletion(Server $server) : void
	{
		$level = $server->getLevel($this->levelId);
		if ($level instanceof Level) {
			if ($this->hasResult()) {
				$level->chunkRequestCallback($this->chunkX, $this->chunkZ, $this->protocol, $this->getResult());
			} else {
				$server->getLogger()->error("Chunk request (protocol: {$this->protocol}) for world #" . $this->levelId . ", x=" . $this->chunkX . ", z=" . $this->chunkZ . " doesn't have any result data");
			}
		} else {
			$server->getLogger()->debug("Dropped chunk task due to world not loaded");
		}
	}
}
