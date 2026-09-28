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

namespace pocketmine\level\generator\vanilla\command\defaults;

use pocketmine\level\generator\vanilla\command\Command;
use pocketmine\level\generator\vanilla\command\CommandSender;
use pocketmine\level\generator\vanilla\command\utils\InvalidCommandSyntaxException;
use pocketmine\level\generator\vanilla\lang\TranslationContainer;
use pocketmine\level\generator\vanilla\utils\TextFormat;

use function is_numeric;
use function substr;

abstract class VanillaCommand extends Command
{
	public const MAX_COORD = 30000000;
	public const MIN_COORD = -30000000;

	/**
	 * @param mixed $value
	 */
	protected function getInteger(CommandSender $sender, $value, int $min = self::MIN_COORD, int $max = self::MAX_COORD) : int
	{
		$i = (int) $value;

		if ($i < $min) {
			$i = $min;
		} elseif ($i > $max) {
			$i = $max;
		}

		return $i;
	}

	protected function getRelativeDouble(float $original, CommandSender $sender, string $input, float $min = self::MIN_COORD, float $max = self::MAX_COORD) : float
	{
		if ($input[0] === "~") {
			$value = $this->getDouble($sender, substr($input, 1));

			return $original + $value;
		}

		return $this->getDouble($sender, $input, $min, $max);
	}

	/**
	 * @param mixed $value
	 */
	protected function getDouble(CommandSender $sender, $value, float $min = self::MIN_COORD, float $max = self::MAX_COORD) : float
	{
		$i = (float) $value;

		if ($i < $min) {
			$i = $min;
		} elseif ($i > $max) {
			$i = $max;
		}

		return $i;
	}

	protected function getBoundedInt(CommandSender $sender, string $input, int $min, int $max) : ?int
	{
		if (!is_numeric($input)) {
			throw new InvalidCommandSyntaxException();
		}

		$v = (int) $input;
		if ($v > $max) {
			$sender->sendMessage(new TranslationContainer(TextFormat::RED . "%commands.generic.num.tooBig", [$input, (string) $max]));
			return null;
		}
		if ($v < $min) {
			$sender->sendMessage(new TranslationContainer(TextFormat::RED . "%commands.generic.num.tooSmall", [$input, (string) $min]));
			return null;
		}

		return $v;
	}
}
