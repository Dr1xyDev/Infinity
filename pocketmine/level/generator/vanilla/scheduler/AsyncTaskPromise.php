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

namespace pocketmine\level\generator\vanilla\scheduler;

use Closure;
use GlobalLogger;
use InvalidArgumentException;
use InvalidStateException;
use pocketmine\level\generator\vanilla\utils\Filesystem;
use pocketmine\level\generator\vanilla\utils\Utils;
use Throwable;

use function count;
use function preg_replace;
use function trim;

use const E_COMPILE_ERROR;
use const E_COMPILE_WARNING;
use const E_CORE_ERROR;
use const E_CORE_WARNING;
use const E_DEPRECATED;
use const E_ERROR;
use const E_NOTICE;
use const E_PARSE;
use const E_RECOVERABLE_ERROR;
use const E_STRICT;
use const E_USER_DEPRECATED;
use const E_USER_ERROR;
use const E_USER_NOTICE;
use const E_USER_WARNING;
use const E_WARNING;

class AsyncTaskPromise
{
	/** @var Closure[] */
	private $resolveCallbacks = [];
	/** @var Closure[] */
	private $crashCallbacks = [];
	/** @var mixed|null */
	private mixed $result;
	/** @var bool */
	private $resolved = false;
	/** @var bool */
	private $crashed = false;
	/** @var AsyncException|null */
	private $crashInfo;
	/** @var bool */
	private $cancelled = false;

	public function __construct(
		protected Closure $closure,
		protected array $argv
	) {
	}

	public function onResolve(Closure ...$callbacks) : self
	{
		$this->checkCancelled();
		foreach ($callbacks as $callback) {
			Utils::validateCallableSignature(function (AsyncTaskPromise $promise) : void {}, $callback);

			if ($this->resolved) {
				$callback($this);
			} else {
				$this->resolveCallbacks[] = $callback;
			}
		}
		return $this;
	}

	public function onCrash(Closure ...$callbacks) : self
	{
		$this->checkCancelled();
		foreach ($callbacks as $callback) {
			Utils::validateCallableSignature(function (AsyncTaskPromise $promise) : void {}, $callback);

			if ($this->crashed) {
				$callback($this);
			} else {
				$this->crashCallbacks[] = $callback;
			}
		}
		return $this;
	}

	public function resolve($result) : void
	{
		if (!$this->cancelled) {
			if ($this->resolved || $this->crashed) {
				throw new InvalidStateException("Cannot resolve promise more than once");
			}
			$this->resolved = true;
			$this->result = $result;
			foreach ($this->resolveCallbacks as $callback) {
				try {
					$callback($this);
				} catch (Throwable $e) {
					GlobalLogger::get()->logException($e);
				}
			}

			$this->resolveCallbacks = [];
			$this->crashCallbacks = [];
		}
	}

	public function crash(AsyncException $crashInfo) : void
	{
		if (!$this->isCancelled()) {
			if ($this->resolved || $this->crashed) {
				throw new InvalidStateException("Cannot resolve promise more than once");
			}
			$this->crashed = true;
			$this->crashInfo = $crashInfo;

			if (count($this->crashCallbacks) > 0) {
				foreach ($this->crashCallbacks as $callback) {
					try {
						$callback($this);
					} catch (Throwable $e) {
						GlobalLogger::get()->logException($e);
					}
				}
			} else {
				$logger = GlobalLogger::get();

				$logger->error("Asynchronous task crashed");

				$asyncData = $crashInfo->getAsyncData();

				static $errorConversion = [
					0 => "EXCEPTION",
					E_ERROR => "E_ERROR",
					E_WARNING => "E_WARNING",
					E_PARSE => "E_PARSE",
					E_NOTICE => "E_NOTICE",
					E_CORE_ERROR => "E_CORE_ERROR",
					E_CORE_WARNING => "E_CORE_WARNING",
					E_COMPILE_ERROR => "E_COMPILE_ERROR",
					E_COMPILE_WARNING => "E_COMPILE_WARNING",
					E_USER_ERROR => "E_USER_ERROR",
					E_USER_WARNING => "E_USER_WARNING",
					E_USER_NOTICE => "E_USER_NOTICE",
					E_STRICT => "E_STRICT",
					E_RECOVERABLE_ERROR => "E_RECOVERABLE_ERROR",
					E_DEPRECATED => "E_DEPRECATED",
					E_USER_DEPRECATED => "E_USER_DEPRECATED"
				];
				$trace = $asyncData->getTrace();

				$errstr = $asyncData->getMessage();
				$errfile = $asyncData->getFile();
				$errno = $asyncData->getCode();
				$errline = $asyncData->getLine();

				$errno = $errorConversion[$errno] ?? $errno;
				$errstr = preg_replace('/\s+/', ' ', trim($errstr));
				$errfile = Filesystem::cleanPath($errfile);
				$logger->critical($asyncData->getClass() . ": \"$errstr\" ($errno) in \"$errfile\" at line $errline");
				foreach (Utils::printableTrace($trace) as $i => $line) {
					$logger->critical(" " . $line);
				}
			}

			$this->resolveCallbacks = [];
			$this->crashCallbacks = [];
		}
	}

	public function getClosure() : Closure
	{
		return $this->closure;
	}

	public function getArgs() : array
	{
		return $this->argv;
	}

	/**
	 * @return mixed|null
	 */
	public function getResult() : mixed
	{
		return $this->result;
	}

	public function isResolved() : bool
	{
		return $this->resolved;
	}

	public function getCrashInfo() : ?AsyncException
	{
		return $this->crashInfo;
	}

	public function isCrashed() : bool
	{
		return $this->crashed;
	}

	public function isFinished() : bool
	{
		return $this->crashed || $this->resolved || $this->cancelled;
	}

	public function cancel() : void
	{
		if ($this->resolved) {
			throw new InvalidStateException("Cannot cancel a resolved promise");
		}
		$this->cancelled = true;
	}

	public function isCancelled() : bool
	{
		return $this->cancelled;
	}

	private function checkCancelled() : void
	{
		if ($this->cancelled) {
			throw new InvalidArgumentException("Promise has been cancelled");
		}
	}
}
