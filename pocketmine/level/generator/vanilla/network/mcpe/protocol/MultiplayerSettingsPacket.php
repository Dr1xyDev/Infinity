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

class MultiplayerSettingsPacket extends DataPacket
{ //TODO: this might be clientbound too, but unsure
	public const NETWORK_ID = ProtocolInfo::MULTIPLAYER_SETTINGS_PACKET;

	public const int ACTION_ENABLE_MULTIPLAYER = 0;
	public const int ACTION_DISABLE_MULTIPLAYER = 1;
	public const int ACTION_REFRESH_JOIN_CODE = 2;

	public int $action;

	public static function create(int $action) : self
	{
		$result = new self();
		$result->action = $action;
		return $result;
	}

	public function getAction() : int
	{
		return $this->action;
	}

	protected function decodePayload() : void
	{
		$this->action = $this->getVarInt();
	}

	protected function encodePayload() : void
	{
		$this->putVarInt($this->action);
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleMultiplayerSettings($this);
	}
}
