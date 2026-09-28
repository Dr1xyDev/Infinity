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

use function array_flip;
use function str_starts_with;

/**
 * Persona piece types are stored internally in the login JSON format ("persona_body"). Since 1.26.40 the network uses
 * an ordinal for skin pieces and a short name ("body") for piece tint colors, so this handles the conversion.
 */
final class PersonaSkinPieceType
{
	/**
	 * Ordered by network ordinal.
	 * UNSUPPORTED (28) was added in 1.26.45.
	 */
	private const JSON_NAMES = [
		"persona_unknown",
		"persona_skeleton",
		"persona_body",
		"persona_skin",
		"persona_bottom",
		"persona_feet",
		"persona_dress",
		"persona_top",
		"persona_high_pants",
		"persona_hand",
		"persona_outerwear",
		"persona_facial_hair",
		"persona_mouth",
		"persona_eyes",
		"persona_hair",
		"persona_hood",
		"persona_back",
		"persona_face_accessory",
		"persona_head",
		"persona_legs",
		"persona_left_leg",
		"persona_right_leg",
		"persona_arms",
		"persona_left_arm",
		"persona_right_arm",
		"persona_capes",
		"persona_classic_skin",
		"persona_emote",
		"persona_unsupported",
	];

	/** Network names (used for tint colors), ordered by network ordinal */
	private const NETWORK_NAMES = [
		"unknown",
		"skeleton",
		"body",
		"skin",
		"bottom",
		"feet",
		"dress",
		"top",
		"high_pants",
		"hands",
		"outerwear",
		"facialhair",
		"mouth",
		"eyes",
		"hair",
		"hood",
		"back",
		"faceaccessory",
		"head",
		"legs",
		"leftleg",
		"rightleg",
		"arms",
		"leftarm",
		"rightarm",
		"capes",
		"classicskin",
		"emote",
		"unsupported",
	];

	public const ORDINAL_UNKNOWN = 0;
	public const ORDINAL_UNSUPPORTED = 28;

	private function __construct()
	{
		//NOOP
	}

	public static function jsonToOrdinal(string $jsonName) : int
	{
		/** @var int[]|null $map */
		static $map = null;
		$map ??= array_flip(self::JSON_NAMES);
		if (isset($map[$jsonName])) {
			return $map[$jsonName];
		}
		//tolerate network names being passed in as well (e.g. from plugins)
		return self::networkNameToOrdinal($jsonName) ?? self::ORDINAL_UNKNOWN;
	}

	public static function ordinalToJson(int $ordinal) : string
	{
		return self::JSON_NAMES[$ordinal] ?? self::JSON_NAMES[self::ORDINAL_UNKNOWN];
	}

	public static function networkNameToOrdinal(string $networkName) : ?int
	{
		/** @var int[]|null $map */
		static $map = null;
		$map ??= array_flip(self::NETWORK_NAMES);
		return $map[$networkName] ?? null;
	}

	public static function jsonToNetworkName(string $jsonName) : string
	{
		if (!str_starts_with($jsonName, "persona_") && self::networkNameToOrdinal($jsonName) !== null) {
			return $jsonName;
		}
		return self::NETWORK_NAMES[self::jsonToOrdinal($jsonName)];
	}

	public static function networkNameToJson(string $networkName) : string
	{
		$ordinal = self::networkNameToOrdinal($networkName);
		if ($ordinal !== null) {
			return self::JSON_NAMES[$ordinal];
		}
		return str_starts_with($networkName, "persona_") ? $networkName : "persona_" . $networkName;
	}

	/**
	 * Clients before 1.26.45 don't know about the UNSUPPORTED piece type
	 */
	public static function clampOrdinal(int $ordinal, bool $supportsUnsupported) : int
	{
		if ($ordinal === self::ORDINAL_UNSUPPORTED && !$supportsUnsupported) {
			return self::ORDINAL_UNKNOWN;
		}
		return $ordinal;
	}
}
