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
use pocketmine\Player;
use pocketmine\level\generator\vanilla\utils\Utils;

use function array_values;
use function gettype;
use function is_int;

/**
 * This form type presents a menu to the user with a list of options on it. The user may select an option or close the
 * form by clicking the X in the top left corner.
 */
abstract class MenuForm extends BaseForm
{
	/** @var string */
	protected $content;
	/** @var MenuOption[] */
	private $options;
	/** @var Closure */
	private $onSubmit;
	/** @var Closure|null */
	private $onClose = null;

	/**
	 * @param MenuOption[] $options
	 * @param Closure      $onSubmit signature `function(Player $player, int $selectedOption)`
	 * @param Closure|null $onClose  signature `function(Player $player)`
	 */
	public function __construct(string $title, string $text, array $options, Closure $onSubmit, ?Closure $onClose = null)
	{
		parent::__construct($title);
		$this->content = $text;
		$this->options = array_values($options);
		Utils::validateCallableSignature(function (Player $player, int $selectedOption) : void { }, $onSubmit);
		$this->onSubmit = $onSubmit;
		if ($onClose !== null) {
			Utils::validateCallableSignature(function (Player $player) : void { }, $onClose);
			$this->onClose = $onClose;
		}
	}

	public function getOption(int $position) : ?MenuOption
	{
		return $this->options[$position] ?? null;
	}

	final public function handleResponse(Player $player, $data) : void
	{
		if ($data === null) {
			if ($this->onClose !== null) {
				($this->onClose)($player);
			}
		} elseif (is_int($data)) {
			if (!isset($this->options[$data])) {
				throw new FormValidationException("Option $data does not exist");
			}
			($this->onSubmit)($player, $data);
		} else {
			throw new FormValidationException("Expected int or null, got " . gettype($data));
		}
	}

	protected function getType() : string
	{
		return "form";
	}

	protected function serializeFormData() : array
	{
		return [
			"content" => $this->content,
			"buttons" => $this->options //yes, this is intended (MCPE calls them buttons)
		];
	}
}
