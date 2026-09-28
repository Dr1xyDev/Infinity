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

class SettingsCommandPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::SETTINGS_COMMAND_PACKET;

	public string $command;
	public bool $suppressOutput;

	public static function create(string $command, bool $suppressOutput) : self
	{
		$result = new self();
		$result->command = $command;
		$result->suppressOutput = $suppressOutput;
		return $result;
	}

	public function getCommand() : string
	{
		return $this->command;
	}

	public function getSuppressOutput() : bool
	{
		return $this->suppressOutput;
	}

	protected function decodePayload() : void
	{
		$this->command = $this->getString();
		$this->suppressOutput = $this->getBool();
	}

	protected function encodePayload() : void
	{
		$this->putString($this->command);
		$this->putBool($this->suppressOutput);
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleSettingsCommand($this);
	}
}
