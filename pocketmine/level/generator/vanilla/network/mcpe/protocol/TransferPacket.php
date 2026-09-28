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
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\GatheringJoinInfo;

class TransferPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::TRANSFER_PACKET;

	public string $address;
	public int $port = 19132;
	public bool $reloadWorld = false;
	/** Gatherings experience to join, 1.26.40+ only */
	public ?GatheringJoinInfo $gatheringsConfig = null;

	protected function decodePayload() : void
	{
		$this->address = $this->getString();
		$this->port = $this->getLShort();
		if ($this->protocol >= ProtocolInfo::PROTOCOL_729) {
			$this->reloadWorld = $this->getBool();
		}
		if ($this->protocol >= ProtocolInfo::PROTOCOL_2168) {
			$this->gatheringsConfig = $this->getOptional(fn() => GatheringJoinInfo::read($this));
		}
	}

	protected function encodePayload() : void
	{
		$this->putString($this->address);
		$this->putLShort($this->port);
		if ($this->protocol >= ProtocolInfo::PROTOCOL_729) {
			$this->putBool($this->reloadWorld);
		}
		if ($this->protocol >= ProtocolInfo::PROTOCOL_2168) {
			$this->putOptional($this->gatheringsConfig, fn(GatheringJoinInfo $v) => $v->write($this));
		}
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleTransfer($this);
	}
}
