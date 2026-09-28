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

use function gettype;
use function is_float;
use function is_int;

class Slider extends CustomFormElement
{
	/** @var float */
	private $min;
	/** @var float */
	private $max;
	/** @var float */
	private $step;
	/** @var float */
	private $default;

	public function __construct(string $name, string $text, float $min, float $max, float $step = 1.0, ?float $default = null)
	{
		parent::__construct($name, $text);

		if ($this->min > $this->max) {
			throw new InvalidArgumentException("Slider min value should be less than max value");
		}
		$this->min = $min;
		$this->max = $max;

		if ($default !== null) {
			if ($default > $this->max || $default < $this->min) {
				throw new InvalidArgumentException("Default must be in range $this->min ... $this->max");
			}
			$this->default = $default;
		} else {
			$this->default = $this->min;
		}

		if ($step <= 0) {
			throw new InvalidArgumentException("Step must be greater than zero");
		}
		$this->step = $step;
	}

	public function getType() : string
	{
		return "slider";
	}

	/**
	 * @param float $value
	 *
	 * @throws FormValidationException
	 */
	public function validateValue($value) : void
	{
		if (!is_float($value) && !is_int($value)) {
			throw new FormValidationException("Expected float, got " . gettype($value));
		}
		if ($value < $this->min || $value > $this->max) {
			throw new FormValidationException("Value $value is out of bounds (min $this->min, max $this->max)");
		}
	}

	public function getMin() : float
	{
		return $this->min;
	}

	public function getMax() : float
	{
		return $this->max;
	}

	public function getStep() : float
	{
		return $this->step;
	}

	public function getDefault() : float
	{
		return $this->default;
	}

	protected function serializeElementData() : array
	{
		return [
			"min" => $this->min,
			"max" => $this->max,
			"default" => $this->default,
			"step" => $this->step
		];
	}
}
