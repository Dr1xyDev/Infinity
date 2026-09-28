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

/**
 * API for Minecraft: Bedrock custom UI (forms)
 */

namespace pocketmine\level\generator\vanilla\form;

/**
 * Base class for a custom form. Forms are serialized to JSON data to be sent to clients.
 */
abstract class BaseForm implements Form
{
	/** @var string */
	protected $title;

	public function __construct(string $title)
	{
		$this->title = $title;
	}

	/**
	 * Returns the text shown on the form title-bar.
	 */
	public function getTitle() : string
	{
		return $this->title;
	}

	/**
	 * Serializes the form to JSON for sending to clients.
	 */
	final public function jsonSerialize() : array
	{
		$ret = $this->serializeFormData();
		$ret["type"] = $this->getType();
		$ret["title"] = $this->getTitle();

		return $ret;
	}

	/**
	 * Returns the type used to show this form to clients
	 */
	abstract protected function getType() : string;

	/**
	 * Serializes additional data needed to show this form to clients.
	 */
	abstract protected function serializeFormData() : array;

}
