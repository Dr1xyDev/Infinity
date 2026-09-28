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

class CommandBlockUpdatePacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::COMMAND_BLOCK_UPDATE_PACKET;

	/** @var bool */
	public $isBlock;

	/** @var int */
	public $x;
	/** @var int */
	public $y;
	/** @var int */
	public $z;
	/** @var int */
	public $commandBlockMode;
	/** @var bool */
	public $isRedstoneMode;
	/** @var bool */
	public $isConditional;

	/** @var int */
	public $minecartEid;

	/** @var string */
	public $command;
	/** @var string */
	public $lastOutput;
	/** @var string */
	public $name;
	/** @var string */
	public $filteredName = "";
	/** @var bool */
	public $shouldTrackOutput;
	/** @var int */
	public $tickDelay;
	/** @var bool */
	public $executeOnFirstTick;

	protected function decodePayload() : void
	{
		$this->isBlock = $this->getBool();

		if ($this->isBlock) {
			$this->getBlockPosition($this->x, $this->y, $this->z);
			$this->commandBlockMode = $this->getUnsignedVarInt();
			$this->isRedstoneMode = $this->getBool();
			$this->isConditional = $this->getBool();
		} else {
			//Minecart with command block
			$this->minecartEid = $this->getEntityRuntimeId();
		}

		$this->command = $this->getString();
		$this->lastOutput = $this->getString();
		$this->name = $this->getString();
		if ($this->protocol >= ProtocolInfo::PROTOCOL_776) {
			$this->filteredName = $this->getString();
		}
		$this->shouldTrackOutput = $this->getBool();
		if ($this->protocol >= ProtocolInfo::PROTOCOL_407) {
			$this->tickDelay = $this->getLInt();
			$this->executeOnFirstTick = $this->getBool();
		}
	}

	protected function encodePayload() : void
	{
		$this->putBool($this->isBlock);

		if ($this->isBlock) {
			$this->putBlockPosition($this->x, $this->y, $this->z);
			$this->putUnsignedVarInt($this->commandBlockMode);
			$this->putBool($this->isRedstoneMode);
			$this->putBool($this->isConditional);
		} else {
			$this->putEntityRuntimeId($this->minecartEid);
		}

		$this->putString($this->command);
		$this->putString($this->lastOutput);
		$this->putString($this->name);
		if ($this->protocol >= ProtocolInfo::PROTOCOL_776) {
			$this->putString($this->filteredName);
		}
		$this->putBool($this->shouldTrackOutput);
		if ($this->protocol >= ProtocolInfo::PROTOCOL_407) {
			$this->putLInt($this->tickDelay);
			$this->putBool($this->executeOnFirstTick);
		}
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleCommandBlockUpdate($this);
	}
}
