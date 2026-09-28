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

namespace pocketmine\level\generator\vanilla\block;

class SnifferEgg extends Transparent
{
	public const int TYPE_NOT_CRACKED = 0;
	public const int TYPE_SLIGHTLY_CRACKED = 1;
	public const int TYPE_VERY_CRACKED = 2;

	protected $id = self::SNIFFER_EGG;

	public function __construct(int $meta = 0)
	{
		$this->meta = $meta;
	}

	public function getName() : string
	{
		return "Sniffer Egg";
	}

	public function getHardness() : float
	{
		return 0.5;
	}

	public function getBlastResistance() : float
	{
		return 2.5;
	}

	public function getVariantBitmask() : int
	{
		return 0;
	}
}
