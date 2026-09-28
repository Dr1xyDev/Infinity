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

namespace pocketmine\level\generator\vanilla\network\mcpe\protocol\types\ddui;

use pocketmine\level\generator\vanilla\network\mcpe\NetworkBinaryStream;

/**
 * @see ClientboundDataStorePacket
 */
final class DataStoreRemoval extends DataStoreOperation{
	public const ID = DataStoreOperationType::REMOVAL;

	public function __construct(
		private string $name,
	){}

	public function getTypeId() : DataStoreOperationType{
		return self::ID;
	}

	public function getName() : string{ return $this->name; }

	public static function read(NetworkBinaryStream $in) : self{
		$name = $in->getString();

		return new self(
			$name,
		);
	}

	public function write(NetworkBinaryStream $out) : void{
		$out->putString($this->name);
	}
}
