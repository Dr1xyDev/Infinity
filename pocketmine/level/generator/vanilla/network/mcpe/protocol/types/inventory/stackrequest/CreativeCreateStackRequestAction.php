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

namespace pocketmine\level\generator\vanilla\network\mcpe\protocol\types\inventory\stackrequest;

use pocketmine\level\generator\vanilla\network\mcpe\NetworkBinaryStream;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ProtocolInfo;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\GetTypeIdFromConstTrait;

/**
 * Creates an item by copying it from the creative inventory. This is treated as a crafting action by vanilla.
 */
final class CreativeCreateStackRequestAction extends ItemStackRequestAction
{
	use GetTypeIdFromConstTrait;

	public const ID = ItemStackRequestActionType::CREATIVE_CREATE;

	public function __construct(
		private int $creativeItemId,
		private int $repetitions
	) {
	}

	public function getCreativeItemId() : int
	{
		return $this->creativeItemId;
	}

	public function getRepetitions() : int
	{
		return $this->repetitions;
	}

	public static function read(NetworkBinaryStream $in) : self
	{
		$creativeItemId = $in->readCreativeItemNetId();
		if ($in->getProtocol() >= ProtocolInfo::PROTOCOL_712) {
			$repetitions = $in->getByte();
		}
		return new self($creativeItemId, $repetitions ?? 1);
	}

	public function write(NetworkBinaryStream $out) : void
	{
		$out->writeCreativeItemNetId($this->creativeItemId);
		if ($out->getProtocol() >= ProtocolInfo::PROTOCOL_712) {
			$out->putByte($this->repetitions);
		}
	}
}
