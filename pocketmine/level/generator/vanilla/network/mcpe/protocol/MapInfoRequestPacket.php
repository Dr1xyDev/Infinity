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
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\MapInfoRequestPacketClientPixel;

use function count;

class MapInfoRequestPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::MAP_INFO_REQUEST_PACKET;

	/** @var int */
	public $mapId;
	/** @var MapInfoRequestPacketClientPixel[] */
	public $clientPixels = [];

	protected function decodePayload() : void
	{
		$this->mapId = $this->getEntityUniqueId();
		if ($this->protocol >= ProtocolInfo::PROTOCOL_544) {
			$this->clientPixels = [];
			for ($i = 0, $count = $this->getLInt(); $i < $count; $i++) {
				$this->clientPixels[] = MapInfoRequestPacketClientPixel::read($this);
			}
		}
	}

	protected function encodePayload() : void
	{
		$this->putEntityUniqueId($this->mapId);
		if ($this->protocol >= ProtocolInfo::PROTOCOL_544) {
			$this->putLInt(count($this->clientPixels));
			foreach ($this->clientPixels as $pixel) {
				$pixel->write($this);
			}
		}
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleMapInfoRequest($this);
	}
}
