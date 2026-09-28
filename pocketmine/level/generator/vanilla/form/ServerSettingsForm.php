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

/**
 * Represents a custom form which can be shown in the Settings menu on the client. This is exactly the same as a regular
 * CustomForm, except that this type can also have an icon which can be shown on the settings section button.
 *
 * Passing this form to {@link Player::sendForm()} will not show a form with an icon nor set this form as the server
 * settings.
 */
abstract class ServerSettingsForm extends CustomForm
{
	/** @var FormIcon|null */
	private $icon;

	public function __construct(string $title, array $elements, ?FormIcon $icon, Closure $onSubmit, ?Closure $onClose = null)
	{
		parent::__construct($title, $elements, $onSubmit, $onClose);
		$this->icon = $icon;
	}

	public function hasIcon() : bool
	{
		return $this->icon !== null;
	}

	public function getIcon() : ?FormIcon
	{
		return $this->icon;
	}

	protected function serializeFormData() : array
	{
		$data = parent::serializeFormData();

		if ($this->hasIcon()) {
			$data["icon"] = $this->icon;
		}

		return $data;
	}
}
