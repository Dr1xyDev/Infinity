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

namespace pocketmine\level\generator\vanilla\level\generator\feature\trunkplacers;

enum TrunkPlacerType {
	case STRAIGHT_TRUNK_PLACER;
	case FORKING_TRUNK_PLACER;
	case GIANT_TRUNK_PLACER;
	case MEGA_JUNGLE_TRUNK_PLACER;
	case DARK_OAK_TRUNK_PLACER;
	case FANCY_TRUNK_PLACER;
	case BENDING_TRUNK_PLACER;
	case UPWARDS_BRANCHING_TRUNK_PLACER;
	case CHERRY_TRUNK_PLACER;
}
