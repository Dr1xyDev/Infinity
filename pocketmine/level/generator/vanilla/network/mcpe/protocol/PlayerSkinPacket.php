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

use pocketmine\level\generator\vanilla\entity\Skin;
use pocketmine\level\generator\vanilla\network\mcpe\NetworkSession;
use pocketmine\level\generator\vanilla\utils\UUID;

class PlayerSkinPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::PLAYER_SKIN_PACKET;

	public UUID $uuid;
	public string $oldSkinName = "";
	public string $newSkinName = "";
	public ?Skin $skin = null;

	protected function decodePayload() : void{
		$this->uuid = $this->getUUID();
		$this->skin = $this->getSkin();
		$this->newSkinName = $this->getString();
		$this->oldSkinName = $this->getString();
		if ($this->protocol < ProtocolInfo::PROTOCOL_2168) {
			$this->getBool(); //TODO: trustedSkin (moved into the skin data in 1.26.40)
		}
	}

	protected function encodePayload() : void{
		$this->putUUID($this->uuid);
		$this->putSkin($this->skin);
		$this->putString($this->newSkinName);
		$this->putString($this->oldSkinName);
		if ($this->protocol < ProtocolInfo::PROTOCOL_2168) {
			$this->putBool($this->skin->getSerializedSkin()->isTrustedSkin());
		}
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handlePlayerSkin($this);
	}
}
