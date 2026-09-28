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
use pocketmine\level\generator\vanilla\utils\UUID;

use function count;

class EmoteListPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::EMOTE_LIST_PACKET;

	public int $playerEntityRuntimeId;
	/** @var UUID[] */
	public array $emoteIds = [];

	/**
	 * @param UUID[] $emoteIds
	 */
	public static function create(int $playerEntityRuntimeId, array $emoteIds) : self
	{
		$result = new self();
		$result->playerEntityRuntimeId = $playerEntityRuntimeId;
		$result->emoteIds = $emoteIds;
		return $result;
	}

	public function getPlayerEntityRuntimeId() : int
	{
		return $this->playerEntityRuntimeId;
	}

	/** @return UUID[] */
	public function getEmoteIds() : array
	{
		return $this->emoteIds;
	}

	protected function decodePayload() : void
	{
		$this->playerEntityRuntimeId = $this->getEntityRuntimeId();
		$this->emoteIds = [];
		for ($i = 0, $len = $this->getUnsignedVarInt(); $i < $len; ++$i) {
			$this->emoteIds[] = $this->getUUID();
		}
	}

	protected function encodePayload() : void
	{
		$this->putEntityRuntimeId($this->playerEntityRuntimeId);
		$this->putUnsignedVarInt(count($this->emoteIds));
		foreach ($this->emoteIds as $emoteId) {
			$this->putUUID($emoteId);
		}
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleEmoteList($this);
	}
}
