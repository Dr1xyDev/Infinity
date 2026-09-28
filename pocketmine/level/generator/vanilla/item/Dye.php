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

namespace pocketmine\level\generator\vanilla\item;

use pocketmine\level\generator\vanilla\network\mcpe\protocol\ProtocolInfo;
use pocketmine\level\generator\vanilla\utils\Color;

class Dye extends Item
{
	public function __construct(int $meta = 0)
	{
		parent::__construct(self::DYE, $meta, "Dye");
	}

	public function getColorFromMeta() : Color
	{
		return match ($this->meta) {
			default => new Color(0xf0, 0xf0, 0xf0),
			1 => new Color(0xf9, 0x80, 0x1d),
			2 => new Color(0xc7, 0x4e, 0xbd),
			3 => new Color(0x3a, 0xb3, 0xda),
			4 => new Color(0xfe, 0xd8, 0x3d),
			5 => new Color(0x80, 0xc7, 0x1f),
			6 => new Color(0xf3, 0x8b, 0xaa),
			7 => new Color(0x47, 0x4f, 0x52),
			8 => new Color(0x9d, 0x9d, 0x97),
			9 => new Color(0x16, 0x9c, 0x9c),
			10 => new Color(0x89, 0x32, 0xb8),
			11, 18 => new Color(0x3c, 0x44, 0xaa),
			12, 17 => new Color(0x83, 0x54, 0x32),
			13 => new Color(0x5e, 0x7c, 0x16),
			14 => new Color(0xb0, 0x2e, 0x26),
			15, 16 => new Color(0x1d, 0x1d, 0x21),
		};
	}

	public function getItemProtocol(int $playerProtocol) : ?TranslatedItemData
	{
		if ($playerProtocol < ProtocolInfo::PROTOCOL_419) {
			$colorsDyeNew = [
				16 => 0, //BLACK
				17 => 3, //BROWN
				18 => 4, //BLUE
				19 => 15, //WHITE
			];

			if (isset($colorsDyeNew[$this->getDamage()])) {
				return new TranslatedItemData($this->getId(), $colorsDyeNew[$this->getDamage()], $this->getName());
			}
		}

		return parent::getItemProtocol($playerProtocol);
	}
}
