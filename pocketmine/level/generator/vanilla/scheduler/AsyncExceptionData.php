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

use Throwable;

use function get_class;
use function is_array;
use function is_object;

class AsyncExceptionData
{
	protected string $class;
	protected string $message;
	protected int $code;
	protected string $file;
	protected int $line;
	protected array $trace;
	protected string $traceAsString;
	protected ?AsyncExceptionData $previous = null;

	public function __construct(Throwable $original)
	{
		$this->class = get_class($original);
		$this->message = $original->getMessage();
		$this->code = $original->getCode();
		$this->file = $original->getFile();
		$this->line = $original->getLine();
		$this->trace = $original->getTrace();
		$this->traceAsString = $original->getTraceAsString();

		$prev = $original->getPrevious();
		if ($prev !== null) {
			$this->previous = new AsyncExceptionData($prev);
		}

		foreach ($this->trace as &$val) {
			if (isset($val["args"])) {
				$val["args"] = $this->processArgs($val["args"]);
			} elseif (isset($val["params"])) {
				$val["params"] = $this->processArgs($val["params"]);
			}
		}
	}

	private function processArgs(array $args) : array
	{
		$result = [];

		foreach ($args as $arg) {
			if (is_object($arg)) {
				$result[] = get_class($arg) . " object";
			} elseif (is_array($arg)) {
				$result[] = $this->processArgs($arg);
			} else {
				$result[] = $arg;
			}
		}

		return $result;
	}

	public function getClass() : string
	{
		return $this->class;
	}

	public function getMessage() : string
	{
		return $this->message;
	}

	public function getCode() : int
	{
		return $this->code;
	}

	public function getFile() : string
	{
		return $this->file;
	}

	public function getLine() : int
	{
		return $this->line;
	}

	public function getTrace() : array
	{
		return $this->trace;
	}

	public function getTraceAsString() : string
	{
		return $this->traceAsString;
	}

	public function getPrevious() : ?AsyncExceptionData
	{
		return $this->previous;
	}

	public function __toString()
	{
		return $this->message;
	}
}
