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

final class EducationSettingsExternalLinkSettings
{
	private string $displayName;
	private string $url;

	public function __construct(string $url, string $displayName)
	{
		$this->displayName = $displayName;
		$this->url = $url;
	}

	public function getUrl() : string
	{
		return $this->url;
	}

	public function getDisplayName() : string
	{
		return $this->displayName;
	}

	public static function read(NetworkBinaryStream $in) : self
	{
		$url = $in->getString();
		$displayName = $in->getString();
		return new self($displayName, $url);
	}

	public function write(NetworkBinaryStream $out) : void
	{
		$out->putString($this->url);
		$out->putString($this->displayName);
	}
}
