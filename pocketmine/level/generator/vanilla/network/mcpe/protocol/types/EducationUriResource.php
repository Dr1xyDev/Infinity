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

final class EducationUriResource
{
	private $buttonName;
	private $linkUri;

	public function __construct(string $buttonName, string $linkUri)
	{
		$this->buttonName = $buttonName;
		$this->linkUri = $linkUri;
	}

	public function getButtonName() : string
	{
		return $this->buttonName;
	}

	public function getLinkUri() : string
	{
		return $this->linkUri;
	}

	public static function read(NetworkBinaryStream $in) : self
	{
		$buttonName = $in->getString();
		$linkUri = $in->getString();
		return new self($buttonName, $linkUri);
	}

	public function write(NetworkBinaryStream $out) : void
	{
		$out->putString($this->buttonName);
		$out->putString($this->linkUri);
	}
}
