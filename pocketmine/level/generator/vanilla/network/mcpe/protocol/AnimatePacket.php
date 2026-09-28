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

class AnimatePacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::ANIMATE_PACKET;

	public const ACTION_SWING_ARM = 1;

	public const ACTION_STOP_SLEEP = 3;
	public const ACTION_CRITICAL_HIT = 4;
	public const ACTION_ROW_RIGHT = 128;
	public const ACTION_ROW_LEFT = 129;

	public int $action;
	public int $actorRuntimeId;
	public float $data = 0.0;
	public float $rowingTime = 0.0;
	public ?string $swingSource = null;

	public static function create(int $actorRuntimeId, int $action, float $data = 0.0, ?string $swingSource = null) : self{
		$result = new self();
		$result->actorRuntimeId = $actorRuntimeId;
		$result->action = $action;
		$result->data = $data;
		$result->swingSource = $swingSource;
		return $result;
	}

	public static function boatHack(int $actorRuntimeId, int $actionId, float $rowingTime) : self{
		if($actionId !== self::ACTION_ROW_LEFT && $actionId !== self::ACTION_ROW_RIGHT){
			throw new \InvalidArgumentException("Invalid actionId for boatHack: $actionId");
		}

		$result = self::create($actorRuntimeId, $actionId);
		$result->rowingTime = $rowingTime;
		return $result;
	}

	protected function decodePayload() : void
	{
		if ($this->protocol >= ProtocolInfo::PROTOCOL_897) {
			$this->action = $this->getByte();
		} else {
			$this->action = $this->getVarInt();
		}

		$this->actorRuntimeId = $this->getEntityRuntimeId();
		if ($this->protocol >= ProtocolInfo::PROTOCOL_859) {
			$this->data = $this->getLFloat();
		}

		if ($this->protocol >= ProtocolInfo::PROTOCOL_897) {
			$this->swingSource = $this->getOptional($this->getString(...));
		} else {
			if ($this->action === self::ACTION_ROW_LEFT || $this->action === self::ACTION_ROW_RIGHT) {
				$this->rowingTime = $this->getLFloat();
			}
		}
	}

	protected function encodePayload() : void
	{
		if ($this->protocol >= ProtocolInfo::PROTOCOL_897) {
			$this->putByte($this->action);
		} else {
			$this->putVarInt($this->action);
		}

		$this->putEntityRuntimeId($this->actorRuntimeId);
		if ($this->protocol >= ProtocolInfo::PROTOCOL_859) {
			$this->putLFloat($this->data);
		}

		if ($this->protocol >= ProtocolInfo::PROTOCOL_897) {
			$this->putOptional($this->swingSource, $this->putString(...));
		} else {
			if ($this->action === self::ACTION_ROW_LEFT || $this->action === self::ACTION_ROW_RIGHT) {
				$this->putLFloat($this->rowingTime);
			}
		}
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleAnimate($this);
	}
}
