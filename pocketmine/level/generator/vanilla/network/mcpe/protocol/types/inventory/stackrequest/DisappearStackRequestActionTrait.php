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

namespace pocketmine\level\generator\vanilla\network\mcpe\protocol\types\inventory\stackrequest;

use pocketmine\level\generator\vanilla\network\mcpe\NetworkBinaryStream;

trait DisappearStackRequestActionTrait
{
	final public function __construct(
		private int $count,
		private ItemStackRequestSlotInfo $source
	) {
	}

	final public function getCount() : int
	{
		return $this->count;
	}

	final public function getSource() : ItemStackRequestSlotInfo
	{
		return $this->source;
	}

	public static function read(NetworkBinaryStream $in) : self
	{
		$count = $in->getByte();
		$source = ItemStackRequestSlotInfo::read($in);
		return new self($count, $source);
	}

	public function write(NetworkBinaryStream $out) : void
	{
		$out->putByte($this->count);
		$this->source->write($out);
	}
}
