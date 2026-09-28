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

class MoveActorDeltaPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::MOVE_ACTOR_DELTA_PACKET;

	public const FLAG_HAS_X = 0x01;
	public const FLAG_HAS_Y = 0x02;
	public const FLAG_HAS_Z = 0x04;
	public const FLAG_HAS_ROT_X = 0x08;
	public const FLAG_HAS_ROT_Y = 0x10;
	public const FLAG_HAS_ROT_Z = 0x20;
	public const FLAG_GROUND = 0x40;
	public const FLAG_TELEPORT = 0x80;
	public const FLAG_FORCE_MOVE_LOCAL_ENTITY = 0x100;

	/** @var int */
	public $entityRuntimeId;
	/** @since 1.26.40 */
	public bool $forceCompletion = false;
	/** @since 1.26.50 - expected number of ticks before the next movement update (client interpolation duration) */
	public int $ticks = 1;
	/** @var int */
	public $flags;
	/** @var float|int */
	public $xPos = 0;
	/** @var float|int */
	public $yPos = 0;
	/** @var float|int */
	public $zPos = 0;
	/** @var float */
	public $xRot = 0.0;
	/** @var float */
	public $yRot = 0.0;
	/** @var float */
	public $zRot = 0.0;

	private function maybeReadCoord(int $flag)
	{
		if ($this->flags & $flag) {
			if ($this->protocol >= ProtocolInfo::PROTOCOL_419) {
				return $this->getLFloat();
			} else {
				return $this->getVarInt();
			}
		}
		return 0;
	}

	private function maybeReadRotation(int $flag) : float
	{
		if ($this->flags & $flag) {
			return $this->getByteRotation();
		}
		return 0.0;
	}

	protected function decodePayload() : void
	{
		$this->entityRuntimeId = $this->getEntityRuntimeId();
		if ($this->protocol >= ProtocolInfo::PROTOCOL_2168) {
			//since 1.26.40 every field is an optional and the flags are sent as individual bools
			$this->flags = 0;
			$fields = [
				[self::FLAG_HAS_X, fn() => $this->getLFloat()],
				[self::FLAG_HAS_Y, fn() => $this->getLFloat()],
				[self::FLAG_HAS_Z, fn() => $this->getLFloat()],
				[self::FLAG_HAS_ROT_X, fn() => $this->getByteRotation()],
				[self::FLAG_HAS_ROT_Y, fn() => $this->getByteRotation()],
				[self::FLAG_HAS_ROT_Z, fn() => $this->getByteRotation()],
			];
			$values = [];
			foreach ($fields as [$flag, $reader]) {
				$value = $this->getOptional($reader);
				if ($value !== null) {
					$this->flags |= $flag;
				}
				$values[] = $value ?? 0;
			}
			[$this->xPos, $this->yPos, $this->zPos, $this->xRot, $this->yRot, $this->zRot] = $values;
			foreach ([self::FLAG_GROUND, self::FLAG_TELEPORT, self::FLAG_FORCE_MOVE_LOCAL_ENTITY] as $flag) {
				if ($this->getBool()) {
					$this->flags |= $flag;
				}
			}
			$this->forceCompletion = $this->getBool();
			if ($this->protocol >= ProtocolInfo::PROTOCOL_2193) {
				$this->ticks = $this->getUnsignedVarLong();
			}
			return;
		}

		$this->flags = $this->getLShort();
		$this->xPos = $this->maybeReadCoord(self::FLAG_HAS_X);
		$this->yPos = $this->maybeReadCoord(self::FLAG_HAS_Y);
		$this->zPos = $this->maybeReadCoord(self::FLAG_HAS_Z);
		$this->xRot = $this->maybeReadRotation(self::FLAG_HAS_ROT_X);
		$this->yRot = $this->maybeReadRotation(self::FLAG_HAS_ROT_Y);
		$this->zRot = $this->maybeReadRotation(self::FLAG_HAS_ROT_Z);
	}

	private function maybeWriteCoord(int $flag, $val) : void
	{
		if ($this->flags & $flag) {
			if ($this->protocol >= ProtocolInfo::PROTOCOL_419) {
				$this->putLFloat($val);
			} else {
				$this->putVarInt($val);
			}
		}
	}

	private function maybeWriteRotation(int $flag, float $val) : void
	{
		if ($this->flags & $flag) {
			$this->putByteRotation($val);
		}
	}

	protected function encodePayload() : void
	{
		$this->putEntityRuntimeId($this->entityRuntimeId);
		if ($this->protocol >= ProtocolInfo::PROTOCOL_2168) {
			foreach ([
				[self::FLAG_HAS_X, (float) $this->xPos, false],
				[self::FLAG_HAS_Y, (float) $this->yPos, false],
				[self::FLAG_HAS_Z, (float) $this->zPos, false],
				[self::FLAG_HAS_ROT_X, $this->xRot, true],
				[self::FLAG_HAS_ROT_Y, $this->yRot, true],
				[self::FLAG_HAS_ROT_Z, $this->zRot, true],
			] as [$flag, $value, $isRotation]) {
				$this->putOptional(($this->flags & $flag) !== 0 ? $value : null, $isRotation ?
					fn(float $v) => $this->putByteRotation($v) :
					fn(float $v) => $this->putLFloat($v)
				);
			}
			$this->putBool(($this->flags & self::FLAG_GROUND) !== 0);
			$this->putBool(($this->flags & self::FLAG_TELEPORT) !== 0);
			$this->putBool(($this->flags & self::FLAG_FORCE_MOVE_LOCAL_ENTITY) !== 0);
			$this->putBool($this->forceCompletion);
			if ($this->protocol >= ProtocolInfo::PROTOCOL_2193) {
				$this->putUnsignedVarLong($this->ticks);
			}
			return;
		}

		$this->putLShort($this->flags);
		$this->maybeWriteCoord(self::FLAG_HAS_X, $this->xPos);
		$this->maybeWriteCoord(self::FLAG_HAS_Y, $this->yPos);
		$this->maybeWriteCoord(self::FLAG_HAS_Z, $this->zPos);
		$this->maybeWriteRotation(self::FLAG_HAS_ROT_X, $this->xRot);
		$this->maybeWriteRotation(self::FLAG_HAS_ROT_Y, $this->yRot);
		$this->maybeWriteRotation(self::FLAG_HAS_ROT_Z, $this->zRot);
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleMoveActorDelta($this);
	}
}
