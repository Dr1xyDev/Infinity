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

use function count;

class ResourcePackClientResponsePacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::RESOURCE_PACK_CLIENT_RESPONSE_PACKET;

	public const STATUS_REFUSED = 1;
	public const STATUS_SEND_PACKS = 2;
	public const STATUS_HAVE_ALL_PACKS = 3;
	public const STATUS_COMPLETED = 4;

	/** @var int */
	public $status;
	/** @var string[] */
	public $packIds = [];

	/**
	 * Since 1.26.40 the status is 0-based and followed by a name. Indexed by network status (= core status - 1).
	 */
	private const STATUS_NAMES_V2168 = [
		"cancel",
		"downloading",
		"downloadingfinished",
		"resourcepackstackfinished",
	];

	protected function decodePayload() : void
	{
		if ($this->protocol >= ProtocolInfo::PROTOCOL_2168) {
			$networkStatus = $this->getUnsignedVarInt();
			$statusName = $this->getString();
			if ((self::STATUS_NAMES_V2168[$networkStatus] ?? null) !== $statusName) {
				throw new PacketDecodeException("Unexpected resource pack client response status $networkStatus ($statusName)");
			}
			$this->status = $networkStatus + 1;
			if ($this->status === self::STATUS_SEND_PACKS) {
				$entryCount = $this->getUnsignedVarInt();
				if ($entryCount > 128) {
					throw new PacketDecodeException("Too many entry count in resource pack response: " . $entryCount);
				}
				while ($entryCount-- > 0) {
					$this->packIds[] = $this->getString();
				}
			}
			return;
		}

		$this->status = $this->getByte();
		$entryCount = $this->getLShort();
		if ($entryCount > 128) {
			throw new PacketDecodeException("Too many entry count in resource pack response: " . $entryCount);
		}
		while ($entryCount-- > 0) {
			$this->packIds[] = $this->getString();
		}
	}

	protected function encodePayload() : void
	{
		if ($this->protocol >= ProtocolInfo::PROTOCOL_2168) {
			$networkStatus = $this->status - 1;
			if (!isset(self::STATUS_NAMES_V2168[$networkStatus])) {
				throw new \LogicException("Unknown resource pack client response status $this->status");
			}
			$this->putUnsignedVarInt($networkStatus);
			$this->putString(self::STATUS_NAMES_V2168[$networkStatus]);
			if ($this->status === self::STATUS_SEND_PACKS) {
				$this->putUnsignedVarInt(count($this->packIds));
				foreach ($this->packIds as $id) {
					$this->putString($id);
				}
			}
			return;
		}

		$this->putByte($this->status);
		$this->putLShort(count($this->packIds));
		foreach ($this->packIds as $id) {
			$this->putString($id);
		}
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleResourcePackClientResponse($this);
	}
}
