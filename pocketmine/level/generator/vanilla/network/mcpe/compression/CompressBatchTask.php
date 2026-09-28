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

namespace pocketmine\level\generator\vanilla\network\mcpe\compression;

use pocketmine\level\generator\vanilla\scheduler\AsyncTask;
use pocketmine\Server;

class CompressBatchTask extends AsyncTask
{
	/** @var string */
	protected $data;
	/** @var int */
	protected $protocol;
	/** @var int */
	protected $level = 7;

	public function __construct(string $data, int $protocol, int $compressionLevel, CompressBatchPromise $promise)
	{
		$this->data = $data;
		$this->protocol = $protocol;
		$this->level = $compressionLevel;

		$this->storeLocal($promise);
	}

	public function onRun()
	{
		$this->setResult(NetworkCompression::compress($this->data, $this->protocol, $this->level));
	}

	public function onCompletion(Server $server)
	{
		$promise = $this->fetchLocal();

		if ($promise instanceof CompressBatchPromise) {
			$promise->resolve($this->getResult());
		}
	}
}
