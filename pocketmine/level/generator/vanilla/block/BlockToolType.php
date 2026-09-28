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

/**
 * Types of tools that can be used to break blocks
 * Blocks may allow multiple tool types by combining these bitflags
 */
interface BlockToolType
{
	public const TYPE_NONE = 0;
	public const TYPE_SWORD = 1 << 0;
	public const TYPE_SHOVEL = 1 << 1;
	public const TYPE_PICKAXE = 1 << 2;
	public const TYPE_AXE = 1 << 3;
	public const TYPE_SHEARS = 1 << 4;
	public const TYPE_HOE = 1 << 5;

}
