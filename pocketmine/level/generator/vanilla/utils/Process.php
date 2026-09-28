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

use LogicException;
use pocketmine\level\generator\vanilla\thread\Thread;

use function exec;
use function fclose;
use function file;
use function file_get_contents;
use function function_exists;
use function getmypid;
use function getmyuid;
use function hexdec;
use function memory_get_usage;
use function posix_kill;
use function preg_match;
use function proc_close;
use function proc_open;
use function stream_get_contents;
use function strpos;
use function trim;

final class Process
{
	private function __construct()
	{
		//NOOP
	}

	/**
	 * @return int[]
	 * @phpstan-return array{int,int,int}
	 */
	public static function getAdvancedMemoryUsage()
	{
		$reserved = memory_get_usage();
		$VmSize = null;
		$VmRSS = null;
		if (Utils::getOS() === Utils::OS_LINUX || Utils::getOS() === Utils::OS_ANDROID) {
			$status = @file_get_contents("/proc/self/status");
			if ($status === false) {
				throw new AssumptionFailedError("/proc/self/status should always be accessible");
			}

			// the numbers found here should never be bigger than PHP_INT_MAX, so we expect them to always be castable to int
			if (preg_match("/VmRSS:[ \t]+([0-9]+) kB/", $status, $matches) > 0) {
				$VmRSS = ((int) $matches[1]) * 1024;
			}

			if (preg_match("/VmSize:[ \t]+([0-9]+) kB/", $status, $matches) > 0) {
				$VmSize = ((int) $matches[1]) * 1024;
			}
		}

		//TODO: more OS

		if ($VmRSS === null) {
			$VmRSS = memory_get_usage();
		}

		if ($VmSize === null) {
			$VmSize = memory_get_usage(true);
		}

		return [$reserved, $VmRSS, $VmSize];
	}

	public static function getMemoryUsage() : int
	{
		return self::getAdvancedMemoryUsage()[1];
	}

	/**
	 * @return int[]
	 */
	public static function getRealMemoryUsage() : array
	{
		$stack = 0;
		$heap = 0;

		if (Utils::getOS() === Utils::OS_LINUX || Utils::getOS() === Utils::OS_ANDROID) {
			$mappings = @file("/proc/self/maps");
			if ($mappings === false) {
				throw new AssumptionFailedError("/proc/self/maps should always be accessible");
			}
			foreach ($mappings as $line) {
				if (preg_match("#([a-z0-9]+)\\-([a-z0-9]+) [rwxp\\-]{4} [a-z0-9]+ [^\\[]*\\[([a-zA-z0-9]+)\\]#", trim($line), $matches) > 0) {
					if (strpos($matches[3], "heap") === 0) {
						$heap += (int) hexdec($matches[2]) - (int) hexdec($matches[1]);
					} elseif (strpos($matches[3], "stack") === 0) {
						$stack += (int) hexdec($matches[2]) - (int) hexdec($matches[1]);
					}
				}
			}
		}

		return [$heap, $stack];
	}

	public static function getThreadCount() : int
	{
		if (Utils::getOS() === Utils::OS_LINUX || Utils::getOS() === Utils::OS_ANDROID) {
			$status = @file_get_contents("/proc/self/status");
			if ($status === false) {
				throw new AssumptionFailedError("/proc/self/status should always be accessible");
			}
			if (preg_match("/Threads:[ \t]+([0-9]+)/", $status, $matches) > 0) {
				return (int) $matches[1];
			}
		}

		//TODO: more OS

		return Thread::getRunningCount() + 1; //pmmpthread doesn't count the main thread
	}

	/**
	 * @param int $pid
	 */
	public static function kill($pid) : void
	{
		$logger = \GlobalLogger::get();
		if ($logger instanceof MainLogger) {
			$logger->syncFlushBuffer();
		}
		switch (Utils::getOS()) {
			case Utils::OS_WINDOWS:
				exec("taskkill.exe /F /PID $pid > NUL");
				break;
			case Utils::OS_MACOS:
			case Utils::OS_LINUX:
			default:
				if (function_exists("posix_kill")) {
					posix_kill($pid, 9); //SIGKILL
				} else {
					exec("kill -9 $pid > /dev/null 2>&1");
				}
		}
	}

	/**
	 * @param string      $command Command to execute
	 * @param string|null $stdout  Reference parameter to write stdout to
	 * @param string|null $stderr  Reference parameter to write stderr to
	 *
	 * @return int process exit code
	 */
	public static function execute(string $command, ?string &$stdout = null, ?string &$stderr = null) : int
	{
		$process = proc_open($command, [
			["pipe", "r"],
			["pipe", "w"],
			["pipe", "w"]
		], $pipes);

		if ($process === false) {
			$stderr = "Failed to open process";
			$stdout = "";

			return -1;
		}

		$stdout = stream_get_contents($pipes[1]);
		$stderr = stream_get_contents($pipes[2]);

		foreach ($pipes as $p) {
			fclose($p);
		}

		return proc_close($process);
	}

	public static function pid() : int
	{
		$result = getmypid();
		if ($result === false) {
			throw new LogicException("getmypid() doesn't work on this platform");
		}
		return $result;
	}

	public static function uid() : int
	{
		$result = getmyuid();
		if ($result === false) {
			throw new LogicException("getmyuid() doesn't work on this platform");
		}
		return $result;
	}
}
