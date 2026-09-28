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

use pocketmine\level\generator\vanilla\network\mcpe\NetworkBinaryStream;
use function count;

final class SerializableVoxelCells{

	/**
	 * @param list<int> $storage
	 */
	public function __construct(
		private int $xSize,
		private int $ySize,
		private int $zSize,
		private array $storage
	){}

	public function getXSize() : int{ return $this->xSize; }

	public function getYSize() : int{ return $this->ySize; }

	public function getZSize() : int{ return $this->zSize; }

	/**
	 * @return list<int>
	 */
	public function getStorage() : array{ return $this->storage; }

	public static function read(NetworkBinaryStream $in) : self{
		$xSize = $in->getByte();
		$ySize = $in->getByte();
		$zSize = $in->getByte();

		$storage = [];
		for($i = 0, $count = $in->getUnsignedVarInt(); $i < $count; ++$i){
			$storage[] = $in->getByte();
		}
		return new self(
			$xSize,
			$ySize,
			$zSize,
			$storage
		);
	}

	public function write(NetworkBinaryStream $out) : void{
		$out->putByte($this->xSize);
		$out->putByte($this->ySize);
		$out->putByte($this->zSize);

		$out->putUnsignedVarInt(count($this->storage));
		foreach($this->storage as $value){
			$out->putByte($value);
		}
	}
}
