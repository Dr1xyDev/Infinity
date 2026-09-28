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
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\StructureSettings;

class StructureTemplateDataRequestPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::STRUCTURE_TEMPLATE_DATA_REQUEST_PACKET;

	public const TYPE_ALWAYS_LOAD = 1;
	public const TYPE_CREATE_AND_LOAD = 2;

	/** @var string */
	public $structureTemplateName;
	/** @var int */
	public $structureBlockX;
	/** @var int */
	public $structureBlockY;
	/** @var int */
	public $structureBlockZ;
	/** @var StructureSettings */
	public $structureSettings;
	/** @var int */
	public $structureTemplateResponseType;

	protected function decodePayload() : void
	{
		$this->structureTemplateName = $this->getString();
		$this->getBlockPosition($this->structureBlockX, $this->structureBlockY, $this->structureBlockZ);
		$this->structureSettings = $this->getStructureSettings();
		$this->structureTemplateResponseType = $this->getByte();
	}

	protected function encodePayload() : void
	{
		$this->putString($this->structureTemplateName);
		$this->putBlockPosition($this->structureBlockX, $this->structureBlockY, $this->structureBlockZ);
		$this->putStructureSettings($this->structureSettings);
		$this->putByte($this->structureTemplateResponseType);
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleStructureTemplateDataRequest($this);
	}
}
