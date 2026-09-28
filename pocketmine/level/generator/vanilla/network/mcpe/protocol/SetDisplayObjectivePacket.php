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

class SetDisplayObjectivePacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::SET_DISPLAY_OBJECTIVE_PACKET;

	/** @var string */
	public $displaySlot;
	/** @var string */
	public $objectiveName;
	/** @var string */
	public $displayName;
	/** @var string */
	public $criteriaName;
	/** @var int */
	public $sortOrder;

	protected function decodePayload() : void
	{
		$this->displaySlot = $this->getString();
		$this->objectiveName = $this->getString();
		$this->displayName = $this->getString();
		$this->criteriaName = $this->getString();
		$this->sortOrder = $this->getVarInt();
	}

	protected function encodePayload() : void
	{
		$this->putString($this->displaySlot);
		$this->putString($this->objectiveName);
		$this->putString($this->displayName);
		$this->putString($this->criteriaName);
		$this->putVarInt($this->sortOrder);
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleSetDisplayObjective($this);
	}
}
