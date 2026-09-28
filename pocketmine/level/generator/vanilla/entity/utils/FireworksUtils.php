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

namespace pocketmine\level\generator\vanilla\entity\utils;

use pocketmine\level\generator\vanilla\entity\object\FireworksRocket;
use pocketmine\level\generator\vanilla\item\Fireworks;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\nbt\NBT;
use pocketmine\level\generator\vanilla\nbt\tag\ByteArrayTag;
use pocketmine\level\generator\vanilla\nbt\tag\ByteTag;
use pocketmine\level\generator\vanilla\nbt\tag\CompoundTag;
use pocketmine\level\generator\vanilla\nbt\tag\ListTag;
use pocketmine\level\generator\vanilla\utils\Random;

use function chr;
use function is_null;

class FireworksUtils
{
	public const COLOR_BLACK = 0;
	public const COLOR_RED = 1;
	public const COLOR_GREEN = 2;
	public const COLOR_BROWN = 3;
	public const COLOR_BLUE = 4;
	public const COLOR_PURPLE = 5;
	public const COLOR_CYAN = 6;
	public const COLOR_LIGHT_GRAY = 7;
	public const COLOR_GRAY = 8;
	public const COLOR_PINK = 9;
	public const COLOR_LIME = 10;
	public const COLOR_YELLOW = 11;
	public const COLOR_LIGHT_BLUE = 12;
	public const COLOR_MAGENTA = 13;
	public const COLOR_ORANGE = 14;
	public const COLOR_WHITE = 15;

	public const TYPE_SMALL_BALL = 0;
	public const TYPE_LARGE_BALL = 1;
	public const TYPE_STAR_SHAPED = 2;
	public const TYPE_CREEPER_SHAPED = 3;
	public const TYPE_BURST = 4;

	public static function createNBT(int $flight = 1, array $explosionTags = []) : CompoundTag
	{
		return new CompoundTag("", [
			new CompoundTag("Fireworks", [
				new ListTag("Explosions", $explosionTags, NBT::TAG_Compound), new ByteTag("Flight", $flight)
			])
		]);
	}

	public static function createExplosion(int $fireworkColor = 0, int $fireworkFade = 0, bool $fireworkFlicker = false, bool $fireworkTrail = false, int $fireworkType = -1) : CompoundTag
	{
		return new CompoundTag("", [
			new ByteArrayTag("FireworkColor", chr($fireworkColor)),
			new ByteArrayTag("FireworkFade", chr($fireworkFade)),
			new ByteTag("FireworkFlicker", $fireworkFlicker ? 1 : 0),
			new ByteTag("FireworkTrail", $fireworkTrail ? 1 : 0), new ByteTag("FireworkType", $fireworkType),
		]);
	}

	public static function createEntityNBT(Vector3 $pos, ?Vector3 $motion, Fireworks $rocket, float $spread = 5.0, ?Random $random = null, ?float $yaw = null, ?float $pitch = null) : CompoundTag
	{
		$random = $random ?? new Random();
		$pos = $pos->add(0.5, 0, 0.5);
		$yaw = $yaw ?? $random->nextBoundedInt(360);
		$pitch = $pitch ?? -1 * (float) (90 + ($random->nextFloat() * $spread - $spread / 2));
		$nbt = FireworksRocket::createBaseNBT($pos, $motion, $yaw, $pitch);

		/** @var CompoundTag $tags */
		$tags = $rocket->getNamedTagEntry("Fireworks");
		if (!is_null($tags)) {
			$nbt->setTag($tags);
		}

		return $nbt;
	}

}
