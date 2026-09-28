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

namespace pocketmine\level\generator\vanilla\network\mcpe\protocol\types\camera;

use pocketmine\level\generator\vanilla\math\Vector2;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\network\mcpe\NetworkBinaryStream;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ProtocolInfo;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\ControlScheme;

final class CameraPreset
{
	public const AUDIO_LISTENER_TYPE_CAMERA = 0;
	public const AUDIO_LISTENER_TYPE_PLAYER = 1;

	public function __construct(
		private string $name,
		private string $parent,
		private ?float $xPosition,
		private ?float $yPosition,
		private ?float $zPosition,
		private ?float $pitch,
		private ?float $yaw,
		private ?float $rotationSpeed,
		private ?bool $snapToTarget,
		private ?Vector2 $horizontalRotationLimit,
		private ?Vector2 $verticalRotationLimit,
		private ?bool $continueTargeting,
		private ?float $blockListeningRadius,
		private ?Vector2 $viewOffset,
		private ?Vector3 $entityOffset,
		private ?float $radius,
		private ?float $yawLimitMin,
		private ?float $yawLimitMax,
		private ?int $audioListenerType,
		private ?bool $playerEffects,
		private ?bool $alignTargetAndCameraForward,
		private ?CameraPresetAimAssist $aimAssist,
		private ?ControlScheme $controlScheme,
		private bool $applyInheritedStartingRotation = false, //since 1.26.50
		private ?Vector2 $startingRotation = null //since 1.26.50
	) {
	}

	public function isApplyInheritedStartingRotation() : bool
	{
		return $this->applyInheritedStartingRotation;
	}

	public function getStartingRotation() : ?Vector2
	{
		return $this->startingRotation;
	}

	public function getName() : string
	{
		return $this->name;
	}

	public function getParent() : string
	{
		return $this->parent;
	}

	public function getXPosition() : ?float
	{
		return $this->xPosition;
	}

	public function getYPosition() : ?float
	{
		return $this->yPosition;
	}

	public function getZPosition() : ?float
	{
		return $this->zPosition;
	}

	public function getPitch() : ?float
	{
		return $this->pitch;
	}

	public function getYaw() : ?float
	{
		return $this->yaw;
	}

	public function getRotationSpeed() : ?float
	{
		return $this->rotationSpeed;
	}

	public function getSnapToTarget() : ?bool
	{
		return $this->snapToTarget;
	}

	public function getHorizontalRotationLimit() : ?Vector2
	{
		return $this->horizontalRotationLimit;
	}

	public function getVerticalRotationLimit() : ?Vector2
	{
		return $this->verticalRotationLimit;
	}

	public function getContinueTargeting() : ?bool
	{
		return $this->continueTargeting;
	}

	public function getBlockListeningRadius() : ?float
	{
		return $this->blockListeningRadius;
	}

	public function getViewOffset() : ?Vector2
	{
		return $this->viewOffset;
	}

	public function getEntityOffset() : ?Vector3
	{
		return $this->entityOffset;
	}

	public function getRadius() : ?float
	{
		return $this->radius;
	}

	public function getYawLimitMin() : ?float
	{
		return $this->yawLimitMin;
	}

	public function getYawLimitMax() : ?float
	{
		return $this->yawLimitMax;
	}

	public function getAudioListenerType() : ?int
	{
		return $this->audioListenerType;
	}

	public function getPlayerEffects() : ?bool
	{
		return $this->playerEffects;
	}

	public function getAlignTargetAndCameraForward() : ?bool
	{
		return $this->alignTargetAndCameraForward;
	}

	public function getAimAssist() : ?CameraPresetAimAssist
	{
		return $this->aimAssist;
	}

	public function getControlScheme() : ?ControlScheme
	{
		return $this->controlScheme;
	}

	public static function read(NetworkBinaryStream $in) : self
	{
		$name = $in->getString();
		$parent = $in->getString();
		$xPosition = $in->getOptional($in->getLFloat(...));
		$yPosition = $in->getOptional($in->getLFloat(...));
		$zPosition = $in->getOptional($in->getLFloat(...));
		$pitch = $in->getOptional($in->getLFloat(...));
		$yaw = $in->getOptional($in->getLFloat(...));
		if ($in->getProtocol() >= ProtocolInfo::PROTOCOL_712) {
			if ($in->getProtocol() >= ProtocolInfo::PROTOCOL_729) {
				$rotationSpeed = $in->getOptional($in->getLFloat(...));
				$snapToTarget = $in->getOptional($in->getBool(...));
				if ($in->getProtocol() >= ProtocolInfo::PROTOCOL_748) {
					$horizontalRotationLimit = $in->getOptional($in->getVector2(...));
					$verticalRotationLimit = $in->getOptional($in->getVector2(...));
					$continueTargeting = $in->getOptional($in->getBool(...));
					if ($in->getProtocol() >= ProtocolInfo::PROTOCOL_766) {
						$blockListeningRadius = $in->getOptional($in->getLFloat(...));
					}
				}
			}
			$viewOffset = $in->getOptional($in->getVector2(...));
			if ($in->getProtocol() >= ProtocolInfo::PROTOCOL_729) {
				$entityOffset = $in->getOptional($in->getVector3(...));
			}
			$radius = $in->getOptional($in->getLFloat(...));
			if ($in->getProtocol() >= ProtocolInfo::PROTOCOL_776) {
				$yawLimitMin = $in->getOptional($in->getLFloat(...));
				$yawLimitMax = $in->getOptional($in->getLFloat(...));
			}
		}
		$audioListenerType = $in->getOptional($in->getByte(...));
		$playerEffects = $in->getOptional($in->getBool(...));
		if ($in->getProtocol() >= ProtocolInfo::PROTOCOL_748) {
			if ($in->getProtocol() < ProtocolInfo::PROTOCOL_818) {
				$alignTargetAndCameraForward = $in->getOptional($in->getBool(...));
			}
			if ($in->getProtocol() >= ProtocolInfo::PROTOCOL_766) {
				$aimAssist = $in->getOptional(fn () => CameraPresetAimAssist::read($in));
				if ($in->getProtocol() >= ProtocolInfo::PROTOCOL_800) {
					$controlScheme = $in->getOptional(fn () => ControlScheme::fromPacket($in->getByte()));
				}
			}
		}
		if ($in->getProtocol() >= ProtocolInfo::PROTOCOL_2193) {
			$applyInheritedStartingRotation = $in->getBool();
			$startingRotation = $in->getOptional($in->getVector2(...));
		}

		return new self(
			$name,
			$parent,
			$xPosition,
			$yPosition,
			$zPosition,
			$pitch,
			$yaw,
			$rotationSpeed ?? null,
			$snapToTarget ?? null,
			$horizontalRotationLimit ?? null,
			$verticalRotationLimit ?? null,
			$continueTargeting ?? null,
			$blockListeningRadius ?? null,
			$viewOffset ?? null,
			$entityOffset ?? null,
			$radius ?? null,
			$yawLimitMin ?? null,
			$yawLimitMax ?? null,
			$audioListenerType,
			$playerEffects,
			$alignTargetAndCameraForward ?? null,
			$aimAssist ?? null,
			$controlScheme ?? null,
			$applyInheritedStartingRotation ?? false,
			$startingRotation ?? null
		);
	}

	public function write(NetworkBinaryStream $out) : void
	{
		$out->putString($this->name);
		$out->putString($this->parent);
		$out->putOptional($this->xPosition, $out->putLFloat(...));
		$out->putOptional($this->yPosition, $out->putLFloat(...));
		$out->putOptional($this->zPosition, $out->putLFloat(...));
		$out->putOptional($this->pitch, $out->putLFloat(...));
		$out->putOptional($this->yaw, $out->putLFloat(...));
		if ($out->getProtocol() >= ProtocolInfo::PROTOCOL_712) {
			if ($out->getProtocol() >= ProtocolInfo::PROTOCOL_729) {
				$out->putOptional($this->rotationSpeed, $out->putLFloat(...));
				$out->putOptional($this->snapToTarget, $out->putBool(...));
				if ($out->getProtocol() >= ProtocolInfo::PROTOCOL_748) {
					$out->putOptional($this->horizontalRotationLimit, $out->putVector2(...));
					$out->putOptional($this->verticalRotationLimit, $out->putVector2(...));
					$out->putOptional($this->continueTargeting, $out->putBool(...));
					if ($out->getProtocol() >= ProtocolInfo::PROTOCOL_766) {
						$out->putOptional($this->blockListeningRadius, $out->putLFloat(...));
					}
				}
			}
			$out->putOptional($this->viewOffset, $out->putVector2(...));
			if ($out->getProtocol() >= ProtocolInfo::PROTOCOL_729) {
				$out->putOptional($this->entityOffset, $out->putVector3(...));
			}
			$out->putOptional($this->radius, $out->putLFloat(...));
			if ($out->getProtocol() >= ProtocolInfo::PROTOCOL_776) {
				$out->putOptional($this->yawLimitMin, $out->putLFloat(...));
				$out->putOptional($this->yawLimitMax, $out->putLFloat(...));
			}
		}

		$out->putOptional($this->audioListenerType, $out->putByte(...));
		$out->putOptional($this->playerEffects, $out->putBool(...));
		if ($out->getProtocol() >= ProtocolInfo::PROTOCOL_748) {
			if ($out->getProtocol() < ProtocolInfo::PROTOCOL_818) {
				$out->putOptional($this->alignTargetAndCameraForward, $out->putBool(...));
			}
			if ($out->getProtocol() >= ProtocolInfo::PROTOCOL_766) {
				$out->putOptional($this->aimAssist, fn (CameraPresetAimAssist $v) => $v->write($out));
				if ($out->getProtocol() >= ProtocolInfo::PROTOCOL_800) {
					$out->putOptional($this->controlScheme, fn (ControlScheme $v) => $out->putByte($v->value));
				}
			}
		}
		if ($out->getProtocol() >= ProtocolInfo::PROTOCOL_2193) {
			$out->putBool($this->applyInheritedStartingRotation);
			$out->putOptional($this->startingRotation, $out->putVector2(...));
		}
	}
}
