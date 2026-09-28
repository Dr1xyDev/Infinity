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

namespace pocketmine\level\generator\vanilla\plugin;

use function spl_object_id;

/**
 * @phpstan-import-type LoggerAttachment from \AttachableLogger
 */
class PluginLogger extends \PrefixedLogger implements \AttachableLogger
{
	/**
	 * @var \Closure[]
	 * @phpstan-var LoggerAttachment[]
	 */
	private array $attachments = [];

	/**
	 * @phpstan-param LoggerAttachment $attachment
	 */
	public function addAttachment(\Closure $attachment)
	{
		$this->attachments[spl_object_id($attachment)] = $attachment;
	}

	/**
	 * @phpstan-param LoggerAttachment $attachment
	 */
	public function removeAttachment(\Closure $attachment)
	{
		unset($this->attachments[spl_object_id($attachment)]);
	}

	public function removeAttachments()
	{
		$this->attachments = [];
	}

	public function getAttachments()
	{
		return $this->attachments;
	}

	public function log($level, $message) : void
	{
		parent::log($level, $message);
		foreach ($this->attachments as $attachment) {
			$attachment($level, $message);
		}
	}
}
