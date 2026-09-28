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

class StructureEditorData
{
	public const TYPE_DATA = 0;
	public const TYPE_SAVE = 1;
	public const TYPE_LOAD = 2;
	public const TYPE_CORNER = 3;
	public const TYPE_INVALID = 4;
	public const TYPE_EXPORT = 5;

	public string $structureName;
	public string $filteredStructureName;
	public string $structureDataField;
	public bool $includePlayers;
	public bool $showBoundingBox;
	public int $structureBlockType;
	public StructureSettings $structureSettings;
	public int $structureRedstoneSaveMode;
}
