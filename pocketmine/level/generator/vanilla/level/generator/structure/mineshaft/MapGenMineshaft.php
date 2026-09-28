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

namespace pocketmine\level\generator\vanilla\level\generator\structure\mineshaft;

use pocketmine\level\generator\vanilla\level\format\Chunk;
use pocketmine\level\generator\vanilla\level\generator\structure\MapGenStructure;
use pocketmine\level\generator\vanilla\level\generator\structure\StructureStart;
use pocketmine\level\generator\vanilla\utils\Random;
use function abs;
use function in_array;
use function max;

/**
 * Abandoned mineshafts of the overworld
 */
class MapGenMineshaft extends MapGenStructure
{
	/** Chance of a chunk to be the start of a mineshaft */
	private const CHANCE = 0.004;
	/** Badlands (mesa) biomes: mineshafts there are made of dark oak */
	private const MESA_BIOMES = [37, 38, 39, 165, 166, 167];

	/**
	 * @param \Closure $biomeAt fn(int $x, int $z) : int, the biome at a block position
	 */
	public function __construct(int $worldSeed, private \Closure $biomeAt, private int $seaLevel = 63)
	{
		parent::__construct($worldSeed);
	}

	protected function canSpawnStructureAtCoords(int $chunkX, int $chunkZ, Random $random) : bool
	{
		return $random->nextFloat() < self::CHANCE && $random->nextBoundedInt(80) < max(abs($chunkX), abs($chunkZ));
	}

	protected function createStart(int $chunkX, int $chunkZ, Random $random) : StructureStart
	{
		$biome = ($this->biomeAt)(($chunkX << Chunk::COORD_BIT_SIZE) + 8, ($chunkZ << Chunk::COORD_BIT_SIZE) + 8);
		$type = in_array($biome, self::MESA_BIOMES, true) ? MineshaftPiece::TYPE_MESA : MineshaftPiece::TYPE_NORMAL;
		return new MineshaftStart($chunkX, $chunkZ, $random, $type, $this->seaLevel);
	}
}
