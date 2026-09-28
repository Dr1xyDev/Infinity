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
 * @see PlayerArmorDamagePacket
 */
final class ArmorSlotAndDamagePair{

	public function __construct(
		private ArmorSlot $slot,
		private int $damage
	){}

	public function getSlot() : ArmorSlot{ return $this->slot; }

	public function getDamage() : int{ return $this->damage; }

	public static function read(NetworkBinaryStream $in) : self{
		$slot = ArmorSlot::fromPacket($in->getByte());
		$damage = $in->getLShort();

		return new self(
			$slot,
			$damage
		);
	}

	public function write(NetworkBinaryStream $out) : void{
		$out->putByte($this->slot->value);
		$out->putLShort($this->damage);
	}
}
