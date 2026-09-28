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

namespace pocketmine\level\generator\vanilla\network\mcpe\protocol\types\hud;

use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\PacketIntEnumTrait;

enum HudElement : int
{
	use PacketIntEnumTrait;

	case PAPER_DOLL = 0;
	case ARMOR = 1;
	case TOOLTIPS = 2;
	case TOUCH_CONTROLS = 3;
	case CROSSHAIR = 4;
	case HOTBAR = 5;
	case HEALTH = 6;
	case XP = 7;
	case FOOD = 8;
	case AIR_BUBBLES = 9;
	case VEHICLE_HEALTH = 10;
	case STATUS_EFFECTS = 11;
	case ITEM_TEXT_POPUP = 12;
}
