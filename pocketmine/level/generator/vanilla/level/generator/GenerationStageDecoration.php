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

namespace pocketmine\level\generator\vanilla\level\generator;

enum GenerationStageDecoration : int {
	case RAW_GENERATION = 0;
	case LAKES = 1;
	case LOCAL_MODIFICATIONS = 2;
	case UNDERGROUND_STRUCTURES = 3;
	case SURFACE_STRUCTURES = 4;
	case STRONGHOLDS = 5;
	case UNDERGROUND_ORES = 6;
	case UNDERGROUND_DECORATION = 7;
	case VEGETAL_DECORATION = 8;
	case TOP_LAYER_MODIFICATION = 9;
}
