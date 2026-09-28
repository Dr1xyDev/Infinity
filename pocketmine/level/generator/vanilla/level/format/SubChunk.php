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

namespace pocketmine\level\generator\vanilla\level\format;

use pocketmine\level\generator\vanilla\block\Block;
use pocketmine\world\format\PalettedBlockArray;
use function array_values;
use function assert;
use function chr;
use function count;
use function ord;
use function str_repeat;
use function strlen;
use function substr;
use function substr_count;

class SubChunk implements SubChunkInterface
{
	public const COORD_BIT_SIZE = 4;
	public const COORD_MASK = ~(~0 << self::COORD_BIT_SIZE);
	public const EDGE_LENGTH = 1 << self::COORD_BIT_SIZE;

	/** @var PalettedBlockArray[] */
	private array $blockLayers;
	private int $emptyBlockId;

	protected string $blockLight = "";
	protected string $skyLight = "";

	private static function assignData(&$target, string $data, int $length, string $value)
	{
		if (strlen($data) !== $length) {
			assert($data === "", "Invalid non-zero length given, expected $length, got " . strlen($data));
			$target = str_repeat($value, $length);
		} else {
			$target = $data;
		}
	}

	public function __construct(int $emptyBlockId, array $blocks, string $skyLight = "", string $blockLight = "")
	{
		$this->emptyBlockId = $emptyBlockId;
		$this->blockLayers = $blocks;
		self::assignData($this->skyLight, $skyLight, 2048, "\xff");
		self::assignData($this->blockLight, $blockLight, 2048, "\x00");
		$this->collectGarbage();
	}

	public function isEmpty(bool $checkLight = true) : bool
	{
		return (
			count($this->blockLayers) === 0 &&
			(!$checkLight || (
				substr_count($this->skyLight, "\xff") === 2048 &&
					substr_count($this->blockLight, "\x00") === 2048
			))
		);
	}

	/**
	 * Returns the block used as the default. This is assumed to refer to air.
	 * If all the blocks in a subchunk layer are equal to this block, the layer is assumed to be empty.
	 */
	public function getEmptyBlockId() : int
	{
		return $this->emptyBlockId;
	}

	/**
	 * @return PalettedBlockArray[]
	 */
	public function getBlockLayers() : array
	{
		return $this->blockLayers;
	}

	public function getBlockId(int $x, int $y, int $z) : int
	{
		return $this->getFullBlock($x, $y, $z) >> Block::INTERNAL_METADATA_BITS;
	}

	public function setBlockId(int $x, int $y, int $z, int $id) : bool
	{
		$this->setBlock($x, $y, $z, $id);
		return true;
	}

	public function getBlockData(int $x, int $y, int $z) : int
	{
		return $this->getFullBlock($x, $y, $z) & Block::INTERNAL_METADATA_MASK;
	}

	public function setBlockData(int $x, int $y, int $z, int $data) : bool
	{
		$this->setBlock($x, $y, $z, null, $data);
		return true;
	}

	public function getFullBlock(int $x, int $y, int $z) : int
	{
		if (count($this->blockLayers) === 0) {
			return $this->emptyBlockId;
		}
		return $this->blockLayers[0]->get($x, $y, $z);
	}

	public function setFullBlock(int $x, int $y, int $z, int $block) : bool
	{
		if (count($this->blockLayers) === 0) {
			$this->blockLayers[] = new PalettedBlockArray($this->emptyBlockId);
		}
		$this->blockLayers[0]->set($x, $y, $z, $block);

		return true;
	}

	public function setBlock(int $x, int $y, int $z, ?int $id = null, ?int $data = null) : bool
	{
		$changed = false;
		if ($id !== null) {
			$fullBlock = $this->getFullBlock($x, $y, $z);
			if ($id !== ($fullBlock >> Block::INTERNAL_METADATA_BITS)) {
				$this->setFullBlock($x, $y, $z, ($id << Block::INTERNAL_METADATA_BITS) | ($fullBlock & Block::INTERNAL_METADATA_MASK));
				$changed = true;
			}
		}

		if ($data !== null) {
			$fullBlock = $this->getFullBlock($x, $y, $z);
			if ($data !== ($fullBlock & Block::INTERNAL_METADATA_MASK)) {
				$this->setFullBlock($x, $y, $z, (($fullBlock >> Block::INTERNAL_METADATA_BITS) << Block::INTERNAL_METADATA_BITS) | ($data & Block::INTERNAL_METADATA_MASK));
				$changed = true;
			}
		}

		return $changed;
	}

	public function getBlockLight(int $x, int $y, int $z) : int
	{
		return (ord($this->blockLight[($x << 7) | ($z << 3) | ($y >> 1)]) >> (($y & 1) << 2)) & 0xf;
	}

	public function setBlockLight(int $x, int $y, int $z, int $level) : bool
	{
		$i = ($x << 7) | ($z << 3) | ($y >> 1);

		$shift = ($y & 1) << 2;
		$byte = ord($this->blockLight[$i]);
		$this->blockLight[$i] = chr(($byte & ~(0xf << $shift)) | (($level & 0xf) << $shift));

		return true;
	}

	public function getBlockSkyLight(int $x, int $y, int $z) : int
	{
		return (ord($this->skyLight[($x << 7) | ($z << 3) | ($y >> 1)]) >> (($y & 1) << 2)) & 0xf;
	}

	public function setBlockSkyLight(int $x, int $y, int $z, int $level) : bool
	{
		$i = ($x << 7) | ($z << 3) | ($y >> 1);

		$shift = ($y & 1) << 2;
		$byte = ord($this->skyLight[$i]);
		$this->skyLight[$i] = chr(($byte & ~(0xf << $shift)) | (($level & 0xf) << $shift));

		return true;
	}

	public function getHighestBlockAt(int $x, int $z) : int
	{
		if (count($this->blockLayers) === 0) {
			return -1;
		}
		for ($y = self::EDGE_LENGTH - 1; $y >= 0; --$y) {
			if ($this->blockLayers[0]->get($x, $y, $z) !== $this->emptyBlockId) {
				return $y;
			}
		}

		return -1; //highest block not in this subchunk
	}

	public function getBlockLightColumn(int $x, int $z) : string
	{
		return substr($this->blockLight, ($x << 7) | ($z << 3), 8);
	}

	public function getBlockSkyLightColumn(int $x, int $z) : string
	{
		return substr($this->skyLight, ($x << 7) | ($z << 3), 8);
	}

	public function getBlockSkyLightArray() : string
	{
		assert(strlen($this->skyLight) === 2048, "Wrong length of skylight array, expecting 2048 bytes, got " . strlen($this->skyLight));
		return $this->skyLight;
	}

	public function setBlockSkyLightArray(string $data)
	{
		assert(strlen($data) === 2048, "Wrong length of skylight array, expecting 2048 bytes, got " . strlen($data));
		$this->skyLight = $data;
	}

	public function getBlockLightArray() : string
	{
		assert(strlen($this->blockLight) === 2048, "Wrong length of light array, expecting 2048 bytes, got " . strlen($this->blockLight));
		return $this->blockLight;
	}

	public function setBlockLightArray(string $data)
	{
		assert(strlen($data) === 2048, "Wrong length of light array, expecting 2048 bytes, got " . strlen($data));
		$this->blockLight = $data;
	}

	public function __debugInfo()
	{
		return [];
	}

	public function collectGarbage() : void
	{
		/*
		 * This strange looking code is designed to exploit PHP's copy-on-write behaviour. Assigning will copy a
		 * reference to the const instead of duplicating the whole string. The string will only be duplicated when
		 * modified, which is perfect for this purpose.
		 */
		foreach ($this->blockLayers as $k => $layer) {
			$layer->collectGarbage();

			foreach ($layer->getPalette() as $p) {
				if ($p !== $this->emptyBlockId) {
					continue 2;
				}
			}
			unset($this->blockLayers[$k]);
		}
		$this->blockLayers = array_values($this->blockLayers);
	}
}
