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

namespace pocketmine\level\generator\vanilla\item\trim;

enum ItemTrimPatternType : string {
	case COAST = 'coast';
	case DUNE = 'dune';
	case EYE = 'eye';
	case RIB = 'rib';
	case SENTRY = 'sentry';
	case SNOUT = 'snout';
	case SPIRE = 'spire';
	case TIDE = 'tide';
	case VEX = 'vex';
	case WARD = 'ward';
	case WILD = 'wild';
	case HOST = 'host';
	case RAISER = 'raiser';
	case SHAPER = 'shaper';
	case SILENCE = 'silence';
	case WAYFINDER = 'wayfinder';
	case FLOW = 'flow';
	case BOLT = 'bolt';
}
