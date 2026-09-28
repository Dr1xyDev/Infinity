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

namespace pocketmine\level\generator\vanilla\network\mcpe\protocol\serializer;

use pocketmine\level\generator\vanilla\network\mcpe\convert\PacketIdTranslator;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\DataPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\PacketDecodeException;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\PacketPool;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\UnknownPacket;
use pocketmine\level\generator\vanilla\utils\BinaryDataException;
use pocketmine\level\generator\vanilla\utils\BinaryStream;

use function strlen;

class PacketBatch
{
	private function __construct()
	{
		//NOOP
	}

	/**
	 * @phpstan-return \Generator<int, string, void, void>
	 * @throws PacketDecodeException
	 */
	final public static function decodeRaw(BinaryStream $stream) : \Generator
	{
		$c = 0;
		while (!$stream->feof()) {
			try {
				$length = $stream->getUnsignedVarInt();
				$buffer = $stream->get($length);
			} catch (BinaryDataException $e) {
				throw new PacketDecodeException("Error decoding packet $c in batch: " . $e->getMessage(), 0, $e);
			}
			yield $buffer;
			$c++;
		}
	}

	/**
	 * @param string[] $packets
	 * @phpstan-param list<string> $packets
	 */
	final public static function encodeRaw(BinaryStream $stream, array $packets) : void
	{
		foreach ($packets as $packet) {
			$stream->putUnsignedVarInt(strlen($packet));
			$stream->put($packet);
		}
	}

	/**
	 * @phpstan-return \Generator<int, DataPacket, void, void>
	 * @throws PacketDecodeException
	 */
	final public static function decodePackets(BinaryStream $stream, int $protocolVersion) : \Generator
	{
		$c = 0;
		foreach (self::decodeRaw($stream) as $packetBuffer) {
			$packet = PacketPool::getPacket($packetBuffer, $protocolVersion);
			$packet->setProtocol($protocolVersion);
			if (!($packet instanceof UnknownPacket)) {
				try {
					$packet->decode();
				} catch (PacketDecodeException $e) {
					throw new PacketDecodeException("Error decoding packet $c in batch: " . $e->getMessage(), 0, $e);
				}
				yield $packet;
			} else {
				throw new PacketDecodeException("Unknown packet $c in batch");
			}
			$c++;
		}
	}

	/**
	 * @param DataPacket[] $packets
	 * @phpstan-param list<DataPacket> $packets
	 */
	final public static function encodePackets(BinaryStream $stream, array $packets, int $protocolVersion) : void
	{
		foreach ($packets as $packet) {
			if (PacketIdTranslator::getInstance()->toNetworkId($protocolVersion, $packet->pid()) === null) {
				continue;
			}

			$packet->setProtocol($protocolVersion);
			$packet->encode();
			$stream->putUnsignedVarInt(strlen($packet->getBuffer()));
			$stream->put($packet->getBuffer());
		}
	}
}
