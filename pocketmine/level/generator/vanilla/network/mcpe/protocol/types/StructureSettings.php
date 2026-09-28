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

use pocketmine\level\generator\vanilla\math\Vector3;

class StructureSettings
{
	/** @var string */
	public $paletteName;
	/** @var bool */
	public $ignoreEntities;
	/** @var bool */
	public $ignoreBlocks;
	/** 1.19.40+ */
	public bool $allowNonTickingChunks = false;
	/** @var int */
	public $structureSizeX;
	/** @var int */
	public $structureSizeY;
	/** @var int */
	public $structureSizeZ;
	/** @var int */
	public $structureOffsetX;
	/** @var int */
	public $structureOffsetY;
	/** @var int */
	public $structureOffsetZ;
	/** @var int */
	public $lastTouchedByPlayerID;
	/** @var int */
	public $rotation;
	/** @var int */
	public $mirror;
	/** 1.18.0+ */
	public int $animationMode = 0;
	/** 1.18.0+ */
	public float $animationSeconds = 0.0;
	/** @var float */
	public $integrityValue;
	/** @var int */
	public $integritySeed;
	/** 1.16.0+ */
	public ?Vector3 $pivot = null;
}
