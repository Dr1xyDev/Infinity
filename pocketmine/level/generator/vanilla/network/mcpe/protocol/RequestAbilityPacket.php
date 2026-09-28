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

use LogicException;
use pocketmine\level\generator\vanilla\network\mcpe\NetworkSession;

use function is_bool;
use function is_float;

/**
 * Sent by the client to request server enabling/disabling/changing certain abilities, such as flying, noclip, etc.
 * As of 1.19.0, the vanilla server only handles this for flying/noclip, despite there being a large range of additional
 * abilities which could be requested, and the packet supporting the use of float values.
 */
class RequestAbilityPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::REQUEST_ABILITY_PACKET;

	private const VALUE_TYPE_BOOL = 1;
	private const VALUE_TYPE_FLOAT = 2;

	public const ABILITY_FLYING = 9;
	public const ABILITY_NOCLIP = 17;

	private int $abilityId;
	private float|bool $abilityValue;

	/**
	 * @generate-create-func
	 */
	public static function create(int $abilityId, float|bool $abilityValue) : self
	{
		$result = new self();
		$result->abilityId = $abilityId;
		$result->abilityValue = $abilityValue;
		return $result;
	}

	public function getAbilityId() : int
	{
		return $this->abilityId;
	}

	public function getAbilityValue() : float|bool
	{
		return $this->abilityValue;
	}

	protected function decodePayload() : void
	{
		$this->abilityId = $this->getVarInt();

		$valueType = $this->getByte();

		//what is the point of having a type ID if you just write all the types anyway ??? mojang ...
		//only one of these values is ever used; the other(s) are discarded
		$boolValue = $this->getBool();
		$floatValue = $this->getLFloat();

		$this->abilityValue = match($valueType) {
			self::VALUE_TYPE_BOOL => $boolValue,
			self::VALUE_TYPE_FLOAT => $floatValue,
			default => throw new PacketDecodeException("Unknown ability value type $valueType")
		};
	}

	protected function encodePayload() : void
	{
		$this->putVarInt($this->abilityId);

		[$valueType, $boolValue, $floatValue] = match(true) {
			is_bool($this->abilityValue) => [self::VALUE_TYPE_BOOL, $this->abilityValue, 0.0],
			is_float($this->abilityValue) => [self::VALUE_TYPE_FLOAT, false, $this->abilityValue],
			default => throw new LogicException("Unreachable")
		};
		$this->putByte($valueType);
		$this->putBool($boolValue);
		$this->putLFloat($floatValue);
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleRequestAbility($this);
	}
}
