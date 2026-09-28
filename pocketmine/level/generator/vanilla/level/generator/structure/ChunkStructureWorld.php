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

namespace pocketmine\level\generator\vanilla\level\generator\structure;

use pocketmine\level\generator\vanilla\block\Block;
use pocketmine\level\generator\vanilla\level\format\Chunk;
use pocketmine\level\generator\vanilla\nbt\tag\CompoundTag;

/**
 * Lets structures write into the single chunk which is being generated
 */
final class ChunkStructureWorld implements StructureWorld
{
	private int $baseX;
	private int $baseZ;
	/** @var int[] column index => highest non-air y */
	private array $columnTops = [];

	public function __construct(private Chunk $chunk, private int $maxY = 255)
	{
		$this->baseX = $chunk->getX() << Chunk::COORD_BIT_SIZE;
		$this->baseZ = $chunk->getZ() << Chunk::COORD_BIT_SIZE;
	}

	public function getBoundingBox() : StructureBoundingBox
	{
		return new StructureBoundingBox($this->baseX, 0, $this->baseZ, $this->baseX + Chunk::EDGE_LENGTH - 1, $this->maxY, $this->baseZ + Chunk::EDGE_LENGTH - 1);
	}

	private function isInside(int $x, int $y, int $z) : bool
	{
		return $y >= 0 && $y <= $this->maxY && ($x - $this->baseX) >= 0 && ($x - $this->baseX) < Chunk::EDGE_LENGTH && ($z - $this->baseZ) >= 0 && ($z - $this->baseZ) < Chunk::EDGE_LENGTH;
	}

	public function getFullBlock(int $x, int $y, int $z) : int
	{
		if (!$this->isInside($x, $y, $z)) {
			return Block::AIR << Block::INTERNAL_METADATA_BITS;
		}
		return $this->chunk->getFullBlock($x - $this->baseX, $y, $z - $this->baseZ);
	}

	public function setFullBlock(int $x, int $y, int $z, int $fullBlock) : void
	{
		if ($this->isInside($x, $y, $z)) {
			$this->chunk->setFullBlock($x - $this->baseX, $y, $z - $this->baseZ, $fullBlock);
		}
	}

	public function isSkyVisible(int $x, int $y, int $z) : bool
	{
		if (!$this->isInside($x, $y, $z)) {
			return false;
		}
		$lx = $x - $this->baseX;
		$lz = $z - $this->baseZ;
		$index = ($lx << Chunk::COORD_BIT_SIZE) | $lz;
		if (!isset($this->columnTops[$index])) {
			$top = -1;
			for ($yy = $this->maxY; $yy >= 0; --$yy) {
				if (($this->chunk->getFullBlock($lx, $yy, $lz) >> Block::INTERNAL_METADATA_BITS) !== Block::AIR) {
					$top = $yy;
					break;
				}
			}
			$this->columnTops[$index] = $top;
		}
		return $y > $this->columnTops[$index];
	}

	public function addTile(CompoundTag $nbt) : void
	{
		$this->chunk->addNBTTile($nbt);
	}

	public function addEntity(CompoundTag $nbt) : void
	{
		$this->chunk->addNBTEntity($nbt);
	}
}
