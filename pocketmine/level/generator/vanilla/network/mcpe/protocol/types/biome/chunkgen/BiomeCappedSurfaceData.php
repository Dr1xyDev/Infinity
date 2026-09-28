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
use function count;

final class BiomeCappedSurfaceData
{
	/**
	 * @param int[] $floorBlocks
	 * @param int[] $ceilingBlocks
	 */
	public function __construct(
		private array $floorBlocks,
		private array $ceilingBlocks,
		private ?int $seaBlock,
		private ?int $foundationBlock,
		private ?int $beachBlock,
	) {
	}

	/**
	 * @return int[]
	 */
	public function getFloorBlocks() : array
	{
		return $this->floorBlocks;
	}

	/**
	 * @return int[]
	 */
	public function getCeilingBlocks() : array
	{
		return $this->ceilingBlocks;
	}

	public function getSeaBlock() : ?int
	{
		return $this->seaBlock;
	}

	public function getFoundationBlock() : ?int
	{
		return $this->foundationBlock;
	}

	public function getBeachBlock() : ?int
	{
		return $this->beachBlock;
	}

	public static function read(NetworkBinaryStream $in) : self
	{
		$floorBlocks = [];
		for ($i = 0, $count = $in->getUnsignedVarInt(); $i < $count; ++$i) {
			$floorBlocks[] = $in->getLInt();
		}

		$ceilingBlocks = [];
		for ($i = 0, $count = $in->getUnsignedVarInt(); $i < $count; ++$i) {
			$ceilingBlocks[] = $in->getLInt();
		}

		$seaBlock = $in->getOptional($in->getLInt(...));
		$foundationBlock = $in->getOptional($in->getLInt(...));
		$beachBlock = $in->getOptional($in->getLInt(...));

		return new self(
			$floorBlocks,
			$ceilingBlocks,
			$seaBlock,
			$foundationBlock,
			$beachBlock
		);
	}

	public function write(NetworkBinaryStream $out) : void
	{
		$out->putUnsignedVarInt(count($this->floorBlocks));
		foreach ($this->floorBlocks as $block) {
			$out->putLInt($block);
		}

		$out->putUnsignedVarInt(count($this->ceilingBlocks));
		foreach ($this->ceilingBlocks as $block) {
			$out->putLInt($block);
		}

		$out->putOptional($this->seaBlock, $out->putLInt(...));
		$out->putOptional($this->foundationBlock, $out->putLInt(...));
		$out->putOptional($this->beachBlock, $out->putLInt(...));
	}
}
