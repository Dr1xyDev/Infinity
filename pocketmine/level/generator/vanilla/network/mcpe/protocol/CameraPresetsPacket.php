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
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\camera\CameraPreset;

use function count;

class CameraPresetsPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::CAMERA_PRESETS_PACKET;

	/** @var CameraPreset[] */
	public array $presets = [];

	/** @phpstan-var CompoundTag */
	public CompoundTag $data; //old

	protected function decodePayload() : void
	{
		if ($this->protocol >= ProtocolInfo::PROTOCOL_618) {
			$this->presets = [];
			for ($i = 0, $count = $this->getUnsignedVarInt(); $i < $count; $i++) {
				$this->presets[] = CameraPreset::read($this);
			}
		} else {
			$this->data = $this->getNbtCompoundRoot();
		}
	}

	protected function encodePayload() : void
	{
		if ($this->protocol >= ProtocolInfo::PROTOCOL_618) {
			$this->putUnsignedVarInt(count($this->presets));
			foreach ($this->presets as $preset) {
				$preset->write($this);
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
		return $session->handleCameraPresets($this);
	}
}
