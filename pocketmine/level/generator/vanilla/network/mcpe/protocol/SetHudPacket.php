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
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\hud\HudElement;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\hud\HudVisibility;

use function count;

class SetHudPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::SET_HUD_PACKET;

	/** @var HudElement[] */
	public array $hudElements = [];
	public HudVisibility $visibility;

	/**
	 * @generate-create-func
	 * @param HudElement[] $hudElements
	 */
	public static function create(array $hudElements, HudVisibility $visibility) : self
	{
		$result = new self();
		$result->hudElements = $hudElements;
		$result->visibility = $visibility;
		return $result;
	}

	/** @return HudElement[] */
	public function getHudElements() : array
	{
		return $this->hudElements;
	}

	public function getVisibility() : HudVisibility
	{
		return $this->visibility;
	}

	protected function decodePayload() : void
	{
		if ($this->protocol >= ProtocolInfo::PROTOCOL_786) {
			$this->hudElements = [];
			for ($i = 0, $count = $this->getUnsignedVarInt(); $i < $count; ++$i) {
				$this->hudElements[] = HudElement::fromPacket($this->getVarInt());
			}
			$this->visibility = HudVisibility::fromPacket($this->getVarInt());
		} else {
			$this->hudElements = [];
			for ($i = 0, $count = $this->getUnsignedVarInt(); $i < $count; ++$i) {
				$this->hudElements[] = HudElement::fromPacket($this->getByte());
			}
			$this->visibility = HudVisibility::fromPacket($this->getByte());
		}
	}

	protected function encodePayload() : void
	{
		if ($this->protocol >= ProtocolInfo::PROTOCOL_786) {
			$this->putUnsignedVarInt(count($this->hudElements));
			foreach ($this->hudElements as $element) {
				$this->putVarInt($element->value);
			}
			$this->putVarInt($this->visibility->value);
		} else {
			$this->putUnsignedVarInt(count($this->hudElements));
			foreach ($this->hudElements as $element) {
				$this->putByte($element->value);
			}
			$this->putByte($this->visibility->value);
		}
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleSetHud($this);
	}
}
