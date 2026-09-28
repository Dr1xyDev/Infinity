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

namespace pocketmine\level\generator\vanilla\scheduler;

use Exception;

class AsyncException extends Exception
{
	/** @var AsyncExceptionData */
	protected $asyncData;

	public function __construct(AsyncExceptionData $data)
	{
		parent::__construct($data->getClass() . ": " . $data->getMessage(), $data->getCode());

		$this->asyncData = $data;

		$this->file = $data->getFile();
		$this->line = $data->getLine();
	}

	public function getAsyncData() : AsyncExceptionData
	{
		return $this->asyncData;
	}
}
