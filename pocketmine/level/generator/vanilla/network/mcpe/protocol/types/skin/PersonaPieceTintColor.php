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

final class PersonaPieceTintColor
{
	public const PIECE_TYPE_PERSONA_EYES = "persona_eyes";
	public const PIECE_TYPE_PERSONA_HAIR = "persona_hair";
	public const PIECE_TYPE_PERSONA_MOUTH = "persona_mouth";

	/**
	 * @param string[] $colors
	 */
	public function __construct(
		private string $pieceType,
		private array $colors
	) {
	}

	public function getPieceType() : string
	{
		return $this->pieceType;
	}

	/**
	 * @return string[]
	 */
	public function getColors() : array
	{
		return $this->colors;
	}
}
