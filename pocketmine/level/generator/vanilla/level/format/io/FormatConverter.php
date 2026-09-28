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

namespace pocketmine\level\generator\vanilla\level\format\io;

use pocketmine\level\generator\vanilla\level\generator\dimension\Overworld;
use pocketmine\level\generator\vanilla\level\generator\GeneratorManager;
use pocketmine\level\generator\vanilla\level\LevelCreationOptions;
use pocketmine\level\generator\vanilla\utils\Filesystem;
use Symfony\Component\Filesystem\Path;
use function basename;
use function crc32;
use function file_exists;
use function floor;
use function microtime;
use function mkdir;
use function random_bytes;
use function rename;
use function round;
use function rtrim;
use const DIRECTORY_SEPARATOR;

class FormatConverter
{
	private string $backupPath;
	private \Logger $logger;

	public function __construct(
		private LevelProvider $oldProvider,
		private WritableLevelProviderManagerEntry $newProvider,
		string $backupPath,
		\Logger $logger,
		private int $chunksPerProgressUpdate = 256
	) {
		$this->logger = new \PrefixedLogger($logger, "World Converter: " . $this->oldProvider->getLevelData()->getName());

		if (!file_exists($backupPath)) {
			@mkdir($backupPath, 0777, true);
		}
		$nextSuffix = "";
		do {
			$this->backupPath = Path::join($backupPath, basename($this->oldProvider->getPath()) . $nextSuffix);
			$nextSuffix = "_" . crc32(random_bytes(4));
		} while (file_exists($this->backupPath));
	}

	public function getBackupPath() : string
	{
		return $this->backupPath;
	}

	public function execute() : LevelProvider
	{
		$new = $this->generateNew();

		$this->populateLevelData($new->getLevelData());
		$this->convertTerrain($new);

		$path = $this->oldProvider->getPath();
		$this->oldProvider->close();
		$new->close();

		$this->logger->info("Backing up pre-conversion world to " . $this->backupPath);
		if (!@rename($path, $this->backupPath)) {
			$this->logger->warning("Moving old world files for backup failed, attempting copy instead. This might take a long time.");
			Filesystem::recursiveCopy($path, $this->backupPath);
			Filesystem::recursiveUnlink($path);
		}
		if (!@rename($new->getPath(), $path)) {
			//we don't expect this to happen because worlds/ should most likely be all on the same FS, but just in case...
			$this->logger->debug("Relocation of new world files to location failed, attempting copy and delete instead");
			Filesystem::recursiveCopy($new->getPath(), $path);
			Filesystem::recursiveUnlink($new->getPath());
		}

		$this->logger->info("Conversion completed");
		return $this->newProvider->fromPath($path);
	}

	private function generateNew() : WritableLevelProvider
	{
		$this->logger->info("Generating new world");
		$data = $this->oldProvider->getLevelData();

		$convertedOutput = rtrim($this->oldProvider->getPath(), "/" . DIRECTORY_SEPARATOR) . "_converted" . DIRECTORY_SEPARATOR;
		if (file_exists($convertedOutput)) {
			$this->logger->info("Found previous conversion attempt, deleting...");
			Filesystem::recursiveUnlink($convertedOutput);
		}
		$this->newProvider->generate(
			$convertedOutput,
			$data->getName(),
			LevelCreationOptions::create()
			//TODO: defaulting to NORMAL here really isn't very good behaviour, but it's consistent with what we already
			//did previously; besides, WorldManager checks for unknown generators before this is reached anyway.
			->setGeneratorClass(GeneratorManager::getGenerator($data->getGenerator()) ?? Overworld::class)
			->setGeneratorOptions("")
			->setSeed($data->getSeed())
			->setSpawnPosition($data->getSpawn())
			->setDifficulty($data->getDifficulty())
		);

		return $this->newProvider->fromPath($convertedOutput);
	}

	private function populateLevelData(LevelData $data) : void
	{
		$this->logger->info("Converting world manifest");
		$oldData = $this->oldProvider->getLevelData();
		$data->setDifficulty($oldData->getDifficulty());
		$data->setLightningLevel($oldData->getLightningLevel());
		$data->setLightningTime($oldData->getLightningTime());
		$data->setRainLevel($oldData->getRainLevel());
		$data->setRainTime($oldData->getRainTime());
		$data->setSpawn($oldData->getSpawn());
		$data->setTime($oldData->getTime());

		$data->save();
		$this->logger->info("Finished converting manifest");
		//TODO: add more properties as-needed
	}

	private function convertTerrain(WritableLevelProvider $new) : void{
		$this->logger->info("Calculating chunk count");
		$count = $this->oldProvider->calculateChunkCount();
		$this->logger->info("Discovered $count chunks");

		$counter = 0;

		$start = microtime(true);
		$thisRound = $start;
		foreach ($this->oldProvider->getAllChunks(true, $this->logger) as $coords => $chunk) {
			[$chunkX, $chunkZ] = $coords;
			$chunk->setChanged();
			$new->saveChunk($chunk);
			$counter++;
			if (($counter % $this->chunksPerProgressUpdate) === 0) {
				$time = microtime(true);
				$diff = $time - $thisRound;
				$thisRound = $time;
				$this->logger->info("Converted $counter / $count chunks (" . floor($this->chunksPerProgressUpdate / $diff) . " chunks/sec)");
			}
		}
		$total = microtime(true) - $start;
		$this->logger->info("Converted $counter / $counter chunks in " . round($total, 3) . " seconds (" . floor($counter / $total) . " chunks/sec)");
	}
}
