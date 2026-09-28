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

class PlayerFogPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::PLAYER_FOG_PACKET;

	/**
	 * @var string[]
	 * @phpstan-var list<string>
	 */
	public array $fogLayers = [];

	/**
	 * @param string[] $fogLayers
	 * @phpstan-param list<string> $fogLayers
	 */
	public static function create(array $fogLayers) : self
	{
		$result = new self();
		$result->fogLayers = $fogLayers;
		return $result;
	}

	/**
	 * @return string[]
	 * @phpstan-return list<string>
	 */
	public function getFogLayers() : array
	{
		return $this->fogLayers;
	}

	protected function decodePayload() : void
	{
		$this->fogLayers = [];
		for ($i = 0, $len = $this->getUnsignedVarInt(); $i < $len; ++$i) {
			$this->fogLayers[] = $this->getString();
		}
	}

	protected function encodePayload() : void
	{
		$this->putUnsignedVarInt(count($this->fogLayers));
		foreach ($this->fogLayers as $fogLayer) {
			$this->putString($fogLayer);
		}
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handlePlayerFog($this);
	}
}
