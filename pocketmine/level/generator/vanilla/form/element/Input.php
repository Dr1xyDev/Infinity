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
use function is_string;

/**
 * Element which accepts text input. The text-box can have a default value, and may also have a text hint when there is
 * no text in the box.
 */
class Input extends CustomFormElement
{
	/** @var string */
	private $hint;
	/** @var string */
	private $default;

	public function __construct(string $name, string $text, string $hintText = "", string $defaultText = "")
	{
		parent::__construct($name, $text);
		$this->hint = $hintText;
		$this->default = $defaultText;
	}

	public function getType() : string
	{
		return "input";
	}

	/**
	 * @param string $value
	 *
	 * @throws FormValidationException
	 */
	public function validateValue($value) : void
	{
		if (!is_string($value)) {
			throw new FormValidationException("Expected string, got " . gettype($value));
		}
	}

	/**
	 * Returns the text shown in the text-box when the box is not focused and there is no text in it.
	 */
	public function getHintText() : string
	{
		return $this->hint;
	}

	/**
	 * Returns the text which will be in the text-box by default.
	 */
	public function getDefaultText() : string
	{
		return $this->default;
	}

	protected function serializeElementData() : array
	{
		return [
			"placeholder" => $this->hint,
			"default" => $this->default
		];
	}
}
