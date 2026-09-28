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

namespace pocketmine\level\generator\vanilla\network\mcpe\protocol\types\resourcepacks;

final class ResourcePackType
{
	private function __construct()
	{
		//NOOP
	}

	public const INVALID = 0;
	public const ADDON = 1;
	public const CACHED = 2;
	public const COPY_PROTECTED = 3;
	public const BEHAVIORS = 4;
	public const PERSONA_PIECE = 5;
	public const RESOURCES = 6;
	public const SKINS = 7;
	public const WORLD_TEMPLATE = 8;
	public const COUNT = 9;
}
