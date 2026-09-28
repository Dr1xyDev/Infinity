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

use pocketmine\level\generator\vanilla\nbt\NetworkLittleEndianNBTStream;
use pocketmine\level\generator\vanilla\nbt\tag\CompoundTag;
use pocketmine\level\generator\vanilla\network\mcpe\NetworkSession;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\camera\CameraFadeInstruction;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\camera\CameraFovInstruction;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\camera\CameraSetInstruction;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\camera\CameraSplineInstruction;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\camera\CameraTargetInstruction;

class CameraInstructionPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::CAMERA_INSTRUCTION_PACKET;

	public ?CameraSetInstruction $set = null;
	public ?bool $clear;
	public ?CameraFadeInstruction $fade = null;
	public ?CameraTargetInstruction $target;
	public ?bool $removeTarget;
	public ?CameraFovInstruction $fieldOfView;
	private ?CameraSplineInstruction $spline;
	private ?int $attachToEntity;
	private ?bool $detachFromEntity;

	/** @phpstan-var CompoundTag */
	public CompoundTag $data; //old

	protected function decodePayload() : void
	{
		if ($this->protocol >= ProtocolInfo::PROTOCOL_618) {
			$this->set = $this->getOptional(fn () => CameraSetInstruction::read($this));
			$this->clear = $this->getOptional($this->getBool(...));
			$this->fade = $this->getOptional(fn () => CameraFadeInstruction::read($this));
			if ($this->protocol >= ProtocolInfo::PROTOCOL_729) {
				$this->target = $this->getOptional(fn () => CameraTargetInstruction::read($this));
				$this->removeTarget = $this->getOptional($this->getBool(...));
				if ($this->protocol >= ProtocolInfo::PROTOCOL_827) {
					$this->fieldOfView = $this->getOptional(fn () => CameraFovInstruction::read($this));
					if ($this->protocol >= ProtocolInfo::PROTOCOL_859) {
						$this->spline = $this->getOptional(fn () => CameraSplineInstruction::read($this));
						$this->attachToEntity = $this->getOptional($this->getLLong(...)); //WHY IS THIS NON-STANDARD?
						$this->detachFromEntity = $this->getOptional($this->getBool(...));
					}
				}
			}
		} else {
			$this->data = $this->getNbtCompoundRoot();
		}
	}

	protected function encodePayload() : void
	{
		if ($this->protocol >= ProtocolInfo::PROTOCOL_618) {
			$this->putOptional($this->set, fn (CameraSetInstruction $v) => $v->write($this));
			$this->putOptional($this->clear, $this->putBool(...));
			$this->putOptional($this->fade, fn (CameraFadeInstruction $v) => $v->write($this));
			if ($this->protocol >= ProtocolInfo::PROTOCOL_729) {
				$this->putOptional($this->target, fn (CameraTargetInstruction $v) => $v->write($this));
				$this->putOptional($this->removeTarget, $this->putBool(...));
				if ($this->protocol >= ProtocolInfo::PROTOCOL_827) {
					$this->putOptional($this->fieldOfView, fn (CameraFovInstruction $v) => $v->write($this));
					if ($this->protocol >= ProtocolInfo::PROTOCOL_859) {
						$this->putOptional($this->spline, fn(CameraSplineInstruction $v) => $v->write($this));
						$this->putOptional($this->attachToEntity, $this->putLLong(...)); //WHY IS THIS NON-STANDARD?
						$this->putOptional($this->detachFromEntity, $this->putBool(...));
					}
				}
			}
		} else {
			$this->put((new NetworkLittleEndianNBTStream())->write($this->data));
		}
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleCameraInstruction($this);
	}
}
