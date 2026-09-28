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
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ProtocolInfo;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\GetTypeIdFromConstTrait;

final class TagItemDescriptor implements ItemDescriptor{
	use GetTypeIdFromConstTrait;

	public const ID = ItemDescriptorType::TAG;

	/** Used to indicate that the item has multiple selectable variants (1.26.40+) */
	public const DEFAULT_META = 32767;

	public function __construct(
		private string $tag,
		private int $meta = self::DEFAULT_META
	){}

	public function getTag() : string{ return $this->tag; }

	public function getMeta() : int{ return $this->meta; }

	public static function read(NetworkBinaryStream $in) : self{
		$tag = $in->getString();
		$meta = $in->getProtocol() >= ProtocolInfo::PROTOCOL_2168 ? $in->getVarInt() : self::DEFAULT_META;

		return new self($tag, $meta);
	}

	public function write(NetworkBinaryStream $out) : void{
		$out->putString($this->tag);
		if($out->getProtocol() >= ProtocolInfo::PROTOCOL_2168){
			$out->putVarInt($this->meta);
		}
	}

	/**
	 * Tag-only representation, used by item stack requests since 1.26.40
	 */
	public static function readTagOnly(NetworkBinaryStream $in) : self{
		return new self($in->getString());
	}

	public function writeTagOnly(NetworkBinaryStream $out) : void{
		$out->putString($this->tag);
	}
}
