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

use function count;

final class CameraAimAssistCategories
{
	/**
	 * @param CameraAimAssistCategory[] $categories
	 */
	public function __construct(
		private string $identifier,
		private array $categories
	) {
	}

	public function getIdentifier() : string
	{
		return $this->identifier;
	}

	/**
	 * @return CameraAimAssistCategory[]
	 */
	public function getCategories() : array
	{
		return $this->categories;
	}

	public static function read(NetworkBinaryStream $in) : self
	{
		$identifier = $in->getString();

		$categories = [];
		for ($i = 0, $len = $in->getUnsignedVarInt(); $i < $len; ++$i) {
			$categories[] = CameraAimAssistCategory::read($in);
		}

		return new self(
			$identifier,
			$categories
		);
	}

	public function write(NetworkBinaryStream $out) : void
	{
		$out->putString($this->identifier);
		$out->putUnsignedVarInt(count($this->categories));
		foreach ($this->categories as $category) {
			$category->write($out);
		}
	}
}
