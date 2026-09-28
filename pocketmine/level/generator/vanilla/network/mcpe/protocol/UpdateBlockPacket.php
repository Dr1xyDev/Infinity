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

use pocketmine\level\generator\vanilla\block\Block;
use pocketmine\level\generator\vanilla\network\mcpe\convert\block\RuntimeBlockMapping;
use pocketmine\level\generator\vanilla\network\mcpe\NetworkSession;

class UpdateBlockPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::UPDATE_BLOCK_PACKET;

	public const FLAG_NONE = 0b0000;
	public const FLAG_NEIGHBORS = 0b0001;
	public const FLAG_NETWORK = 0b0010;
	public const FLAG_NOGRAPHIC = 0b0100;
	public const FLAG_PRIORITY = 0b1000;

	public const FLAG_ALL = self::FLAG_NEIGHBORS | self::FLAG_NETWORK;
	public const FLAG_ALL_PRIORITY = self::FLAG_ALL | self::FLAG_PRIORITY;

	public const DATA_LAYER_NORMAL = 0;
	public const DATA_LAYER_LIQUID = 1;

	public int $x = 0;
	public int $z = 0;
	public int $y = 0;
	public int $blockId;
	public int $blockMeta;
	public int $flags;
	public int $dataLayerId = self::DATA_LAYER_NORMAL;
	/**
	 * Network runtime ID to send instead of the mapping of blockId:blockMeta, used for states which have no legacy
	 * equivalent (neighbour-dependent states since 1.26.50)
	 */
	public ?int $blockRuntimeId = null;

	protected function decodePayload() : void
	{
		$this->getBlockPosition($this->x, $this->y, $this->z);
		if ($this->protocol >= ProtocolInfo::PROTOCOL_407) {
			$fullState = RuntimeBlockMapping::getInstance($this->protocol)->fromRuntimeId($this->getUnsignedVarInt());
			$this->blockId = $fullState >> Block::INTERNAL_METADATA_BITS;
			$this->blockMeta = $fullState & Block::INTERNAL_METADATA_MASK;
			$this->flags = $this->getUnsignedVarInt();
			$this->dataLayerId = $this->getUnsignedVarInt();
		} else {
			$this->blockId = $this->getUnsignedVarInt();
			$aux = $this->getUnsignedVarInt();
			$this->blockMeta = $aux & 0x0f;
			$this->flags = $aux >> 4;
		}
	}

	protected function encodePayload() : void
	{
		$this->putBlockPosition($this->x, $this->y, $this->z);
		if ($this->protocol >= ProtocolInfo::PROTOCOL_407) {
			$runtimeId = $this->blockRuntimeId ?? RuntimeBlockMapping::getInstance($this->protocol)->toRuntimeId(($this->blockId << Block::INTERNAL_METADATA_BITS) | $this->blockMeta);
			$this->putUnsignedVarInt($runtimeId);
			$this->putUnsignedVarInt($this->flags);
			$this->putUnsignedVarInt($this->dataLayerId);
		} else {
			$this->putUnsignedVarInt($this->blockId);
			$this->putUnsignedVarInt(($this->flags << 4) | $this->blockMeta);
		}
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleUpdateBlock($this);
	}
}
