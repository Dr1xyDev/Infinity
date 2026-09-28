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

namespace pocketmine\level\generator\vanilla\network\mcpe\protocol\types\shape;

use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\PacketIntEnumTrait;

enum PrimitiveShapeType : int
{
	use PacketIntEnumTrait;

	case LINE = 0;
	case BOX = 1;
	case SPHERE = 2;
	case CIRCLE = 3;
	case TEXT = 4;
	case ARROW = 5;
	case CYLINDER = 6;
	case PYRAMID = 7;
	case ELLIPSOID = 8;
	case CONE = 9;

	/** @deprecated */
	const TEST = self::TEXT;

	public const PAYLOAD_TYPE_NONE = 0;
	public const PAYLOAD_TYPE_ARROW = 1;
	public const PAYLOAD_TYPE_TEXT = 2;
	public const PAYLOAD_TYPE_BOX = 3;
	public const PAYLOAD_TYPE_LINE = 4;
	public const PAYLOAD_TYPE_CIRCLE_OR_SPHERE = 5;
	public const PAYLOAD_TYPE_CYLINDER = 6;
	public const PAYLOAD_TYPE_PYRAMID = 7;
	public const PAYLOAD_TYPE_ELLIPSOID = 8;
	public const PAYLOAD_TYPE_CONE = 9;

	/**
	 * UGH
	 */
	public function getPayloadType() : int{
		return match($this){
			self::ARROW => self::PAYLOAD_TYPE_ARROW,
			self::TEXT => self::PAYLOAD_TYPE_TEXT,
			self::BOX => self::PAYLOAD_TYPE_BOX,
			self::LINE => self::PAYLOAD_TYPE_LINE,
			self::CIRCLE, self::SPHERE => self::PAYLOAD_TYPE_CIRCLE_OR_SPHERE,
			self::CYLINDER => self::PAYLOAD_TYPE_CYLINDER,
			self::PYRAMID => self::PAYLOAD_TYPE_PYRAMID,
			self::ELLIPSOID => self::PAYLOAD_TYPE_ELLIPSOID,
			self::CONE => self::PAYLOAD_TYPE_CONE
		};
	}
}
