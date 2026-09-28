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

final class CameraAimAssistCategoryEntityPriority
{
	public function __construct(
		private string $identifier,
		private int $priority
	) {
	}

	public function getIdentifier() : string
	{
		return $this->identifier;
	}

	public function getPriority() : int
	{
		return $this->priority;
	}

	public static function read(NetworkBinaryStream $in) : self
	{
		$identifier = $in->getString();
		$priority = $in->getLInt();
		return new self(
			$identifier,
			$priority
		);
	}

	public function write(NetworkBinaryStream $out) : void
	{
		$out->putString($this->identifier);
		$out->putLInt($this->priority);
	}
}
