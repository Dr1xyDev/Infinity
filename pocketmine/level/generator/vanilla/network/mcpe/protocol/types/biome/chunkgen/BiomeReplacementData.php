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

final class BiomeReplacementData{

	/**
	 * @param int[] $targetBiomes
	 */
	public function __construct(
		private int $biome,
		private int $dimension,
		private array $targetBiomes,
		private float $amount,
		private int $replacementIndex
	){}

	public function getBiome() : int{ return $this->biome; }

	public function getDimension() : int{ return $this->dimension; }

	/**
	 * @return int[]
	 */
	public function getTargetBiomes() : array{ return $this->targetBiomes; }

	public function getAmount() : float{ return $this->amount; }

	public function getReplacementIndex() : int{ return $this->replacementIndex; }

	public static function read(NetworkBinaryStream $in) : self{
		$biome = $in->getSignedLShort();
		$dimension = $in->getVarInt();
		$targetBiomes = [];
		for($i = 0, $count = $in->getUnsignedVarInt(); $i < $count; ++$i){
			$targetBiomes[] = $in->getSignedLShort();
		}
		$amount = $in->getLFloat();
		$replacementIndex = $in->getLInt();
		return new self($biome, $dimension, $targetBiomes, $amount, $replacementIndex);
	}

	public function write(NetworkBinaryStream $out) : void{
		$out->putLShort($this->biome);
		$out->putVarInt($this->dimension);
		$out->putUnsignedVarInt(count($this->targetBiomes));
		foreach($this->targetBiomes as $biome){
			$out->putLShort($biome);
		}
		$out->putLFloat($this->amount);
		$out->putLInt($this->replacementIndex);
	}
}
