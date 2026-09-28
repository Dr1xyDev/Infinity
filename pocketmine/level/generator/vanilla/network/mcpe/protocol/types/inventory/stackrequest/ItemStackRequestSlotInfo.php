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
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\inventory\FullContainerName;

final class ItemStackRequestSlotInfo
{
	public function __construct(
		private FullContainerName $containerName,
		private int $slotId,
		private int $stackId
	) {
	}

	public function getContainerName() : FullContainerName
	{
		return $this->containerName;
	}

	public function getSlotId() : int
	{
		return $this->slotId;
	}

	public function getStackId() : int
	{
		return $this->stackId;
	}

	public static function read(NetworkBinaryStream $in) : self
	{
		$containerName = FullContainerName::read($in);
		$slotId = $in->getByte();
		$stackId = $in->readItemStackNetIdVariant();
		return new self($containerName, $slotId, $stackId);
	}

	public function write(NetworkBinaryStream $out) : void
	{
		$this->containerName->write($out);
		$out->putByte($this->slotId);
		$out->writeItemStackNetIdVariant($this->stackId);
	}
}
