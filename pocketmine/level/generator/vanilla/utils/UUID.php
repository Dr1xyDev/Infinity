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

namespace pocketmine\level\generator\vanilla\utils;

use InvalidArgumentException;

use function bin2hex;
use function chr;
use function explode;
use function getmypid;
use function getmyuid;
use function hash;
use function hex2bin;
use function implode;
use function md5;
use function mt_rand;
use function ord;
use function strlen;
use function substr;
use function time;
use function trim;

class UUID
{
	/** @var int[] */
	private $parts;
	/** @var int */
	private $version;

	public function __construct(int $part1 = 0, int $part2 = 0, int $part3 = 0, int $part4 = 0, ?int $version = null)
	{
		$this->parts = [$part1, $part2, $part3, $part4];

		$this->version = $version ?? ($this->parts[1] & 0xf000) >> 12;
	}

	public function getVersion() : int
	{
		return $this->version;
	}

	public function equals(UUID $uuid) : bool
	{
		return $uuid->parts === $this->parts;
	}

	/**
	 * Creates an UUID from an hexadecimal representation
	 */
	public static function fromString(string $uuid, ?int $version = null) : UUID
	{
		$hex = "";
		foreach (explode("-", trim($uuid)) as $part) {
			if (strlen($part) % 2 !== 0) {
				$part = "0" . $part;
			}
			$hex .= $part;
		}
		return self::fromBinary(hex2bin($hex), $version);
	}

	/**
	 * Creates an UUID from a binary representation
	 *
	 * @throws InvalidArgumentException
	 */
	public static function fromBinary(string $uuid, ?int $version = null) : UUID
	{
		if (strlen($uuid) !== 16) {
			throw new InvalidArgumentException("Must have exactly 16 bytes");
		}

		return new UUID(Binary::readInt(substr($uuid, 0, 4)), Binary::readInt(substr($uuid, 4, 4)), Binary::readInt(substr($uuid, 8, 4)), Binary::readInt(substr($uuid, 12, 4)), $version);
	}

	/**
	 * Creates an UUIDv3 from binary data or list of binary data
	 */
	public static function fromData(string ...$data) : UUID
	{
		$hash = hash("md5", implode($data), true);

		return self::fromBinary($hash, 3);
	}

	public static function fromRandom() : UUID
	{
		return self::fromData(Binary::writeInt(time()), Binary::writeShort(($pid = getmypid()) !== false ? $pid : 0), Binary::writeShort(($uid = getmyuid()) !== false ? $uid : 0), Binary::writeInt(mt_rand(-0x7fffffff, 0x7fffffff)), Binary::writeInt(mt_rand(-0x7fffffff, 0x7fffffff)));
	}

	/**
	 * Derive UUID from XUID
	 */
	public static function fromXuid(string $xuid) : UUID{
		$hash = md5("pocket-auth-1-xuid:" . $xuid, true);
		$hash[6] = chr((ord($hash[6]) & 0x0f) | 0x30);
		$hash[8] = chr((ord($hash[8]) & 0x3f) | 0x80);
		return UUID::fromBinary($hash);
	}

	public function toBinary() : string
	{
		return Binary::writeInt($this->parts[0]) . Binary::writeInt($this->parts[1]) . Binary::writeInt($this->parts[2]) . Binary::writeInt($this->parts[3]);
	}

	public function toString() : string
	{
		$hex = bin2hex($this->toBinary());

		//xxxxxxxx-xxxx-Mxxx-Nxxx-xxxxxxxxxxxx 8-4-4-4-12
		return substr($hex, 0, 8) . "-" . substr($hex, 8, 4) . "-" . substr($hex, 12, 4) . "-" . substr($hex, 16, 4) . "-" . substr($hex, 20, 12);
	}

	public function __toString() : string
	{
		return $this->toString();
	}

	/**
	 * @return int
	 * @throws InvalidArgumentException
	 */
	public function getPart(int $partNumber)
	{
		if ($partNumber < 0 || $partNumber > 3) {
			throw new InvalidArgumentException("Invalid UUID part index $partNumber");
		}
		return $this->parts[$partNumber];
	}

	/**
	 * @return int[]
	 */
	public function getParts() : array
	{
		return $this->parts;
	}
}
