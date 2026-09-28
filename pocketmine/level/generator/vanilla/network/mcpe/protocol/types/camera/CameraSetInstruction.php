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

final class CameraSetInstruction
{
	public function __construct(
		private int $preset,
		private ?CameraSetInstructionEase $ease,
		private ?Vector3 $cameraPosition,
		private ?CameraSetInstructionRotation $rotation,
		private ?Vector3 $facingPosition,
		private ?Vector2 $viewOffset,
		private ?Vector3 $entityOffset,
		private ?bool $default,
		private bool $ignoreStartingValuesComponent
	) {
	}

	public function getPreset() : int
	{
		return $this->preset;
	}

	public function getEase() : ?CameraSetInstructionEase
	{
		return $this->ease;
	}

	public function getCameraPosition() : ?Vector3
	{
		return $this->cameraPosition;
	}

	public function getRotation() : ?CameraSetInstructionRotation
	{
		return $this->rotation;
	}

	public function getFacingPosition() : ?Vector3
	{
		return $this->facingPosition;
	}

	public function getViewOffset() : ?Vector2
	{
		return $this->viewOffset;
	}

	public function getEntityOffset() : ?Vector3
	{
		return $this->entityOffset;
	}

	public function getDefault() : ?bool
	{
		return $this->default;
	}

	public function isIgnoringStartingValuesComponent() : bool
	{
		return $this->ignoreStartingValuesComponent;
	}

	public static function read(NetworkBinaryStream $in) : self
	{
		$preset = $in->getLInt();
		$ease = $in->getOptional(fn () => CameraSetInstructionEase::read($in));
		$cameraPosition = $in->getOptional($in->getVector3(...));
		$rotation = $in->getOptional(fn () => CameraSetInstructionRotation::read($in));
		$facingPosition = $in->getOptional($in->getVector3(...));
		if ($in->getProtocol() >= ProtocolInfo::PROTOCOL_712) {
			$viewOffset = $in->getOptional($in->getVector2(...));
			if ($in->getProtocol() >= ProtocolInfo::PROTOCOL_748) {
				$entityOffset = $in->getOptional($in->getVector3(...));
			}
		}
		$default = $in->getOptional($in->getBool(...));
		if ($in->getProtocol() >= ProtocolInfo::PROTOCOL_818) {
			$ignoreStartingValuesComponent = $in->getBool();
		}

		return new self(
			$preset,
			$ease,
			$cameraPosition,
			$rotation,
			$facingPosition,
			$viewOffset ?? null,
			$entityOffset ?? null,
			$default,
			$ignoreStartingValuesComponent ?? true
		);
	}

	public function write(NetworkBinaryStream $out) : void
	{
		$out->putLInt($this->preset);
		$out->putOptional($this->ease, fn (CameraSetInstructionEase $v) => $v->write($out));
		$out->putOptional($this->cameraPosition, $out->putVector3(...));
		$out->putOptional($this->rotation, fn (CameraSetInstructionRotation $v) => $v->write($out));
		$out->putOptional($this->facingPosition, $out->putVector3(...));
		if ($out->getProtocol() >= ProtocolInfo::PROTOCOL_712) {
			$out->putOptional($this->viewOffset, $out->putVector2(...));
			if ($out->getProtocol() >= ProtocolInfo::PROTOCOL_748) {
				$out->putOptional($this->entityOffset, $out->putVector3(...));
			}
		}
		$out->putOptional($this->default, $out->putBool(...));
		if ($out->getProtocol() >= ProtocolInfo::PROTOCOL_818) {
			$out->putBool($this->ignoreStartingValuesComponent);
		}
	}
}
