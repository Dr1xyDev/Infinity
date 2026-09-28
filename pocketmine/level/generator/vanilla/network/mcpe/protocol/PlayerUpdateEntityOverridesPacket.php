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
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\OverrideUpdateType;

class PlayerUpdateEntityOverridesPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::PLAYER_UPDATE_ENTITY_OVERRIDES_PACKET;

	public int $actorRuntimeId;
	public int $propertyIndex;
	public OverrideUpdateType $updateType;
	public ?int $intOverrideValue;
	public ?float $floatOverrideValue;

	/**
	 * @generate-create-func
	 */
	private static function create(int $actorRuntimeId, int $propertyIndex, OverrideUpdateType $updateType, ?int $intOverrideValue, ?float $floatOverrideValue) : self
	{
		$result = new self();
		$result->actorRuntimeId = $actorRuntimeId;
		$result->propertyIndex = $propertyIndex;
		$result->updateType = $updateType;
		$result->intOverrideValue = $intOverrideValue;
		$result->floatOverrideValue = $floatOverrideValue;
		return $result;
	}

	public static function createIntOverride(int $actorRuntimeId, int $propertyIndex, int $value) : self
	{
		return self::create($actorRuntimeId, $propertyIndex, OverrideUpdateType::SET_INT_OVERRIDE, $value, null);
	}

	public static function createFloatOverride(int $actorRuntimeId, int $propertyIndex, float $value) : self
	{
		return self::create($actorRuntimeId, $propertyIndex, OverrideUpdateType::SET_FLOAT_OVERRIDE, null, $value);
	}

	public static function createClearOverrides(int $actorRuntimeId, int $propertyIndex) : self
	{
		return self::create($actorRuntimeId, $propertyIndex, OverrideUpdateType::CLEAR_OVERRIDES, null, null);
	}

	public static function createRemoveOverride(int $actorRuntimeId, int $propertyIndex) : self
	{
		return self::create($actorRuntimeId, $propertyIndex, OverrideUpdateType::REMOVE_OVERRIDE, null, null);
	}

	/** Update type names used since 1.26.40, by ordinal */
	private const UPDATE_TYPE_NAMES_V2168 = [
		"clearoverrides",
		"removeoverride",
		"setintoverride",
		"setfloatoverride",
	];

	protected function decodePayload() : void
	{
		if ($this->protocol >= ProtocolInfo::PROTOCOL_2168) {
			//since 1.26.40 the actor is identified by its unique ID, and the type is an ordinal + name
			$this->actorRuntimeId = $this->getEntityUniqueId();
			$this->propertyIndex = $this->getUnsignedVarInt();
			$this->updateType = OverrideUpdateType::fromPacket($this->getUnsignedVarInt());
			$typeName = $this->getString();
			if ($typeName !== self::UPDATE_TYPE_NAMES_V2168[$this->updateType->value]) {
				throw new PacketDecodeException("Unexpected inner type $typeName for override update type {$this->updateType->value}");
			}
		} else {
			$this->actorRuntimeId = $this->getEntityRuntimeId();
			$this->propertyIndex = $this->getUnsignedVarInt();
			$this->updateType = OverrideUpdateType::fromPacket($this->getByte());
		}
		if ($this->updateType === OverrideUpdateType::SET_INT_OVERRIDE) {
			$this->intOverrideValue = $this->getLInt();
		} elseif ($this->updateType === OverrideUpdateType::SET_FLOAT_OVERRIDE) {
			$this->floatOverrideValue = $this->getLFloat();
		}
	}

	protected function encodePayload() : void
	{
		if ($this->protocol >= ProtocolInfo::PROTOCOL_2168) {
			$this->putEntityUniqueId($this->actorRuntimeId);
			$this->putUnsignedVarInt($this->propertyIndex);
			$this->putUnsignedVarInt($this->updateType->value);
			$this->putString(self::UPDATE_TYPE_NAMES_V2168[$this->updateType->value]);
		} else {
			//before 1.26.40 this was a runtime ID (unsigned varlong), not a unique ID (signed varlong)
			$this->putEntityRuntimeId($this->actorRuntimeId);
			$this->putUnsignedVarInt($this->propertyIndex);
			$this->putByte($this->updateType->value);
		}
		if ($this->updateType === OverrideUpdateType::SET_INT_OVERRIDE) {
			if ($this->intOverrideValue === null) { // this should never be the case
				throw new \LogicException("PlayerUpdateEntityOverridesPacket with type SET_INT_OVERRIDE require an intOverrideValue to be provided");
			}
			$this->putLInt($this->intOverrideValue);
		} elseif ($this->updateType === OverrideUpdateType::SET_FLOAT_OVERRIDE) {
			if ($this->floatOverrideValue === null) { // this should never be the case
				throw new \LogicException("PlayerUpdateEntityOverridesPacket with type SET_INT_OVERRIDE require an intOverrideValue to be provided");
			}
			$this->putLFloat($this->floatOverrideValue);
		}
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handlePlayerUpdateEntityOverridesPacket($this);
	}
}
