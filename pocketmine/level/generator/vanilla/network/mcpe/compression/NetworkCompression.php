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

namespace pocketmine\level\generator\vanilla\network\mcpe\compression;

use pocketmine\level\generator\vanilla\network\mcpe\protocol\ProtocolInfo;
use pocketmine\level\generator\vanilla\utils\Utils;

use function function_exists;
use function libdeflate_deflate_compress;
use function libdeflate_zlib_compress;
use function zlib_decode;
use function zlib_encode;

use const ZLIB_ENCODING_DEFLATE;
use const ZLIB_ENCODING_RAW;

final class NetworkCompression
{
	public static $LEVEL = 7;
	public static $THRESHOLD = 256;

	private function __construct()
	{
		//NOOP
	}

	public static function isCompressed(string $payload) : bool
	{
		return $payload[0] === "\x78"; // Check if the first byte indicates zlib compression
	}

	public static function decompress(string $payload) : string
	{
		$result = @zlib_decode($payload, 8 * 1024 * 1024);
		if ($result === false) {
			throw new DecompressionException("Failed to decompress data");
		}
		return $result;
	}

	public static function compress(string $payload, int $protocol, ?int $compressionLevel = null) : string
	{
		$level = $compressionLevel ?? self::$LEVEL;

		if ($protocol >= ProtocolInfo::PROTOCOL_407) {
			return function_exists('libdeflate_deflate_compress') ?
				libdeflate_deflate_compress($payload, $level) :
				Utils::assumeNotFalse(zlib_encode($payload, ZLIB_ENCODING_RAW, $level), "ZLIB compression failed");
		} else {
			return function_exists('libdeflate_deflate_compress') ?
				libdeflate_zlib_compress($payload, $level) :
				Utils::assumeNotFalse(zlib_encode($payload, ZLIB_ENCODING_DEFLATE, $level), "ZLIB compression failed");
		}
	}
}
