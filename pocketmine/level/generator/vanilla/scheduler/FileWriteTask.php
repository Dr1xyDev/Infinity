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

use function file_put_contents;

class FileWriteTask extends AsyncTask
{
	/** @var string */
	private $path;
	/** @var mixed */
	private $contents;
	/** @var int */
	private $flags;

	/**
	 * @param mixed $contents
	 */
	public function __construct(string $path, $contents, int $flags = 0)
	{
		$this->path = $path;
		$this->contents = $contents;
		$this->flags = $flags;
	}

	public function onRun()
	{
		file_put_contents($this->path, $this->contents, $this->flags);
	}
}
