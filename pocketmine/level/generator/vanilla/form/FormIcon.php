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
 * Represents an icon which can be placed next to options on menus, or as the icon for the server-settings form type.
 */
class FormIcon implements JsonSerializable
{
	public const IMAGE_TYPE_URL = "url";
	public const IMAGE_TYPE_PATH = "path";

	/** @var string */
	private $type;
	/** @var string */
	private $data;

	/**
	 * @param string $data URL or path depending on the type chosen.
	 * @param string $type Can be one of the constants at the top of the file, but only "url" is known to work.
	 */
	public function __construct(string $data, string $type = self::IMAGE_TYPE_URL)
	{
		$this->type = $type;
		$this->data = $data;
	}

	public function getType() : string
	{
		return $this->type;
	}

	public function getData() : string
	{
		return $this->data;
	}

	public function jsonSerialize() : array
	{
		return [
			"type" => $this->type,
			"data" => $this->data
		];
	}
}
