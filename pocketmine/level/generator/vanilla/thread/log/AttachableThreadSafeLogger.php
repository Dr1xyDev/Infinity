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

namespace pocketmine\level\generator\vanilla\thread\log;

use pmmp\thread\ThreadSafeArray;

abstract class AttachableThreadSafeLogger extends ThreadSafeLogger
{
	/**
	 * @var ThreadSafeArray|ThreadSafeLoggerAttachment[]
	 * @phpstan-var ThreadSafeArray<int, ThreadSafeLoggerAttachment>
	 */
	protected ThreadSafeArray $attachments;

	public function __construct()
	{
		$this->attachments = new ThreadSafeArray();
	}

	public function addAttachment(ThreadSafeLoggerAttachment $attachment) : void
	{
		$this->attachments[] = $attachment;
	}

	public function removeAttachment(ThreadSafeLoggerAttachment $attachment) : void
	{
		foreach ($this->attachments as $i => $a) {
			if ($attachment === $a) {
				unset($this->attachments[$i]);
			}
		}
	}

	public function removeAttachments() : void
	{
		foreach ($this->attachments as $i => $a) {
			unset($this->attachments[$i]);
		}
	}

	/**
	 * @return ThreadSafeLoggerAttachment[]
	 */
	public function getAttachments() : array
	{
		return (array) $this->attachments;
	}
}
