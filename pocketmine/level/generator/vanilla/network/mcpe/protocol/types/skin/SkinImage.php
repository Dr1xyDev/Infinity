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

namespace pocketmine\level\generator\vanilla\network\mcpe\protocol\types\skin;

use pocketmine\level\generator\vanilla\entity\InvalidSkinException;

use function strlen;

class SkinImage
{
	public function __construct(
		private int $height,
		private int $width,
		private string $data
	) {
	}

	public static function fromLegacy(string $data) : SkinImage
	{
		switch (strlen($data)) {
			case 64 * 32 * 4:
				return new self(32, 64, $data);
			case 64 * 64 * 4:
				return new self(64, 64, $data);
			case 128 * 128 * 4:
				return new self(128, 128, $data);
			case 256 * 128 * 4:
				return new self(128, 256, $data);
			case 256 * 256 * 4:
				return new self(256, 256, $data);
		}

		throw new InvalidSkinException("Unknown size (strlen " . strlen($data) . ")");
	}

	public static function empty() : self
	{
		return new self(0, 0, "");
	}

	public function getHeight() : int
	{
		return $this->height;
	}

	public function getWidth() : int
	{
		return $this->width;
	}

	public function getData() : string
	{
		return $this->data;
	}
}
