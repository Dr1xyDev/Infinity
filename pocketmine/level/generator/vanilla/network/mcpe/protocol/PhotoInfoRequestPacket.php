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

#include <rules/DataPacket.h>

use pocketmine\level\generator\vanilla\network\mcpe\NetworkSession;

class PhotoInfoRequestPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::PHOTO_INFO_REQUEST_PACKET;

	private int $photoId;

	public static function create(int $photoId) : self
	{
		$result = new self();
		$result->photoId = $photoId;
		return $result;
	}

	protected function decodePayload() : void
	{
		$this->photoId = $this->getEntityUniqueId();
	}

	protected function encodePayload() : void
	{
		$this->putEntityUniqueId($this->photoId);
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handlePhotoInfoRequest($this);
	}
}
