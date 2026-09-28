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

namespace pocketmine\level\generator\vanilla\level\generator\surfacebuilders;

use pocketmine\level\generator\vanilla\block\Block;

class SurfaceBuilderConfig {
	public function __construct(
		private Block $topMaterial,
		private Block $underMaterial,
		private Block $underWaterMaterial
	){}

	public function getTop() : Block {
		return $this->topMaterial;
	}

	public function getUnder() : Block {
		return $this->underMaterial;
	}

	public function getUnderWater() : Block {
		return $this->underWaterMaterial;
	}
}
