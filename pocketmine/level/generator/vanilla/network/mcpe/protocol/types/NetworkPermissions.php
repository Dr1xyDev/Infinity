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

final class NetworkPermissions
{
	public function __construct(
		private bool $disableClientSounds
	) {
	}

	public function disableClientSounds() : bool
	{
		return $this->disableClientSounds;
	}

	public static function decode(NetworkBinaryStream $in) : self
	{
		$disableClientSounds = $in->getBool();
		return new self($disableClientSounds);
	}

	public function encode(NetworkBinaryStream $out) : void
	{
		$out->putBool($this->disableClientSounds);
	}
}
