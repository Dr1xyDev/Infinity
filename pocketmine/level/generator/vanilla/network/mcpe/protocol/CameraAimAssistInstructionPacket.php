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
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\camera\CameraAimAssistActionType;

class CameraAimAssistInstructionPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::CAMERA_AIM_ASSIST_INSTRUCTION_PACKET;

	private string $presetId;
	private CameraAimAssistActionType $actionType;
	private bool $allowAimAssist;

	/**
	 * @generate-create-func
	 */
	public static function create(string $presetId, CameraAimAssistActionType $actionType, bool $allowAimAssist) : self
	{
		$result = new self();
		$result->presetId = $presetId;
		$result->actionType = $actionType;
		$result->allowAimAssist = $allowAimAssist;
		return $result;
	}

	public function getPresetId() : string
	{
		return $this->presetId;
	}

	public function getActionType() : CameraAimAssistActionType
	{
		return $this->actionType;
	}

	public function getAllowAimAssist() : bool
	{
		return $this->allowAimAssist;
	}

	protected function decodePayload() : void
	{
		$this->presetId = $this->getString();
		$this->actionType = CameraAimAssistActionType::fromPacket($this->getByte());
		$this->allowAimAssist = $this->getBool();
	}

	protected function encodePayload() : void
	{
		$this->putString($this->presetId);
		$this->putByte($this->actionType->value);
		$this->putBool($this->allowAimAssist);
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleCameraAimAssistInstruction($this);
	}
}
