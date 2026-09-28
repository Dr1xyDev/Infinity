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

namespace pocketmine\level\generator\vanilla\network\mcpe\protocol\types\biome\chunkgen;

use pocketmine\level\generator\vanilla\network\mcpe\NetworkBinaryStream;

final class BiomeMesaSurfaceData
{
	public function __construct(
		private int $clayMaterial,
		private int $hardClayMaterial,
		private bool $brycePillars,
		private bool $forest,
	) {
	}

	public function getClayMaterial() : int
	{
		return $this->clayMaterial;
	}

	public function getHardClayMaterial() : int
	{
		return $this->hardClayMaterial;
	}

	public function hasBrycePillars() : bool
	{
		return $this->brycePillars;
	}

	public function hasForest() : bool
	{
		return $this->forest;
	}

	public static function read(NetworkBinaryStream $in) : self
	{
		$clayMaterial = $in->getLInt();
		$hardClayMaterial = $in->getLInt();
		$brycePillars = $in->getBool();
		$forest = $in->getBool();

		return new self(
			$clayMaterial,
			$hardClayMaterial,
			$brycePillars,
			$forest
		);
	}

	public function write(NetworkBinaryStream $out) : void
	{
		$out->putLInt($this->clayMaterial);
		$out->putLInt($this->hardClayMaterial);
		$out->putBool($this->brycePillars);
		$out->putBool($this->forest);
	}
}
