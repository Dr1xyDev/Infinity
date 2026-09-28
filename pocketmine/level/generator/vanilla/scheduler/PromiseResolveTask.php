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
use pocketmine\level\generator\vanilla\thread\NonThreadSafeValue;

class PromiseResolveTask extends AsyncTask
{
	protected \Closure $resolveFn;
	protected NonThreadSafeValue $argv;

	public function __construct(
		AsyncTaskPromise $promise
	) {
		$this->resolveFn = $promise->getClosure();
		$this->argv = new NonThreadSafeValue($promise->getArgs());

		$this->storeLocal($promise);
	}

	public function onRun() : void
	{
		try {
			$this->setResult(($this->resolveFn)(...$this->argv->deserialize()));
		} catch (\Throwable $e) {
			$this->setResult($e);
		}
	}

	public function onCompletion(Server $server) : void
	{
		$promise = $this->fetchLocal();

		if (($result = $this->getResult()) === null) {
			$promise->resolve(null);
			return;
		}

		if ($result instanceof \Throwable) {
			$promise->crash(new AsyncException(new AsyncExceptionData($result)));
			return;
		}

		if ($result instanceof NonThreadSafeValue) {
			$result = $result->deserialize();
		}

		$promise->resolve($result);
	}
}
