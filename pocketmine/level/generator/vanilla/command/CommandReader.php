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

namespace pocketmine\level\generator\vanilla\command;

use pmmp\thread\ThreadSafeArray;
use pocketmine\level\generator\vanilla\snooze\SleeperNotifier;
use pocketmine\level\generator\vanilla\thread\Thread;
use pocketmine\level\generator\vanilla\thread\ThreadException;
use pocketmine\level\generator\vanilla\utils\Utils;
use Throwable;

use function extension_loaded;
use function fclose;
use function fgets;
use function fopen;
use function fstat;
use function function_exists;
use function getopt;
use function is_resource;
use function microtime;
use function posix_isatty;
use function preg_replace;
use function readline;
use function readline_add_history;
use function stream_select;
use function trim;
use function usleep;

use const STDIN;

class CommandReader extends Thread
{
	public const TYPE_READLINE = 0;
	public const TYPE_STREAM = 1;
	public const TYPE_PIPED = 2;

	protected ThreadSafeArray $buffer;
	private bool $shutdown = false;

	private int $type = self::TYPE_STREAM;

	private SleeperNotifier $notifier;

	public function __construct(?SleeperNotifier $notifier = null)
	{
		$this->buffer = new ThreadSafeArray();
		$this->notifier = $notifier;

		$opts = getopt("", ["disable-readline", "enable-readline"]);

		if (extension_loaded("readline") && (Utils::getOS() === Utils::OS_WINDOWS ? isset($opts["enable-readline"]) : !isset($opts["disable-readline"])) && !$this->isPipe(STDIN)) {
			$this->type = self::TYPE_READLINE;
		}

		$this->setClassLoaders();
	}

	public function shutdown()
	{
		$this->shutdown = true;
	}

	public function quit() : void
	{
		$wait = microtime(true) + 0.5;
		while (microtime(true) < $wait) {
			if ($this->isRunning()) {
				usleep(100000);
			} else {
				parent::quit();
				return;
			}
		}

		$message = "Thread blocked for unknown reason";
		if ($this->type === self::TYPE_PIPED) {
			$message = "STDIN is being piped from another location and the pipe is blocked, cannot stop safely";
		}

		throw new ThreadException($message);
	}

	private function initStdin()
	{
		global $stdin;

		if (is_resource($stdin)) {
			fclose($stdin);
		}

		$stdin = fopen("php://stdin", "r");
		if ($this->isPipe($stdin)) {
			$this->type = self::TYPE_PIPED;
		} else {
			$this->type = self::TYPE_STREAM;
		}
	}

	/**
	 * Checks if the specified stream is a FIFO pipe.
	 *
	 * @param resource $stream
	 */
	private function isPipe($stream) : bool
	{
		return is_resource($stream) && ((function_exists("posix_isatty") && !posix_isatty($stream)) || ((fstat($stream)["mode"] & 0170000) === 0010000));
	}

	/**
	 * Reads a line from the console and adds it to the buffer. This method may block the thread.
	 *
	 * @return bool if the main execution should continue reading lines
	 */
	private function readLine() : bool
	{
		$line = "";
		if ($this->type === self::TYPE_READLINE) {
			if (($raw = readline("> ")) !== false && ($line = trim($raw)) !== "") {
				readline_add_history($line);
			} else {
				return true;
			}
		} else {
			global $stdin;

			if (!is_resource($stdin)) {
				$this->initStdin();
			}

			switch ($this->type) {
				/** @noinspection PhpMissingBreakStatementInspection */
				case self::TYPE_STREAM:
					//stream_select doesn't work on piped streams for some reason
					$r = [$stdin];
					$w = $e = null;
					if (($count = stream_select($r, $w, $e, 0, 200000)) === 0) { //nothing changed in 200000 microseconds
						return true;
					} elseif ($count === false) { //stream error
						$this->initStdin();
					}

					// no break
				case self::TYPE_PIPED:
					try{
						$raw = fgets($stdin);
					}catch(Throwable $e){
						$raw = false;
					}
					if ($raw === false) { //broken pipe or EOF
						$this->initStdin();
						$this->synchronized(function () {
							$this->wait(200000);
						}); //prevent CPU waste if it's end of pipe
						return true; //loop back round
					}

					$line = trim($raw);
					break;
			}
		}

		if ($line !== "") {
			$this->buffer[] = preg_replace("#\\x1b\\x5b([^\\x1b]*\\x7e|[\\x40-\\x50])#", "", $line);
			if ($this->notifier !== null) {
				$this->notifier->wakeupSleeper();
			}
		}

		return true;
	}

	/**
	 * Reads a line from console, if available. Returns null if not available
	 *
	 * @return string|null
	 */
	public function getLine()
	{
		if ($this->buffer->count() !== 0) {
			return $this->buffer->shift();
		}

		return null;
	}

	public function onRun() : void
	{
		$this->registerClassLoaders();

		if ($this->type !== self::TYPE_READLINE) {
			$this->initStdin();
		}

		while (!$this->shutdown && $this->readLine()) ;

		if ($this->type !== self::TYPE_READLINE) {
			global $stdin;
			fclose($stdin);
		}

	}

	public function getThreadName() : string
	{
		return "Console";
	}
}
