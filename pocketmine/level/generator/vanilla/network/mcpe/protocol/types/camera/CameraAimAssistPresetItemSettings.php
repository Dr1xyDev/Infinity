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

namespace pocketmine\level\generator\vanilla\network\mcpe\protocol\types\camera;

use pocketmine\level\generator\vanilla\network\mcpe\NetworkBinaryStream;

final class CameraAimAssistPresetItemSettings
{
	public function __construct(
		private string $itemIdentifier,
		private string $categoryName,
	) {
	}

	public function getItemIdentifier() : string
	{
		return $this->itemIdentifier;
	}

	public function getCategoryName() : string
	{
		return $this->categoryName;
	}

	public static function read(NetworkBinaryStream $in) : self
	{
		$itemIdentifier = $in->getString();
		$categoryName = $in->getString();
		return new self(
			$itemIdentifier,
			$categoryName
		);
	}

	public function write(NetworkBinaryStream $out) : void
	{
		$out->putString($this->itemIdentifier);
		$out->putString($this->categoryName);
	}
}
