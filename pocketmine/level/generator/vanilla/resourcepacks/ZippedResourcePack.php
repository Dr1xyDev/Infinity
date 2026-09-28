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

namespace pocketmine\level\generator\vanilla\resourcepacks;

use Ahc\Json\Comment as CommentedJsonDecoder;
use stdClass;

use function array_key_first;
use function assert;
use function count;
use function fclose;
use function feof;
use function file_exists;
use function filesize;
use function fopen;
use function fread;
use function fseek;
use function gettype;
use function hash_file;
use function implode;
use function preg_match;
use function strlen;

class ZippedResourcePack implements ResourcePack
{
	protected const MAX_CACHE_SIZE = 64;

	/**
	 * Performs basic validation checks on a resource pack's manifest.json.
	 * TODO: add more manifest validation
	 */
	public static function verifyManifest(stdClass $manifest) : bool
	{
		if (!isset($manifest->format_version) || !isset($manifest->header) || !isset($manifest->modules)) {
			return false;
		}

		//Right now we don't care about anything else, only the stuff we're sending to clients.
		return
			isset($manifest->header->description) &&
			isset($manifest->header->name) &&
			isset($manifest->header->uuid) &&
			isset($manifest->header->version) &&
			count($manifest->header->version) === 3;
	}

	protected string $path;
	/** @var stdClass */
	protected $manifest;
	protected ?string $sha256 = null;
	/** @var resource */
	protected $fileResource;
	protected string $encryptionKey;

	/** @var array<string, string>  */
	protected array $chunkCache = [];

	/**
	 * @param string $zipPath Path to the resource pack zip
	 * @throws ResourcePackException
	 */
	public function __construct(string $zipPath)
	{
		$this->path = $zipPath;

		if (!file_exists($zipPath)) {
			throw new ResourcePackException("File not found");
		}

		$archive = new \ZipArchive();
		if (($openResult = $archive->open($zipPath)) !== true) {
			throw new ResourcePackException("Encountered ZipArchive error code $openResult while trying to open $zipPath");
		}

		if (($manifestData = $archive->getFromName("manifest.json")) === false) {
			$manifestPath = null;
			$manifestIdx = null;
			for ($i = 0; $i < $archive->numFiles; ++$i) {
				$name = $archive->getNameIndex($i);
				if (
					($manifestPath === null || strlen($name) < strlen($manifestPath)) &&
					preg_match('#.*/manifest.json$#', $name) === 1
				) {
					$manifestPath = $name;
					$manifestIdx = $i;
				}
			}
			if ($manifestIdx !== null) {
				$manifestData = $archive->getFromIndex($manifestIdx);
				assert($manifestData !== false);
			} elseif ($archive->locateName("pack_manifest.json") !== false) {
				throw new ResourcePackException("Unsupported old pack format");
			} else {
				throw new ResourcePackException("manifest.json not found in the archive root");
			}
		}

		$archive->close();

		//maybe comments in the json, use stripped decoder (thanks mojang)
		try {
			$manifest = (new CommentedJsonDecoder())->decode($manifestData);
		} catch (\RuntimeException $e) {
			throw new ResourcePackException("Failed to parse manifest.json: " . $e->getMessage(), $e->getCode(), $e);
		}
		if (!($manifest instanceof stdClass)) {
			throw new ResourcePackException("manifest.json should contain a JSON object, not " . gettype($manifest));
		}
		if (!self::verifyManifest($manifest)) {
			throw new ResourcePackException("manifest.json is missing required fields");
		}

		$this->manifest = $manifest;

		$this->fileResource = fopen($zipPath, "rb");
	}

	public function __destruct()
	{
		fclose($this->fileResource);
	}

	public function getPath() : string
	{
		return $this->path;
	}

	public function getPackName() : string
	{
		return $this->manifest->header->name;
	}

	public function getPackVersion() : string
	{
		return implode(".", $this->manifest->header->version);
	}

	public function getPackId() : string
	{
		return $this->manifest->header->uuid;
	}

	public function getPackSize() : int
	{
		return filesize($this->path);
	}

	public function getSha256(bool $cached = true) : string
	{
		if ($this->sha256 === null || !$cached) {
			$this->sha256 = hash_file("sha256", $this->path, true);
		}
		return $this->sha256;
	}

	public function getPackChunk(int $start, int $length, bool $useCache = true) : string
	{
		$cacheKey = $start . ':' . $length;
		if ($useCache && isset($this->chunkCache[$cacheKey])) {
			return $this->chunkCache[$cacheKey];
		}

		fseek($this->fileResource, $start);
		if (feof($this->fileResource)) {
			throw new \InvalidArgumentException("Requested a resource pack chunk with invalid start offset");
		}

		$chunk = fread($this->fileResource, $length);
		if ($useCache) {
			if (count($this->chunkCache) >= self::MAX_CACHE_SIZE) {
				unset($this->chunkCache[array_key_first($this->chunkCache)]);
			}

			$this->chunkCache[$cacheKey] = $chunk;
		}

		return $chunk;
	}

	public function getEncryptionKey() : ?string
	{
		return $this->encryptionKey ?? null;
	}

	public function setEncryptionKey(string $key) : void
	{
		$this->encryptionKey = $key;
	}
}
