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

class StructureTemplateDataResponsePacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::STRUCTURE_TEMPLATE_DATA_RESPONSE_PACKET;

	/** @var string */
	public $structureTemplateName;
	public const TYPE_EXPORT = 1;
	public const TYPE_QUERY = 2;
	public const TYPE_IMPORT = 3;

	/** @var string|null serialized network NBT */
	public $namedtag;
	/** 1.18.0+ */
	public int $responseType = self::TYPE_EXPORT;

	protected function decodePayload() : void
	{
		$this->structureTemplateName = $this->getString();
		if ($this->getBool()) {
			$start = $this->getOffset();
			$this->getNbtCompoundRoot();
			$this->namedtag = substr($this->getBuffer(), $start, $this->getOffset() - $start);
		}
		if ($this->protocol >= ProtocolInfo::PROTOCOL_475) {
			$this->responseType = $this->getByte();
		}
	}

	protected function encodePayload() : void
	{
		$this->putString($this->structureTemplateName);
		$this->putBool($this->namedtag !== null);
		if ($this->namedtag !== null) {
			$this->put($this->namedtag);
		}
		if ($this->protocol >= ProtocolInfo::PROTOCOL_475) {
			$this->putByte($this->responseType);
		}
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleStructureTemplateDataResponse($this);
	}
}
