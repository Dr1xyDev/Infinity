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
use pocketmine\level\generator\vanilla\network\mcpe\protocol\serializer\BitSet;

class ClientMovementPredictionSyncPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::CLIENT_MOVEMENT_PREDICTION_SYNC_PACKET;

	/**
	 * Number of actor flags in the bitset (EntityMetadataFlags::NUMBER_OF_FLAGS of the client version)
	 */
	public static function getFlagsLength(int $protocol) : int
	{
		return match (true) {
			$protocol >= ProtocolInfo::PROTOCOL_2168 => 131,
			$protocol >= ProtocolInfo::PROTOCOL_975 => 128,
			$protocol >= ProtocolInfo::PROTOCOL_897 => 127,
			$protocol >= ProtocolInfo::PROTOCOL_844 => 126,
			$protocol >= ProtocolInfo::PROTOCOL_818 => 125,
			$protocol >= ProtocolInfo::PROTOCOL_800 => 124,
			default => 123
		};
	}

	private BitSet $flags;

	private float $scale;
	private float $width;
	private float $height;

	private float $movementSpeed;
	private float $underwaterMovementSpeed;
	private float $lavaMovementSpeed;
	private float $jumpStrength;
	private float $health;
	private float $hunger;
	private float $frictionModifier;
	private float $bounciness;
	private float $airDragModifier;

	private int $actorUniqueId;
	private bool $actorFlyingState;

	/**
	 * @generate-create-func
	 */
	private static function internalCreate(
		BitSet $flags,
		float $scale,
		float $width,
		float $height,
		float $movementSpeed,
		float $underwaterMovementSpeed,
		float $lavaMovementSpeed,
		float $jumpStrength,
		float $health,
		float $hunger,
		float $frictionModifier,
		float $bounciness,
		float $airDragModifier,
		int $actorUniqueId,
		bool $actorFlyingState
	) : self {
		$result = new self();
		$result->flags = $flags;
		$result->scale = $scale;
		$result->width = $width;
		$result->height = $height;
		$result->movementSpeed = $movementSpeed;
		$result->underwaterMovementSpeed = $underwaterMovementSpeed;
		$result->lavaMovementSpeed = $lavaMovementSpeed;
		$result->jumpStrength = $jumpStrength;
		$result->health = $health;
		$result->hunger = $hunger;
		$result->frictionModifier = $frictionModifier;
		$result->bounciness = $bounciness;
		$result->airDragModifier = $airDragModifier;
		$result->actorUniqueId = $actorUniqueId;
		$result->actorFlyingState = $actorFlyingState;
		return $result;
	}

	public static function create(
		BitSet $flags,
		float $scale,
		float $width,
		float $height,
		float $movementSpeed,
		float $underwaterMovementSpeed,
		float $lavaMovementSpeed,
		float $jumpStrength,
		float $health,
		float $hunger,
		float $frictionModifier,
		float $bounciness,
		float $airDragModifier,
		int $actorUniqueId,
		bool $actorFlyingState
	) : self{
		return self::internalCreate($flags, $scale, $width, $height, $movementSpeed, $underwaterMovementSpeed, $lavaMovementSpeed, $jumpStrength, $health, $hunger, $frictionModifier, $bounciness, $airDragModifier, $actorUniqueId, $actorFlyingState);
	}

	public function getFlags() : BitSet{ return $this->flags; }

	public function getScale() : float{ return $this->scale; }

	public function getWidth() : float{ return $this->width; }

	public function getHeight() : float{ return $this->height; }

	public function getMovementSpeed() : float{ return $this->movementSpeed; }

	public function getUnderwaterMovementSpeed() : float{ return $this->underwaterMovementSpeed; }

	public function getLavaMovementSpeed() : float{ return $this->lavaMovementSpeed; }

	public function getJumpStrength() : float{ return $this->jumpStrength; }

	public function getHealth() : float{ return $this->health; }

	public function getHunger() : float{ return $this->hunger; }

	public function getFrictionModifier() : float{ return $this->frictionModifier; }

	public function getBounciness() : float{ return $this->bounciness; }

	public function getAirDragModifier() : float{ return $this->airDragModifier; }

	public function getActorUniqueId() : int{ return $this->actorUniqueId; }

	public function getActorFlyingState() : bool{ return $this->actorFlyingState; }

	protected function decodePayload() : void
	{
		$this->flags = BitSet::read($this, self::getFlagsLength($this->protocol));
		$this->scale = $this->getLFloat();
		$this->width = $this->getLFloat();
		$this->height = $this->getLFloat();
		$this->movementSpeed = $this->getLFloat();
		$this->underwaterMovementSpeed = $this->getLFloat();
		$this->lavaMovementSpeed = $this->getLFloat();
		$this->jumpStrength = $this->getLFloat();
		$this->health = $this->getLFloat();
		$this->hunger = $this->getLFloat();
		if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_975) {
			$this->frictionModifier = $this->getLFloat();
			$this->bounciness = $this->getLFloat();
			$this->airDragModifier = $this->getLFloat();
		}

		$this->actorUniqueId = $this->getEntityUniqueId();
		if ($this->protocol >= ProtocolInfo::PROTOCOL_786) {
			$this->actorFlyingState = $this->getBool();
		}
	}

	protected function encodePayload() : void
	{
		$this->flags->write($this);
		$this->putLFloat($this->scale);
		$this->putLFloat($this->width);
		$this->putLFloat($this->height);
		$this->putLFloat($this->movementSpeed);
		$this->putLFloat($this->underwaterMovementSpeed);
		$this->putLFloat($this->lavaMovementSpeed);
		$this->putLFloat($this->jumpStrength);
		$this->putLFloat($this->health);
		$this->putLFloat($this->hunger);
		if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_975) {
			$this->putLFloat($this->frictionModifier);
			$this->putLFloat($this->bounciness);
			$this->putLFloat($this->airDragModifier);
		}

		$this->putEntityUniqueId($this->actorUniqueId);
		if ($this->protocol >= ProtocolInfo::PROTOCOL_786) {
			$this->putBool($this->actorFlyingState);
		}
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleClientMovementPredictionSync($this);
	}
}
