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

namespace pocketmine\level\generator\vanilla\form\element;

use pocketmine\level\generator\vanilla\form\FormValidationException;

use function gettype;
use function is_bool;

/**
 * Represents a UI on/off switch. The switch may have a default value.
 */
class Toggle extends CustomFormElement
{
	/** @var bool */
	private $default;

	public function __construct(string $name, string $text, bool $defaultValue = false)
	{
		parent::__construct($name, $text);
		$this->default = $defaultValue;
	}

	public function getType() : string
	{
		return "toggle";
	}

	public function getDefaultValue() : bool
	{
		return $this->default;
	}

	/**
	 * @param bool $value
	 *
	 * @throws FormValidationException
	 */
	public function validateValue($value) : void
	{
		if (!is_bool($value)) {
			throw new FormValidationException("Expected bool, got " . gettype($value));
		}
	}

	protected function serializeElementData() : array
	{
		return [
			"default" => $this->default
		];
	}
}
