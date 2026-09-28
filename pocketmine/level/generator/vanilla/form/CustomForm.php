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

use Closure;
use InvalidArgumentException;
use pocketmine\level\generator\vanilla\form\element\CustomFormElement;
use pocketmine\Player;
use pocketmine\level\generator\vanilla\utils\Utils;

use function array_values;
use function count;
use function gettype;
use function is_array;

abstract class CustomForm extends BaseForm
{
	/** @var CustomFormElement[] */
	private $elements;
	/** @var CustomFormElement[] */
	private $elementMap = [];
	/** @var Closure */
	private $onSubmit;
	/** @var Closure|null */
	private $onClose = null;

	/**
	 * @param CustomFormElement[] $elements
	 * @param Closure             $onSubmit signature `function(Player $player, CustomFormResponse $data)`
	 * @param Closure|null        $onClose  signature `function(Player $player)`
	 *
	 * @throws InvalidArgumentException
	 */
	public function __construct(string $title, array $elements, Closure $onSubmit, ?Closure $onClose = null)
	{
		parent::__construct($title);
		$this->elements = array_values($elements);
		foreach ($this->elements as $element) {
			if (isset($this->elements[$element->getName()])) {
				throw new InvalidArgumentException("Multiple elements cannot have the same name, found \"" . $element->getName() . "\" more than once");
			}
			$this->elementMap[$element->getName()] = $element;
		}

		Utils::validateCallableSignature(function (Player $player, CustomFormResponse $response) : void { }, $onSubmit);
		$this->onSubmit = $onSubmit;
		if ($onClose !== null) {
			Utils::validateCallableSignature(function (Player $player) : void { }, $onClose);
			$this->onClose = $onClose;
		}
	}

	public function getElement(int $index) : ?CustomFormElement
	{
		return $this->elements[$index] ?? null;
	}

	public function getElementByName(string $name) : ?CustomFormElement
	{
		return $this->elementMap[$name] ?? null;
	}

	/**
	 * @return CustomFormElement[]
	 */
	public function getAllElements() : array
	{
		return $this->elements;
	}

	final public function handleResponse(Player $player, $data) : void
	{
		if ($data === null) {
			if ($this->onClose !== null) {
				($this->onClose)($player);
			}
		} elseif (is_array($data)) {
			if (($actual = count($data)) !== ($expected = count($this->elements))) {
				throw new FormValidationException("Expected $expected result data, got $actual");
			}

			$values = [];

			/** @var array $data */
			foreach ($data as $index => $value) {
				if (!isset($this->elements[$index])) {
					throw new FormValidationException("Element at offset $index does not exist");
				}
				$element = $this->elements[$index];
				try {
					$element->validateValue($value);
				} catch (FormValidationException $e) {
					throw new FormValidationException("Validation failed for element \"" . $element->getName() . "\": " . $e->getMessage(), 0, $e);
				}
				$values[$element->getName()] = $value;
			}

			($this->onSubmit)($player, new CustomFormResponse($values));
		} else {
			throw new FormValidationException("Expected array or null, got " . gettype($data));
		}
	}

	protected function getType() : string
	{
		return "custom_form";
	}

	protected function serializeFormData() : array
	{
		return [
			"content" => $this->elements
		];
	}
}
