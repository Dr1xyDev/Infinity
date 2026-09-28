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

namespace pocketmine\level\generator\vanilla\network\mcpe\protocol\types\biome\chunkgen;

use pocketmine\level\generator\vanilla\network\mcpe\NetworkBinaryStream;
use function count;

final class BiomeScatterParamData
{
	/**
	 * @param BiomeCoordinateData[] $coordinates
	 */
	public function __construct(
		private array $coordinates,
		private int $evalOrder,
		private int $chancePercentType,
		private int $chancePercent,
		private int $chanceNumerator,
		private int $chanceDenominator,
		private int $iterationsType,
		private int $iterations,
	) {
	}

	/**
	 * @return BiomeCoordinateData[]
	 */
	public function getCoordinates() : array
	{
		return $this->coordinates;
	}

	public function getEvalOrder() : int
	{
		return $this->evalOrder;
	}

	public function getChancePercentType() : int
	{
		return $this->chancePercentType;
	}

	public function getChancePercent() : int
	{
		return $this->chancePercent;
	}

	public function getChanceNumerator() : int
	{
		return $this->chanceNumerator;
	}

	public function getChanceDenominator() : int
	{
		return $this->chanceDenominator;
	}

	public function getIterationsType() : int
	{
		return $this->iterationsType;
	}

	public function getIterations() : int
	{
		return $this->iterations;
	}

	public static function read(NetworkBinaryStream $in) : self
	{
		$coordinates = [];
		for ($i = 0, $count = $in->getUnsignedVarInt(); $i < $count; ++$i) {
			$coordinates[] = BiomeCoordinateData::read($in);
		}
		$evalOrder = $in->getVarInt();
		$chancePercentType = $in->getVarInt();
		$chancePercent = $in->getLShort();
		$chanceNumerator = $in->getLInt();
		$chanceDenominator = $in->getLInt();
		$iterationsType = $in->getVarInt();
		$iterations = $in->getLShort();

		return new self(
			$coordinates,
			$evalOrder,
			$chancePercentType,
			$chancePercent,
			$chanceNumerator,
			$chanceDenominator,
			$iterationsType,
			$iterations
		);
	}

	public function write(NetworkBinaryStream $out) : void
	{
		$out->putUnsignedVarInt(count($this->coordinates));
		foreach ($this->coordinates as $coordinate) {
			$coordinate->write($out);
		}
		$out->putVarInt($this->evalOrder);
		$out->putVarInt($this->chancePercentType);
		$out->putLShort($this->chancePercent);
		$out->putLInt($this->chanceNumerator);
		$out->putLInt($this->chanceDenominator);
		$out->putVarInt($this->iterationsType);
		$out->putLShort($this->iterations);
	}
}
