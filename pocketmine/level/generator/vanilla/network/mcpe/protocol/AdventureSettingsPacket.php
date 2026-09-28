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
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\AdventureSettingsData;

class AdventureSettingsPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::ADVENTURE_SETTINGS_PACKET;

	public AdventureSettingsData $adventureSettingsData;

	/**
	 * @generate-create-func
	 */
	public static function create(AdventureSettingsData $adventureSettingsData) : self
	{
		$result = new self();
		$result->adventureSettingsData = $adventureSettingsData;
		return $result;
	}

	public function getAdventureSettingsData() : AdventureSettingsData {
		return $this->adventureSettingsData;
	}

	protected function decodePayload() : void
	{
		$this->adventureSettingsData = AdventureSettingsData::decode($this);
	}

	protected function encodePayload() : void
	{
		$this->adventureSettingsData->encode($this);
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleAdventureSettings($this);
	}
}
