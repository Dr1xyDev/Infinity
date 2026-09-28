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

use pocketmine\level\generator\vanilla\promise\PromiseResolver;
use pocketmine\Server;
use pocketmine\level\generator\vanilla\timings\TimingsHandler;

/**
 * @phpstan-type Resolver PromiseResolver<list<string>>
 */
final class TimingsCollectionTask extends AsyncTask
{
	/**
	 * @phpstan-param PromiseResolver<list<string>> $promiseResolver
	 */
	public function __construct(PromiseResolver $promiseResolver)
	{
		$this->storeLocal($promiseResolver);
	}

	public function onRun() : void
	{
		$this->setResult(TimingsHandler::printCurrentThreadRecords());
	}

	public function onCompletion(Server $server) : void
	{
		/**
		 * @var string[] $result
		 * @phpstan-var list<string> $result
		 */
		$result = $this->getResult();
		/**
		 * @var PromiseResolver $promiseResolver
		 * @phpstan-var PromiseResolver<list<string>> $promiseResolver
		 */
		$promiseResolver = $this->fetchLocal();

		$promiseResolver->resolve($result);
	}
}
