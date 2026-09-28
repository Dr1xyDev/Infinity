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

namespace pocketmine\level\generator\vanilla\network\mcpe\protocol;

use pocketmine\level\generator\vanilla\network\mcpe\NetworkSession;

class GameTestResultsPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::GAME_TEST_RESULTS_PACKET;

	private bool $success;
	private string $error;
	private string $testName;

	/**
	 * @generate-create-func
	 */
	public static function create(bool $success, string $error, string $testName) : self
	{
		$result = new self();
		$result->success = $success;
		$result->error = $error;
		$result->testName = $testName;
		return $result;
	}

	public function isSuccess() : bool
	{
		return $this->success;
	}

	public function getError() : string
	{
		return $this->error;
	}

	public function getTestName() : string
	{
		return $this->testName;
	}

	protected function decodePayload() : void
	{
		$this->success = $this->getBool();
		$this->error = $this->getString();
		$this->testName = $this->getString();
	}

	protected function encodePayload() : void
	{
		$this->putBool($this->success);
		$this->putString($this->error);
		$this->putString($this->testName);
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleGameTestResults($this);
	}
}
