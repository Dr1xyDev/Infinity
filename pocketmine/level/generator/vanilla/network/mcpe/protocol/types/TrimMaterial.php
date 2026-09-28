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

final class TrimMaterial
{
	public function __construct(
		private string $materialId,
		private string $color,
		private string $itemId
	) {
	}

	public function getMaterialId() : string
	{
		return $this->materialId;
	}

	public function getColor() : string
	{
		return $this->color;
	}

	public function getItemId() : string
	{
		return $this->itemId;
	}

	public static function read(NetworkBinaryStream $in) : self
	{
		$materialId = $in->getString();
		$color = $in->getString();
		$itemId = $in->getString();
		return new self($materialId, $color, $itemId);
	}

	public function write(NetworkBinaryStream $out) : void
	{
		$out->putString($this->materialId);
		$out->putString($this->color);
		$out->putString($this->itemId);
	}
}
