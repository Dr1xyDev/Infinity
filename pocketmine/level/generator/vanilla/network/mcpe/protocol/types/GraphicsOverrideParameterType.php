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

namespace pocketmine\level\generator\vanilla\network\mcpe\protocol\types;

enum GraphicsOverrideParameterType : int{
	use PacketIntEnumTrait;

	case SKY_ZENITH_COLOR = 0;
	case SKY_HORIZON_COLOR = 1;
	case HORIZON_BLEND_MIN = 2;
	case HORIZON_BLEND_MAX = 3;
	case HORIZON_BLEND_START = 4;
	case HORIZON_BLEND_MIE_START = 5;
	case RAYLEIGH_STRENGTH = 6;
	case SUN_MIE_STRENGTH = 7;
	case MOON_MIE_STRENGTH = 8;
	case SUN_GLARE_SHAPE = 9;
	case CHLOROPHYLL = 10;
	case CDOM = 11;
	case SUSPENDED_SEDIMENT = 12;
	case WAVES_DEPTH = 13;
	case WAVES_FREQUENCY = 14;
	case WAVES_FREQUENCY_SCALING = 15;
	case WAVES_SPEED = 16;
	case WAVES_SPEED_SCALING = 17;
	case WAVES_SHAPE = 18;
	case WAVES_OCTAVES = 19;
	case WAVES_MIX = 20;
	case WAVES_PULL = 21;
	case WAVES_DIRECTION_INCREMENT = 22;
	case MIDTONES_CONTRAST = 23;
	case HIGHLIGHTS_CONTRAST = 24;
	case SHADOWS_CONTRAST = 25;
	case HIGHLIGHTS_GAIN = 26;
	case HIGHLIGHTS_GAMMA = 27;
	case HIGHLIGHTS_OFFSET = 28;
	case HIGHLIGHTS_SATURATION = 29;
	case MIDTONES_GAIN = 30;
	case MIDTONES_GAMMA = 31;
	case MIDTONES_OFFSET = 32;
	case MIDTONES_SATURATION = 33;
	case SHADOWS_GAIN = 34;
	case SHADOWS_GAMMA = 35;
	case SHADOWS_OFFSET = 36;
	case SHADOWS_SATURATION = 37;
	case HIGHLIGHTS_MIN = 38;
	case SHADOWS_MAX = 39;
	case TEMPERATURE = 40;
	case SUN_COLOR = 41;
	case SUN_ILLUMINANCE = 42;
	case MOON_COLOR = 43;
	case MOON_ILLUMINANCE = 44;
	case FLASH_COLOR = 45;
	case FLASH_ILLUMINANCE = 46;
	case AMBIENT_COLOR = 47;
	case AMBIENT_ILLUMINANCE = 48;
}
