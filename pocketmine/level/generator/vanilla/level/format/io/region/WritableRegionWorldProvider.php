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

namespace pocketmine\level\generator\vanilla\level\format\io\region;

use pocketmine\level\generator\vanilla\level\format\Chunk;
use pocketmine\level\generator\vanilla\level\format\io\data\JavaLevelData;
use pocketmine\level\generator\vanilla\level\format\io\WritableLevelProvider;
use pocketmine\level\generator\vanilla\level\LevelCreationOptions;
use Symfony\Component\Filesystem\Path;
use function file_exists;
use function mkdir;

/**
 * This class implements the stuff needed for general region-based world providers to support saving.
 * While this isn't used at the time of writing, it may come in useful if Java 1.13 Anvil support is ever implemented,
 * or for a custom world format based on the region concept.
 */
abstract class WritableRegionWorldProvider extends RegionLevelProvider implements WritableLevelProvider
{
	public static function generate(string $path, string $name, LevelCreationOptions $options) : void
	{
		if (!file_exists($path)) {
			mkdir($path, 0777, true);
		}

		$regionPath = Path::join($path, "region");
		if (!file_exists($regionPath)) {
			mkdir($regionPath, 0777);
		}

		JavaLevelData::generate($path, $name, $options, static::getPcWorldFormatVersion());
	}

	abstract protected function serializeChunk(Chunk $chunk) : string;

	public function saveChunk(Chunk $chunk) : void
	{
		self::getRegionIndex($chunk->getX(), $chunk->getZ(), $regionX, $regionZ);
		$this->loadRegion($regionX, $regionZ)->writeChunk($chunk->getX() & 0x1f, $chunk->getZ() & 0x1f, $this->serializeChunk($chunk));
	}
}
