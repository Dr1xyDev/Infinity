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

final class BiomeNoiseGradientSurfaceData{

	/**
	 * @param int[]                      $nonReplaceableBlocks
	 * @param BiomeNoiseBlockSpecifier[] $gradientBlocks
	 * @param float[]                    $amplitudes
	 */
	public function __construct(
		private array $nonReplaceableBlocks,
		private array $gradientBlocks,
		private string $noiseSeed,
		private int $firstOctave,
		private array $amplitudes
	){}

	/**
	 * @return int[]
	 */
	public function getNonReplaceableBlocks() : array{ return $this->nonReplaceableBlocks; }

	/**
	 * @return BiomeNoiseBlockSpecifier[]
	 */
	public function getGradientBlocks() : array{ return $this->gradientBlocks; }

	public function getNoiseSeed() : string{ return $this->noiseSeed; }

	public function getFirstOctave() : int{ return $this->firstOctave; }

	/**
	 * @return float[]
	 */
	public function getAmplitudes() : array{ return $this->amplitudes; }

	public static function read(NetworkBinaryStream $in) : self{
		$nonReplaceableBlocks = [];
		for($i = 0, $count = $in->getUnsignedVarInt(); $i < $count; ++$i){
			$nonReplaceableBlocks[] = $in->getLInt();
		}

		$gradientBlocks = [];
		for($i = 0, $count = $in->getUnsignedVarInt(); $i < $count; ++$i){
			$gradientBlocks[] = BiomeNoiseBlockSpecifier::read($in);
		}

		$noiseSeed = $in->getString();
		$firstOctave = $in->getLInt();

		$amplitudes = [];
		for($i = 0, $count = $in->getUnsignedVarInt(); $i < $count; ++$i){
			$amplitudes[] = $in->getLFloat();
		}

		return new self(
			$nonReplaceableBlocks,
			$gradientBlocks,
			$noiseSeed,
			$firstOctave,
			$amplitudes
		);
	}

	public function write(NetworkBinaryStream $out) : void{
		$out->putUnsignedVarInt(count($this->nonReplaceableBlocks));
		foreach($this->nonReplaceableBlocks as $value){
			$out->putLInt($value);
		}

		$out->putUnsignedVarInt(count($this->gradientBlocks));
		foreach($this->gradientBlocks as $value){
			$value->write($out);
		}

		$out->putString($this->noiseSeed);
		$out->putLInt($this->firstOctave);

		$out->putUnsignedVarInt(count($this->amplitudes));
		foreach($this->amplitudes as $value){
			$out->putLFloat($value);
		}
	}
}
