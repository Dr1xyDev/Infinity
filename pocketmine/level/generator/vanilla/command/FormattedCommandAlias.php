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

use InvalidArgumentException;
use pocketmine\Server;
use pocketmine\level\generator\vanilla\utils\TextFormat;

use function count;
use function ord;
use function strlen;
use function strpos;
use function substr;

class FormattedCommandAlias extends Command
{
	/** @var string[] */
	private array $formatStrings;

	/**
	 * @param string[] $formatStrings
	 */
	public function __construct(string $alias, array $formatStrings)
	{
		parent::__construct($alias);
		$this->formatStrings = $formatStrings;
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args)
	{

		$commands = [];
		$result = false;

		foreach ($this->formatStrings as $formatString) {
			try {
				$commands[] = $this->buildCommand($formatString, $args);
			} catch (InvalidArgumentException $e) {
				$sender->sendMessage(TextFormat::RED . $e->getMessage());
				return false;
			}
		}

		foreach ($commands as $command) {
			$result |= Server::getInstance()->dispatchCommand($sender, $command, true);
		}

		return (bool) $result;
	}

	/**
	 * @param string[] $args
	 */
	private function buildCommand(string $formatString, array $args) : string
	{
		$index = strpos($formatString, '$');
		while ($index !== false) {
			$start = $index;
			if ($index > 0 && $formatString[$start - 1] === "\\") {
				$formatString = substr($formatString, 0, $start - 1) . substr($formatString, $start);
				$index = strpos($formatString, '$', $index);
				continue;
			}

			$required = false;
			if ($formatString[$index + 1] == '$') {
				$required = true;

				++$index;
			}

			++$index;

			$argStart = $index;

			while ($index < strlen($formatString) && self::inRange(ord($formatString[$index]) - 48, 0, 9)) {
				++$index;
			}

			if ($argStart === $index) {
				throw new InvalidArgumentException("Invalid replacement token");
			}

			$position = (int) substr($formatString, $argStart, $index);

			if ($position === 0) {
				throw new InvalidArgumentException("Invalid replacement token");
			}

			--$position;

			$rest = false;

			if ($index < strlen($formatString) && $formatString[$index] === "-") {
				$rest = true;
				++$index;
			}

			$end = $index;

			if ($required && $position >= count($args)) {
				throw new InvalidArgumentException("Missing required argument " . ($position + 1));
			}

			$replacement = "";
			if ($rest && $position < count($args)) {
				for ($i = $position, $c = count($args); $i < $c; ++$i) {
					if ($i !== $position) {
						$replacement .= " ";
					}

					$replacement .= $args[$i];
				}
			} elseif ($position < count($args)) {
				$replacement .= $args[$position];
			}

			$formatString = substr($formatString, 0, $start) . $replacement . substr($formatString, $end);

			$index = $start + strlen($replacement);

			$index = strpos($formatString, '$', $index);
		}

		return $formatString;
	}

	private static function inRange(int $i, int $j, int $k) : bool
	{
		return $i >= $j && $i <= $k;
	}
}
