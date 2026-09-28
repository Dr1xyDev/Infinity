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

final class BiomeSurfaceMaterialData
{
	public function __construct(
		private int $topBlock,
		private int $midBlock,
		private int $seaFloorBlock,
		private int $foundationBlock,
		private int $seaBlock,
		private int $seaFloorDepth
	) {
	}

	public function getTopBlock() : int
	{
		return $this->topBlock;
	}

	public function getMidBlock() : int
	{
		return $this->midBlock;
	}

	public function getSeaFloorBlock() : int
	{
		return $this->seaFloorBlock;
	}

	public function getFoundationBlock() : int
	{
		return $this->foundationBlock;
	}

	public function getSeaBlock() : int
	{
		return $this->seaBlock;
	}

	public function getSeaFloorDepth() : int
	{
		return $this->seaFloorDepth;
	}

	public static function read(NetworkBinaryStream $in) : self
	{
		$topBlock = $in->getLInt();
		$midBlock = $in->getLInt();
		$seaFloorBlock = $in->getLInt();
		$foundationBlock = $in->getLInt();
		$seaBlock = $in->getLInt();
		$seaFloorDepth = $in->getLInt();

		return new self(
			$topBlock,
			$midBlock,
			$seaFloorBlock,
			$foundationBlock,
			$seaBlock,
			$seaFloorDepth
		);
	}

	public function write(NetworkBinaryStream $out) : void
	{
		$out->putLInt($this->topBlock);
		$out->putLInt($this->midBlock);
		$out->putLInt($this->seaFloorBlock);
		$out->putLInt($this->foundationBlock);
		$out->putLInt($this->seaBlock);
		$out->putLInt($this->seaFloorDepth);
	}
}
