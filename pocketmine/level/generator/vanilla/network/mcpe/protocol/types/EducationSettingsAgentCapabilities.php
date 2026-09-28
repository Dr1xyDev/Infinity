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

final class EducationSettingsAgentCapabilities
{
	private ?bool $canModifyBlocks;

	public function __construct(?bool $canModifyBlocks)
	{
		$this->canModifyBlocks = $canModifyBlocks;
	}

	public function getCanModifyBlocks() : ?bool
	{
		return $this->canModifyBlocks;
	}

	public static function read(NetworkBinaryStream $in) : self
	{
		$canModifyBlocks = $in->getBool() ? $in->getBool() : null;
		return new self($canModifyBlocks);
	}

	public function write(NetworkBinaryStream $out) : void
	{
		if ($this->canModifyBlocks !== null) {
			$out->putBool(true);
			$out->putBool($this->canModifyBlocks);
		} else {
			$out->putBool(false);
		}
	}
}
