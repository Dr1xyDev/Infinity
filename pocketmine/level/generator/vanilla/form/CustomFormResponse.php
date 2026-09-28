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

use InvalidArgumentException;

class CustomFormResponse
{
	/** @var array */
	private $data;

	public function __construct(array $data)
	{
		$this->data = $data;
	}

	public function getInt(string $name) : int
	{
		$this->checkExists($name);
		return $this->data[$name];
	}

	public function getString(string $name) : string
	{
		$this->checkExists($name);
		return $this->data[$name];
	}

	public function getFloat(string $name) : float
	{
		$this->checkExists($name);
		return $this->data[$name];
	}

	public function getBool(string $name) : bool
	{
		$this->checkExists($name);
		return $this->data[$name];
	}

	public function getAll() : array
	{
		return $this->data;
	}

	private function checkExists(string $name) : void
	{
		if (!isset($this->data[$name])) {
			throw new InvalidArgumentException("Value \"$name\" not found");
		}
	}
}
