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
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\EducationUriResource;

class EduUriResourcePacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::EDU_URI_RESOURCE_PACKET;

	private EducationUriResource $resource;

	public static function create(EducationUriResource $resource) : self
	{
		$result = new self();
		$result->resource = $resource;
		return $result;
	}

	public function getResource() : EducationUriResource
	{
		return $this->resource;
	}

	protected function decodePayload() : void
	{
		$this->resource = EducationUriResource::read($this);
	}

	protected function encodePayload() : void
	{
		$this->resource->write($this);
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleEduUriResource($this);
	}
}
