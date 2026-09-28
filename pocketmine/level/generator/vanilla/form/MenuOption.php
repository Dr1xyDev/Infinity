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

namespace pocketmine\level\generator\vanilla\form;

use JsonSerializable;

/**
 * Represents an option on a MenuForm. The option is shown as a button and may optionally have an image next to it.
 */
class MenuOption implements JsonSerializable
{
	/** @var string */
	private $text;
	/** @var FormIcon|null */
	private $image;

	public function __construct(string $text, ?FormIcon $image = null)
	{
		$this->text = $text;
		$this->image = $image;
	}

	public function getText() : string
	{
		return $this->text;
	}

	public function hasImage() : bool
	{
		return $this->image !== null;
	}

	public function getImage() : ?FormIcon
	{
		return $this->image;
	}

	public function jsonSerialize() : array
	{
		$json = [
			"text" => $this->text
		];

		if ($this->hasImage()) {
			$json["image"] = $this->image;
		}

		return $json;
	}
}
