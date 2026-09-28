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

namespace pocketmine\level\generator\vanilla\utils;

use function str_repeat;
use function strlen;
use function trim;

final class Git
{
	private function __construct()
	{
		//NOOP
	}

	/**
	 * Returns the git hash of the currently checked out head of the given repository, or null on failure.
	 *
	 * @param bool $dirty reference parameter, set to whether the repo has local changes
	 */
	public static function getRepositoryState(string $dir, bool &$dirty) : ?string
	{
		if (Utils::execute("git -C \"$dir\" rev-parse HEAD", $out) === 0 && $out !== false && strlen($out = trim($out)) === 40) {
			if (Utils::execute("git -C \"$dir\" diff --quiet") === 1 || Utils::execute("git -C \"$dir\" diff --cached --quiet") === 1) { //Locally-modified
				$dirty = true;
			}
			return $out;
		}
		return null;
	}

	/**
	 * Infallible, returns a string representing git state, or a string of zeros on failure.
	 * If the repo is dirty, a "-dirty" suffix is added.
	 */
	public static function getRepositoryStatePretty(string $dir) : string
	{
		$dirty = false;
		$detectedHash = self::getRepositoryState($dir, $dirty);
		if ($detectedHash !== null) {
			return $detectedHash . ($dirty ? "-dirty" : "");
		}
		return str_repeat("00", 20);
	}
}
