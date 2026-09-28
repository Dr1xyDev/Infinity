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

namespace pocketmine\level\generator\vanilla\network\mcpe\protocol\types\inventory;

use pocketmine\level\generator\vanilla\network\mcpe\NetworkBinaryStream;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ProtocolInfo;

final class CreativeGroupEntry
{
	public function __construct(
		private int $categoryId,
		private string $categoryName,
		private ItemStack $icon
	) {
	}

	public function getCategoryId() : int
	{
		return $this->categoryId;
	}

	public function getCategoryName() : string
	{
		return $this->categoryName;
	}

	public function getIcon() : ItemStack
	{
		return $this->icon;
	}

	public static function read(NetworkBinaryStream $in) : self
	{
		//since 1.26.40 the category is a byte
		$categoryId = $in->getProtocol() >= ProtocolInfo::PROTOCOL_2168 ? $in->getByte() : $in->getLInt();
		$categoryName = $in->getString();
		$icon = $in->getItemStackWithoutStackId();
		return new self($categoryId, $categoryName, $icon);
	}

	public function write(NetworkBinaryStream $out) : void
	{
		if ($out->getProtocol() >= ProtocolInfo::PROTOCOL_2168) {
			$out->putByte($this->categoryId);
		} else {
			$out->putLInt($this->categoryId);
		}
		$out->putString($this->categoryName);
		$out->putItemStackWithoutStackId($this->icon);
	}
}
