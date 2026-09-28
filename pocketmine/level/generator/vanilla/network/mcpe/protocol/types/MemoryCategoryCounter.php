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

final class MemoryCategoryCounter{

	public function __construct(
		private int $category,
		private int $bytes
	){}

	public function getCategory() : int{ return $this->category; }

	public function getBytes() : int{ return $this->bytes; }

	public static function read(NetworkBinaryStream $in) : self{
		$category = $in->getByte();
		$bytes = $in->getLLong();

		return new self(
			$category,
			$bytes
		);
	}

	public function write(NetworkBinaryStream $out) : void{
		$out->putByte($this->category);
		$out->putLLong($this->bytes);
	}
}
