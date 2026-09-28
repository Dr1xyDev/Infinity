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

namespace pocketmine\level\generator\vanilla\level\generator\feature\foliageplacers;

enum FoliagePlacerType {
	case BLOB_FOLIAGE_PLACER;
	case SPRUCE_FOLIAGE_PLACER;
	case PINE_FOLIAGE_PLACER;
	case ACACIA_FOLIAGE_PLACER;
	case BUSH_FOLIAGE_PLACER;
	case FANCY_FOLIAGE_PLACER;
	case MEGA_JUNGLE_FOLIAGE_PLACER;
	case MEGA_PINE_FOLIAGE_PLACER;
	case DARK_OAK_FOLIAGE_PLACER;
	case RANDOM_SPREAD_FOLIAGE_PLACER;
	case CHERRY_FOLIAGE_PLACER;
}
