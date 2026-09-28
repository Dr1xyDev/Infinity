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

namespace pocketmine\level\generator\vanilla\utils;

final class InternetRequestResult
{
	/**
	 * @param string[][] $headers
	 * @phpstan-param list<array<string, string>> $headers
	 */
	public function __construct(
		private array $headers,
		private string $body,
		private int $code
	) {
	}

	/**
	 * @return string[][]
	 * @phpstan-return list<array<string, string>>
	 */
	public function getHeaders() : array
	{
		return $this->headers;
	}

	public function getBody() : string
	{
		return $this->body;
	}

	public function getCode() : int
	{
		return $this->code;
	}
}
