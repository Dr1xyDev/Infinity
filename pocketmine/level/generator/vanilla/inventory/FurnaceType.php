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

namespace pocketmine\level\generator\vanilla\inventory;

use pocketmine\level\generator\vanilla\level\sound\BlastFurnaceSound;
use pocketmine\level\generator\vanilla\level\sound\CampfireSound;
use pocketmine\level\generator\vanilla\level\sound\FurnaceSound;
use pocketmine\level\generator\vanilla\level\sound\SmokerSound;
use pocketmine\level\generator\vanilla\level\sound\Sound;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\utils\LegacyEnumShimTrait;
use function spl_object_id;

/**
 * TODO: These tags need to be removed once we get rid of LegacyEnumShimTrait (PM6)
 *  These are retained for backwards compatibility only.
 *
 * @method static FurnaceType BLAST_FURNACE()
 * @method static FurnaceType CAMPFIRE()
 * @method static FurnaceType FURNACE()
 * @method static FurnaceType SMOKER()
 * @method static FurnaceType SOUL_CAMPFIRE()
 *
 * @phpstan-type TMetadata array{0: int, 1: string}
 */
enum FurnaceType{
	use LegacyEnumShimTrait;

	case FURNACE;
	case BLAST_FURNACE;
	case SMOKER;
	case CAMPFIRE;
	case SOUL_CAMPFIRE;

	/**
	 * @phpstan-return TMetadata
	 */
	private function getMetadata() : array{
		/** @phpstan-var array<int, TMetadata> $cache */
		static $cache = [];

		return $cache[spl_object_id($this)] ??= match($this){
			self::FURNACE => [200, FurnaceSound::class],
			self::BLAST_FURNACE => [100, BlastFurnaceSound::class],
			self::SMOKER => [100, SmokerSound::class],
			self::CAMPFIRE, self::SOUL_CAMPFIRE => [600, CampfireSound::class],
		};
	}

	public function getCookDurationTicks() : int{ return $this->getMetadata()[0]; }

	public function getCookSound(Vector3 $vector3) : Sound{ return new ($this->getMetadata()[1])($vector3); }
}
