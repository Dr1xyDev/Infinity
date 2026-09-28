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
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\camera\CameraAimAssistActorPriorityData;
use function count;

class CameraAimAssistActorPriorityPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::CAMERA_AIM_ASSIST_ACTOR_PRIORITY_PACKET;

	/**
	 * @var CameraAimAssistActorPriorityData[]
	 * @phpstan-var list<CameraAimAssistActorPriorityData>
	 */
	private array $priorityData = [];

	/**
	 * @generate-create-func
	 * @param CameraAimAssistActorPriorityData[] $priorityData
	 * @phpstan-param list<CameraAimAssistActorPriorityData> $priorityData
	 */
	public static function create(array $priorityData) : self{
		$result = new self();
		$result->priorityData = $priorityData;
		return $result;
	}

	/**
	 * @return CameraAimAssistActorPriorityData[]
	 * @phpstan-return list<CameraAimAssistActorPriorityData>
	 */
	public function getPriorityData() : array{ return $this->priorityData; }

	protected function decodePayload() : void{
		$this->priorityData = [];
		for($i = 0, $count = $this->getUnsignedVarInt(); $i < $count; ++$i){
			$this->priorityData[] = CameraAimAssistActorPriorityData::read($this);
		}
	}

	protected function encodePayload() : void{
		$this->putUnsignedVarInt(count($this->priorityData));
		foreach($this->priorityData as $data){
			$data->write($this);
		}
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool{
		return $session->handleCameraAimAssistActorPriority($this);
	}
}
