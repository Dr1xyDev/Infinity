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

namespace pocketmine\level\generator\vanilla\level\generator\structure\fortress;

/**
 * How often a piece type is chosen and how many of it one fortress may have (0 = unlimited)
 */
final class FortressPieceWeight
{
	public int $placeCount = 0;

	/**
	 * @param class-string<FortressPiece> $pieceClass
	 */
	public function __construct(
		public string $pieceClass,
		public int $weight,
		public int $maxPlaceCount,
		public bool $allowInRow = false
	) {
	}

	public function canPlace() : bool
	{
		return $this->maxPlaceCount === 0 || $this->placeCount < $this->maxPlaceCount;
	}

	/**
	 * @return FortressPieceWeight[]
	 */
	public static function bridgePieces() : array
	{
		return [
			new self(BridgeStraight::class, 30, 0, true),
			new self(BridgeCrossing::class, 10, 4),
			new self(BridgeSmallCrossing::class, 10, 4),
			new self(BridgeStairs::class, 10, 3),
			new self(MonsterThrone::class, 5, 2),
			new self(CastleEntrance::class, 5, 1)
		];
	}

	/**
	 * @return FortressPieceWeight[]
	 */
	public static function castlePieces() : array
	{
		return [
			new self(CastleCorridor::class, 25, 0, true),
			new self(CastleSmallCrossing::class, 15, 5),
			new self(CastleRightTurn::class, 5, 10),
			new self(CastleLeftTurn::class, 5, 10),
			new self(CastleStairs::class, 10, 3, true),
			new self(CastleTBalcony::class, 7, 2),
			new self(CastleStalkRoom::class, 5, 2)
		];
	}
}
