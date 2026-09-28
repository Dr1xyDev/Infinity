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

use pocketmine\Server;
use pocketmine\level\generator\vanilla\utils\Internet;
use pocketmine\level\generator\vanilla\utils\InternetException;
use pocketmine\level\generator\vanilla\utils\InternetRequestResult;

use function igbinary_serialize;
use function igbinary_unserialize;

/**
 * Executes a consecutive list of cURL operations.
 *
 * The result of this AsyncTask is an array of arrays (returned from {@link Internet::simpleCurl}) or InternetException objects.
 */
class BulkCurlTask extends AsyncTask
{
	private string $operations;

	/**
	 * BulkCurlTask constructor.
	 *
	 * $operations accepts an array of arrays. Each member array must contain a string mapped to "page", and optionally,
	 * "timeout", "extraHeaders" and "extraOpts". Documentation of these options are same as those in
	 * {@link Internet::simpleCurl}.
	 *
	 * @param BulkCurlTaskOperation[] $operations
	 * @phpstan-param \Closure(list<InternetRequestResult|InternetException> $results) : void $onCompletion
	 */
	public function __construct(array $operations, \Closure $onCompletion)
	{
		$this->operations = igbinary_serialize($operations);
		$this->storeLocal($onCompletion);
	}

	public function onRun() : void
	{
		/**
		 * @var BulkCurlTaskOperation[] $operations
		 * @phpstan-var list<BulkCurlTaskOperation> $operations
		 */
		$operations = igbinary_unserialize($this->operations);
		$results = [];
		foreach ($operations as $op) {
			try {
				$results[] = Internet::simpleCurl($op->getPage(), $op->getTimeout(), $op->getExtraHeaders(), $op->getExtraOpts());
			} catch (InternetException $e) {
				$results[] = $e;
			}
		}
		$this->setResult($results);
	}

	public function onCompletion(Server $server) : void
	{
		/**
		 * @var \Closure
		 * @phpstan-var \Closure(list<InternetRequestResult|InternetException>) : void
		 */
		$callback = $this->fetchLocal();
		/** @var InternetRequestResult[]|InternetException[] $results */
		$results = $this->getResult();
		$callback($results);
	}
}
