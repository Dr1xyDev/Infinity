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

namespace pocketmine\level\generator\vanilla\network\mcpe\protocol\types\recipe;

use pocketmine\level\generator\vanilla\network\mcpe\NetworkBinaryStream;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\PacketDecodeException;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ProtocolInfo;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\GetTypeIdFromConstTrait;

final class StringIdMetaItemDescriptor implements ItemDescriptor{
	use GetTypeIdFromConstTrait;

	public const ID = ItemDescriptorType::STRING_ID_META;

	public function __construct(
		private string $id,
		private int $meta
	){
		if($meta < 0){
			throw new \InvalidArgumentException("Meta cannot be negative");
		}
	}

	public function getId() : string{ return $this->id; }

	public function getMeta() : int{ return $this->meta; }

	public static function read(NetworkBinaryStream $in) : self{
		$stringId = $in->getString();
		if($in->getProtocol() >= ProtocolInfo::PROTOCOL_2168){
			$meta = $in->getVarInt();
			if($meta < 0){
				throw new PacketDecodeException("Meta cannot be negative");
			}
		}else{
			$meta = $in->getLShort();
		}

		return new self($stringId, $meta);
	}

	public function write(NetworkBinaryStream $out) : void{
		$out->putString($this->id);
		if($out->getProtocol() >= ProtocolInfo::PROTOCOL_2168){
			$out->putVarInt($this->meta);
		}else{
			$out->putLShort($this->meta);
		}
	}
}
