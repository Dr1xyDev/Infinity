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

namespace pocketmine\level\generator\vanilla\stats;

use pocketmine\level\generator\vanilla\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\level\generator\vanilla\utils\Internet;
use pocketmine\level\generator\vanilla\utils\InternetRequestResult;
use function json_decode;
use function json_encode;
use function sprintf;
use function strlen;
use const JSON_THROW_ON_ERROR;

class SendStatsTask extends AsyncTask {
	private string $apiUrl;
	private string|false $payload;
	private string $asyncName;

	/**
	 * @throws \JsonException
	 */
	public function __construct(string $apiUrl, StatsData $statsRequestData, string $asyncName = "Stats") {
		$this->asyncName = $asyncName;
		$this->apiUrl = $apiUrl;
		$data = $statsRequestData->jsonSerialize();
		$this->payload = json_encode($data, JSON_THROW_ON_ERROR);

		if ($this->payload === false) {
			throw new \JsonException("Data not encoded");
		}
	}

	public function onRun() : void {
		$result = [];

		try {
			$response = Internet::postURL(
				$this->apiUrl,
				$this->payload,
				10,
				[
					"Content-Type: application/json",
					"Content-Length: " . strlen($this->payload),
					"User-Agent: Submarine-Stats-Sender/1.0"
				]
			);

			if ($response instanceof InternetRequestResult) {
				$result['http_code'] = $response->getCode();
				$result['response'] = $response->getBody();
				$result['error'] = '';
			} else {
				$result['http_code'] = 0;
				$result['response'] = '';
				$result['error'] = 'Invalid response type';
			}

		} catch (\Throwable $e) {
			$result['http_code'] = 0;
			$result['response'] = '';
			$result['error'] = $e->getMessage();
		}

		$this->setResult($result);
	}

	/**
	 * @throws \JsonException
	 */
	public function onCompletion(Server $server) : void {
		$logger = new \PrefixedLogger(Server::getInstance()->getLogger(), $this->asyncName);
		$result = $this->getResult();

		try {
			if (!empty($result['error'])) {
				$logger->debug(sprintf("Async stats error: %s", $result['error']));
				return;
			}

			if ($result['http_code'] === 200) {
				$responseData = json_decode($result['response'], true, 512, JSON_THROW_ON_ERROR);

				if (!isset($responseData['success'])) {
					$logger->debug(sprintf("API error: %s", ($responseData['error'] ?? 'Unknown error')));
				}
			} else {
				if (isset($result['response'])) {
					$responseData = json_decode($result['response'], true, 512, JSON_THROW_ON_ERROR);
					$logger->debug(sprintf("HTTP error %s", $result['http_code'] . ": " . ($responseData['error'] ?? 'No error message')));
				}
			}
		} catch (\Throwable $e) {
			$logger->debug($e->getMessage());
		}
	}
}
