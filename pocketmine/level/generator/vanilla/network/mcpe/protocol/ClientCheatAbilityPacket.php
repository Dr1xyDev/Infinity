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
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\AbilitiesData;

class ClientCheatAbilityPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::CLIENT_CHEAT_ABILITY_PACKET;

	private AbilitiesData $data;

	/**
	 * @generate-create-func
	 */
	public static function create(AbilitiesData $data) : self
	{
		$result = new self();
		$result->data = $data;
		return $result;
	}

	public function getData() : AbilitiesData
	{
		return $this->data;
	}

	protected function decodePayload() : void
	{
		$this->data = AbilitiesData::decode($this);
	}

	protected function encodePayload() : void
	{
		$this->data->encode($this);
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleClientCheatAbility($this);
	}
}
