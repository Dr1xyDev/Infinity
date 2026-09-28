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

namespace pocketmine\level\generator\vanilla\level\generator\verticalanchor;

use pocketmine\level\generator\vanilla\level\ChunkManager;
use pocketmine\level\generator\vanilla\level\Level;

class AboveBottom extends VerticalAnchor{

	public function __construct(private readonly int $offset) {}

	public function resolveY(ChunkManager $level) : int{
		return Level::Y_MIN + $this->offset;
	}

	public function toString() : string{
		return $this->offset . " above bottom";
	}

	public function toArray() : array{
		return ['above_bottom' => $this->offset];
	}

	public function offset() : int{
		return $this->offset;
	}
}
