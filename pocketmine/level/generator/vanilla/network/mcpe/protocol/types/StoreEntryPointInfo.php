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

/**
 * @see ServerJoinInformation (1.26.20+)
 */
final class StoreEntryPointInfo{
	public function __construct(
		private string $storeId,
		private string $storeName
	){}

	public function getStoreId() : string{ return $this->storeId; }

	public function getStoreName() : string{ return $this->storeName; }

	public static function read(NetworkBinaryStream $in) : self{
		return new self($in->getString(), $in->getString());
	}

	public function write(NetworkBinaryStream $out) : void{
		$out->putString($this->storeId);
		$out->putString($this->storeName);
	}
}
