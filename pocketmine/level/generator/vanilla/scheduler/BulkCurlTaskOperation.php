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

final class BulkCurlTaskOperation
{
	/**
	 * @param string[] $extraHeaders
	 * @param mixed[]  $extraOpts
	 * @phpstan-param list<string> $extraHeaders
	 * @phpstan-param array<int, mixed> $extraOpts
	 */
	public function __construct(
		private string $page,
		private float $timeout = 10,
		private array $extraHeaders = [],
		private array $extraOpts = []
	) {
	}

	public function getPage() : string
	{
		return $this->page;
	}

	public function getTimeout() : float
	{
		return $this->timeout;
	}

	/**
	 * @return string[]
	 * @phpstan-return list<string>
	 */
	public function getExtraHeaders() : array
	{
		return $this->extraHeaders;
	}

	/**
	 * @return mixed[]
	 * @phpstan-return array<int, mixed>
	 */
	public function getExtraOpts() : array
	{
		return $this->extraOpts;
	}
}
