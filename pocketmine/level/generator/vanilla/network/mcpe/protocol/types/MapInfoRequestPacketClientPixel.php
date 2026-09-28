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

use pocketmine\level\generator\vanilla\network\mcpe\NetworkBinaryStream;
use pocketmine\level\generator\vanilla\utils\Color;

use function intdiv;

final class MapInfoRequestPacketClientPixel
{
	private const Y_INDEX_MULTIPLIER = 128;

	public function __construct(
		public Color $color,
		public int $x,
		public int $y
	) {
	}

	public function getColor() : Color
	{
		return $this->color;
	}

	public function getX() : int
	{
		return $this->x;
	}

	public function getY() : int
	{
		return $this->y;
	}

	public static function read(NetworkBinaryStream $in) : self
	{
		$color = $in->getLInt();
		$index = $in->getLShort();

		$x = $index % self::Y_INDEX_MULTIPLIER;
		$y = intdiv($index, self::Y_INDEX_MULTIPLIER);

		return new self(Color::fromRGBA($color), $x, $y);
	}

	public function write(NetworkBinaryStream $out) : void
	{
		$out->putLInt($this->color->toRGBA());
		$out->putLShort($this->x + ($this->y * self::Y_INDEX_MULTIPLIER));
	}
}
