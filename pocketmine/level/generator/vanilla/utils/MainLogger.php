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

use pmmp\thread\Thread as NativeThread;
use pocketmine\level\generator\vanilla\thread\log\AttachableThreadSafeLogger;
use pocketmine\level\generator\vanilla\thread\log\ThreadSafeLoggerAttachment;
use pocketmine\level\generator\vanilla\thread\Thread;
use pocketmine\level\generator\vanilla\thread\Worker;

use function implode;
use function sprintf;

use const PHP_EOL;

class MainLogger extends AttachableThreadSafeLogger implements \BufferedLogger
{
	protected bool $logDebug;

	private string $format = TextFormat::BOLD . TextFormat::BLUE . "[%s] " . TextFormat::RESET . "%s[%s/%s]: %s" . TextFormat::RESET;
	private bool $useFormattingCodes;
	private string $mainThreadName;
	private string $timezone;
	private ?MainLoggerThread $logWriterThread = null;

	/**
	 * @throws \RuntimeException
	 */
	public function __construct(?string $logFile, bool $useFormattingCodes, string $mainThreadName, \DateTimeZone $timezone, bool $logDebug = false, ?string $logArchiveDir = null)
	{
		parent::__construct();
		$this->logDebug = $logDebug;

		$this->useFormattingCodes = $useFormattingCodes;
		$this->mainThreadName = $mainThreadName;
		$this->timezone = $timezone->getName();

		if ($logFile !== null) {
			$this->logWriterThread = new MainLoggerThread($logFile, $logArchiveDir);
			$this->logWriterThread->start(NativeThread::INHERIT_NONE);
		}
	}

	/**
	 * Returns the current logger format used for console output.
	 */
	public function getFormat() : string
	{
		return $this->format;
	}

	/**
	 * Sets the logger format to use for outputting text to the console.
	 * It should be an sprintf()able string accepting 5 string arguments:
	 * - time
	 * - color
	 * - thread name
	 * - prefix (debug, info etc)
	 * - message
	 *
	 * @see http://php.net/manual/en/function.sprintf.php
	 */
	public function setFormat(string $format) : void
	{
		$this->format = $format;
	}

	public function emergency($message)
	{
		$this->send($message, \LogLevel::EMERGENCY, "EMERGENCY", TextFormat::RED);
	}

	public function alert($message)
	{
		$this->send($message, \LogLevel::ALERT, "ALERT", TextFormat::RED);
	}

	public function critical($message)
	{
		$this->send($message, \LogLevel::CRITICAL, "CRITICAL", TextFormat::RED);
	}

	public function error($message)
	{
		$this->send($message, \LogLevel::ERROR, "ERROR", TextFormat::DARK_RED);
	}

	public function warning($message)
	{
		$this->send($message, \LogLevel::WARNING, "WARNING", TextFormat::YELLOW);
	}

	public function notice($message)
	{
		$this->send($message, \LogLevel::NOTICE, "NOTICE", TextFormat::AQUA);
	}

	public function info($message)
	{
		$this->send($message, \LogLevel::INFO, "INFO", TextFormat::WHITE);
	}

	public function debug($message, bool $force = false)
	{
		if (!$this->logDebug && !$force) {
			return;
		}
		$this->send($message, \LogLevel::DEBUG, "DEBUG", TextFormat::GRAY);
	}

	public function setLogDebug(bool $logDebug) : void
	{
		$this->logDebug = $logDebug;
	}

	/**
	 * @param mixed[][]|null $trace
	 * @phpstan-param list<array<string, mixed>>|null $trace
	 *
	 * @return void
	 */
	public function logException(\Throwable $e, $trace = null)
	{
		$this->critical(implode("\n", Utils::printableExceptionInfo($e, $trace)));

		$this->syncFlushBuffer();
	}

	public function log($level, $message)
	{
		switch ($level) {
			case \LogLevel::EMERGENCY:
				$this->emergency($message);
				break;
			case \LogLevel::ALERT:
				$this->alert($message);
				break;
			case \LogLevel::CRITICAL:
				$this->critical($message);
				break;
			case \LogLevel::ERROR:
				$this->error($message);
				break;
			case \LogLevel::WARNING:
				$this->warning($message);
				break;
			case \LogLevel::NOTICE:
				$this->notice($message);
				break;
			case \LogLevel::INFO:
				$this->info($message);
				break;
			case \LogLevel::DEBUG:
				$this->debug($message);
				break;
		}
	}

	/**
	 * @phpstan-param \Closure() : void $c
	 */
	public function buffer(\Closure $c) : void
	{
		$this->synchronized($c);
	}

	public function shutdownLogWriterThread() : void
	{
		if ($this->logWriterThread !== null) {
			if (NativeThread::getCurrentThreadId() === $this->logWriterThread->getCreatorId()) {
				$this->logWriterThread->shutdown();
			} else {
				throw new \LogicException("Only the creator thread can shutdown the logger thread");
			}
		}
	}

	protected function send(string $message, string $level, string $prefix, string $color) : void
	{
		$time = new \DateTime('now', new \DateTimeZone($this->timezone));

		$thread = NativeThread::getCurrentThread();
		if ($thread === null) {
			$threadName = $this->mainThreadName . " thread";
		} elseif ($thread instanceof Thread || $thread instanceof Worker) {
			$threadName = $thread->getThreadName() . " thread";
		} else {
			$threadName = (new \ReflectionClass($thread))->getShortName() . " thread";
		}

		$message = sprintf($this->format, $time->format("H:i:s.v"), $color, $threadName, $prefix, TextFormat::addBase($color, TextFormat::clean($message, false)));

		if (!Terminal::isInit()) {
			Terminal::init($this->useFormattingCodes); //lazy-init colour codes because we don't know if they've been registered on this thread
		}

		$this->synchronized(function () use ($message, $level, $time) : void {
			Terminal::writeLine($message);
			if ($this->logWriterThread !== null) {
				$this->logWriterThread->write($time->format("Y-m-d") . " " . TextFormat::clean($message) . PHP_EOL);
			}

			/**
			 * @var ThreadSafeLoggerAttachment $attachment
			 */
			foreach ($this->attachments as $attachment) {
				$attachment->log($level, $message);
			}
		});
	}

	public function syncFlushBuffer() : void
	{
		$this->logWriterThread?->syncFlushBuffer();
	}

	public function __destruct()
	{
		if ($this->logWriterThread !== null && !$this->logWriterThread->isJoined() && NativeThread::getCurrentThreadId() === $this->logWriterThread->getCreatorId()) {
			$this->shutdownLogWriterThread();
		}
	}
}
