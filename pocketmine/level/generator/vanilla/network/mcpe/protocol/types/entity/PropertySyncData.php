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

namespace pocketmine\level\generator\vanilla\network\mcpe\protocol\types\entity;

use pocketmine\level\generator\vanilla\network\mcpe\NetworkBinaryStream;

use function count;

final class PropertySyncData
{
	/**
	 * @param int[]   $intProperties
	 * @param float[] $floatProperties
	 * @phpstan-param array<int, int> $intProperties
	 * @phpstan-param array<int, float> $floatProperties
	 */
	public function __construct(
		private array $intProperties,
		private array $floatProperties,
	) {
	}

	/**
	 * @return int[]
	 * @phpstan-return array<int, int>
	 */
	public function getIntProperties() : array
	{
		return $this->intProperties;
	}

	/**
	 * @return float[]
	 * @phpstan-return array<int, float>
	 */
	public function getFloatProperties() : array
	{
		return $this->floatProperties;
	}

	public static function read(NetworkBinaryStream $in) : self
	{
		$intProperties = [];
		$floatProperties = [];

		for ($i = 0, $count = $in->getUnsignedVarInt(); $i < $count; ++$i) {
			$intProperties[$in->getUnsignedVarInt()] = $in->getVarInt();
		}
		for ($i = 0, $count = $in->getUnsignedVarInt(); $i < $count; ++$i) {
			$floatProperties[$in->getUnsignedVarInt()] = $in->getLFloat();
		}

		return new self($intProperties, $floatProperties);
	}

	public function write(NetworkBinaryStream $out) : void
	{
		$out->putUnsignedVarInt(count($this->intProperties));
		foreach ($this->intProperties as $key => $value) {
			$out->putUnsignedVarInt($key);
			$out->putVarInt($value);
		}
		$out->putUnsignedVarInt(count($this->floatProperties));
		foreach ($this->floatProperties as $key => $value) {
			$out->putUnsignedVarInt($key);
			$out->putLFloat($value);
		}
	}
}
