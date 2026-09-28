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

namespace pocketmine\level\generator\vanilla\level\generator\layer;

use function array_fill;

class LayerData {
	/** @var int[] */
	public array $parentArea = [];
	/** @var int[] */
	public array $result = [];

	public function __construct() {
		$size = 32 * 32;

		$this->parentArea = array_fill(0, $size, 0);
		$this->result = array_fill(0, $size, 0);
	}

	public function swap() : void {
		[$this->parentArea, $this->result] = [$this->result, $this->parentArea];
	}
}
