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

use InvalidArgumentException;
use pocketmine\level\generator\vanilla\form\FormValidationException;

use function array_values;
use function gettype;
use function is_int;

abstract class BaseSelector extends CustomFormElement
{
	/** @var int */
	protected $defaultOptionIndex;
	/** @var string[] */
	protected $options;

	/**
	 * @param string[] $options
	 *
	 * @throws InvalidArgumentException
	 */
	public function __construct(string $name, string $text, array $options, int $defaultOptionIndex = 0)
	{
		parent::__construct($name, $text);
		$this->options = array_values($options);

		if (!isset($this->options[$defaultOptionIndex])) {
			throw new InvalidArgumentException("No option at index $defaultOptionIndex, cannot set as default");
		}
		$this->defaultOptionIndex = $defaultOptionIndex;
	}

	/**
	 * @param int $value
	 *
	 * @throws FormValidationException
	 */
	public function validateValue($value) : void
	{
		if (!is_int($value)) {
			throw new FormValidationException("Expected int, got " . gettype($value));
		}
		if (!isset($this->options[$value])) {
			throw new FormValidationException("Option $value does not exist");
		}
	}

	/**
	 * Returns the text of the option at the specified index, or null if it doesn't exist.
	 */
	public function getOption(int $index) : ?string
	{
		return $this->options[$index] ?? null;
	}

	public function getDefaultOptionIndex() : int
	{
		return $this->defaultOptionIndex;
	}

	public function getDefaultOption() : string
	{
		return $this->options[$this->defaultOptionIndex];
	}

	/**
	 * @return string[]
	 */
	public function getOptions() : array
	{
		return $this->options;
	}
}
