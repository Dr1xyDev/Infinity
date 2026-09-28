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

use pocketmine\level\generator\vanilla\utils\Color;

class MapDecoration
{
	public const TYPE_PLAYER = 0;
	public const TYPE_FRAME = 1;
	public const TYPE_RED_MARKER = 2;
	public const TYPE_BLUE_MARKER = 3;
	public const TYPE_TARGET_X = 4;
	public const TYPE_TARGET_POINT = 5;
	public const TYPE_PLAYER_OFF_MAP = 6;
	public const TYPE_PLAYER_OFF_LIMITS = 7;
	public const TYPE_MANSION = 8;
	public const TYPE_MONUMENT = 9;
	// TODO: more ???

	/** @var int */
	private $icon;
	/** @var int */
	private $rotation;
	/** @var int */
	private $xOffset;
	/** @var int */
	private $yOffset;
	/** @var string */
	private $label;
	/** @var Color */
	private $color;

	public function __construct(int $icon, int $rotation, int $xOffset, int $yOffset, string $label, Color $color)
	{
		$this->icon = $icon;
		$this->rotation = $rotation;
		$this->xOffset = $xOffset;
		$this->yOffset = $yOffset;
		$this->label = $label;
		$this->color = $color;
	}

	public function getIcon() : int
	{
		return $this->icon;
	}

	public function getRotation() : int
	{
		return $this->rotation;
	}

	public function getXOffset() : int
	{
		return $this->xOffset;
	}

	public function getYOffset() : int
	{
		return $this->yOffset;
	}

	public function getLabel() : string
	{
		return $this->label;
	}

	public function getColor() : Color
	{
		return $this->color;
	}
}
