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

interface ResourcePack
{
	/**
	 * Returns the path to the resource pack. This might be a file or a directory, depending on the type of pack.
	 */
	public function getPath() : string;

	/**
	 * Returns the human-readable name of the resource pack
	 */
	public function getPackName() : string;

	/**
	 * Returns the pack's UUID as a human-readable string
	 */
	public function getPackId() : string;

	/**
	 * Returns the size of the pack on disk in bytes.
	 */
	public function getPackSize() : int;

	/**
	 * Returns a version number for the pack in the format major.minor.patch
	 */
	public function getPackVersion() : string;

	/**
	 * Returns the raw SHA256 sum of the compressed resource pack zip. This is used by clients to validate pack downloads.
	 * @return string byte-array length 32 bytes
	 */
	public function getSha256() : string;

	/**
	 * Returns a chunk of the resource pack zip as a byte-array for sending to clients.
	 *
	 * Note that resource packs must **always** be in zip archive format for sending.
	 * A folder resource loader may need to perform on-the-fly compression for this purpose.
	 *
	 * @param int $start  Offset to start reading the chunk from
	 * @param int $length Maximum length of data to return.
	 *
	 * @return string byte-array
	 * @throws \InvalidArgumentException if the chunk does not exist
	 */
	public function getPackChunk(int $start, int $length) : string;

	public function getEncryptionKey() : ?string;

	public function setEncryptionKey(string $key);
}
