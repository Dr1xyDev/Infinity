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

use pocketmine\level\generator\vanilla\level\generator\structure\StructureBoundingBox;
use pocketmine\level\generator\vanilla\utils\Random;
use function array_filter;
use function array_values;

/**
 * First bridge crossing of a fortress, it keeps track of the pieces used so far
 */
class FortressStartPiece extends BridgeCrossing
{
	public ?FortressPieceWeight $lastPlaced = null;
	/** @var FortressPiece[] pieces whose connections are not generated yet */
	public array $pendingChildren = [];
	/** @var FortressPieceWeight[] */
	private array $bridgeWeights;
	/** @var FortressPieceWeight[] */
	private array $castleWeights;

	public function __construct(Random $random, int $x, int $z)
	{
		$facing = self::randomHorizontal($random);
		parent::__construct(0, $random, new StructureBoundingBox($x, 64, $z, $x + 18, 73, $z + 18), $facing);
		$this->bridgeWeights = FortressPieceWeight::bridgePieces();
		$this->castleWeights = FortressPieceWeight::castlePieces();
	}

	/**
	 * @return FortressPieceWeight[]
	 */
	public function getWeights(bool $castle) : array
	{
		return $castle ? $this->castleWeights : $this->bridgeWeights;
	}

	public function removeWeight(bool $castle, FortressPieceWeight $weight) : void
	{
		$filter = static fn(FortressPieceWeight $w) : bool => $w !== $weight;
		if ($castle) {
			$this->castleWeights = array_values(array_filter($this->castleWeights, $filter));
		} else {
			$this->bridgeWeights = array_values(array_filter($this->bridgeWeights, $filter));
		}
	}
}
