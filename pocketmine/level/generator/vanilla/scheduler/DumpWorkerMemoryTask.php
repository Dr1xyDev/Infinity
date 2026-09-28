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

use pocketmine\MemoryManager;

use const DIRECTORY_SEPARATOR;

/**
 * Task used to dump memory from AsyncWorkers
 */
class DumpWorkerMemoryTask extends AsyncTask
{
	/** @var string */
	private $outputFolder;
	/** @var int */
	private $maxNesting;
	/** @var int */
	private $maxStringSize;

	public function __construct(string $outputFolder, int $maxNesting, int $maxStringSize)
	{
		$this->outputFolder = $outputFolder;
		$this->maxNesting = $maxNesting;
		$this->maxStringSize = $maxStringSize;
	}

	public function onRun()
	{
		MemoryManager::dumpMemory(
			$this->worker,
			$this->outputFolder . DIRECTORY_SEPARATOR . "AsyncWorker#" . $this->worker->getAsyncWorkerId(),
			$this->maxNesting,
			$this->maxStringSize,
			$this->worker->getLogger()
		);
	}
}
