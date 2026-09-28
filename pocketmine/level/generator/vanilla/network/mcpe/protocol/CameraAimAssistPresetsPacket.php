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
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\camera\CameraAimAssistCategories;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\camera\CameraAimAssistCategory;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\camera\CameraAimAssistPreset;

use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\camera\CameraAimAssistPresetsPacketOperation;
use function count;

class CameraAimAssistPresetsPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::CAMERA_AIM_ASSIST_PRESETS_PACKET;

	/** @var CameraAimAssistCategory[]|CameraAimAssistCategories[] */
	private array $categories = [];
	/** @var CameraAimAssistPreset[] */
	private array $presets = [];
	private CameraAimAssistPresetsPacketOperation $operation;

	/**
	 * @generate-create-func
	 * @param CameraAimAssistCategory[]|CameraAimAssistCategories[] $categories
	 * @param CameraAimAssistPreset[]                               $presets
	 */
	public static function create(array $categories, array $presets, CameraAimAssistPresetsPacketOperation $operation) : self
	{
		$result = new self();
		$result->categories = $categories;
		$result->presets = $presets;
		$result->operation = $operation;
		return $result;
	}

	/**
	 * @return CameraAimAssistCategory[]|CameraAimAssistCategories[]
	 */
	public function getCategories() : array
	{
		return $this->categories;
	}

	/**
	 * @return CameraAimAssistPreset[]
	 */
	public function getPresets() : array
	{
		return $this->presets;
	}

	public function getOperation() : CameraAimAssistPresetsPacketOperation
	{
		return $this->operation;
	}

	protected function decodePayload() : void
	{
		$this->categories = [];
		for ($i = 0, $count = $this->getUnsignedVarInt(); $i < $count; ++$i) {
			//1.21.80 flattened the category groups into a plain category list
			if ($this->protocol >= ProtocolInfo::PROTOCOL_800) {
				$this->categories[] = CameraAimAssistCategory::read($this);
			} else {
				$this->categories[] = CameraAimAssistCategories::read($this);
			}
		}
		$this->presets = [];
		for ($i = 0, $count = $this->getUnsignedVarInt(); $i < $count; ++$i) {
			$this->presets[] = CameraAimAssistPreset::read($this);
		}

		if ($this->protocol >= ProtocolInfo::PROTOCOL_776) {
			$this->operation = CameraAimAssistPresetsPacketOperation::fromPacket($this->getByte());
		}
	}

	protected function encodePayload() : void
	{
		$this->putUnsignedVarInt(count($this->categories));
		foreach ($this->categories as $category) {
			$category->write($this);
		}
		$this->putUnsignedVarInt(count($this->presets));
		foreach ($this->presets as $preset) {
			$preset->write($this);
		}

		if ($this->protocol >= ProtocolInfo::PROTOCOL_776) {
			$this->putByte($this->operation->value);
		}
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleCameraAimAssistPresets($this);
	}
}
