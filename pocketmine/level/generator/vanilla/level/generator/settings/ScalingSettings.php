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

namespace pocketmine\level\generator\vanilla\level\generator\settings;

class ScalingSettings {
	public function __construct(
		private float $xzScale,
		private float $yScale,
		private float $xzFactor,
		private float $yFactor
	){}

	public function getXzScale() : float {
		return $this->xzScale;
	}

	public function getYScale() : float {
		return $this->yScale;
	}

	public function getXzFactor() : float {
		return $this->xzFactor;
	}

	public function getYFactor() : float {
		return $this->yFactor;
	}
}
