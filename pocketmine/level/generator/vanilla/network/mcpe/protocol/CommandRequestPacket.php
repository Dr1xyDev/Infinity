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
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\command\CommandOriginData;

class CommandRequestPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::COMMAND_REQUEST_PACKET;

	public string $command;
	public int $playerUniqueId = 0;
	public CommandOriginData $originData;
	public bool $isInternal;
	public string $version;

	protected function decodePayload() : void
	{
		$this->command = $this->getString();
		if ($this->protocol >= ProtocolInfo::PROTOCOL_407) {
			$this->originData = $this->getCommandOriginData();
			$this->isInternal = $this->getBool();
			if ($this->protocol >= ProtocolInfo::PROTOCOL_567) {
				if ($this->protocol >= ProtocolInfo::PROTOCOL_897) {
					$this->version = $this->getString();
				} else {
					$this->version = (string) $this->getVarInt();
				}
			}
		} else {
			$this->playerUniqueId = $this->getEntityUniqueId();
		}
	}

	protected function encodePayload() : void
	{
		$this->putString($this->command);
		if ($this->protocol >= ProtocolInfo::PROTOCOL_407) {
			$this->putCommandOriginData($this->originData);
			$this->putBool($this->isInternal);
			if ($this->protocol >= ProtocolInfo::PROTOCOL_567) {
				if ($this->protocol >= ProtocolInfo::PROTOCOL_897) {
					$this->putString($this->version);
				} else {
					$this->putVarInt((int) $this->version);
				}
			}
		} else {
			$this->putEntityUniqueId($this->playerUniqueId);
		}
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleCommandRequest($this);
	}
}
