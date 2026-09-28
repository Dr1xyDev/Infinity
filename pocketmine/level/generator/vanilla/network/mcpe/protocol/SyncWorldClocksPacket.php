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

use InvalidArgumentException;
use pocketmine\level\generator\vanilla\network\mcpe\NetworkSession;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\ClockPayloadType;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\SyncWorldClockStateData;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\TimeMarkerData;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\WorldClockData;

use function count;

class SyncWorldClocksPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::SYNC_WORLD_CLOCKS_PACKET;

	public int $payloadType = ClockPayloadType::SYNC_STATE;
	/**
	 * @var SyncWorldClockStateData[]
	 * @phpstan-var list<SyncWorldClockStateData>
	 */
	public array $syncStates = [];
	/**
	 * @var WorldClockData[]
	 * @phpstan-var list<WorldClockData>
	 */
	public array $clocks = [];
	public int $addClockId = 0;
	/**
	 * @var TimeMarkerData[]
	 * @phpstan-var list<TimeMarkerData>
	 */
	public array $addTimeMarkers = [];
	public int $removeClockId = 0;
	/**
	 * @var int[]
	 * @phpstan-var list<int>
	 */
	public array $removeTimeMarkerIds = [];

	protected function decodePayload() : void{
		$this->payloadType = $this->getUnsignedVarInt();
		$this->syncStates = [];
		$this->clocks = [];
		$this->addClockId = 0;
		$this->addTimeMarkers = [];
		$this->removeClockId = 0;
		$this->removeTimeMarkerIds = [];

		switch($this->payloadType){
			case ClockPayloadType::SYNC_STATE:
				for($i = 0, $count = $this->getUnsignedVarInt(); $i < $count; ++$i){
					$this->syncStates[] = SyncWorldClockStateData::read($this);
				}
				break;
			case ClockPayloadType::INITIALIZE_REGISTRY:
				for($i = 0, $count = $this->getUnsignedVarInt(); $i < $count; ++$i){
					$this->clocks[] = WorldClockData::read($this);
				}
				break;
			case ClockPayloadType::ADD_TIME_MARKER:
				$this->addClockId = $this->getUnsignedVarLong();
				for($i = 0, $count = $this->getUnsignedVarInt(); $i < $count; ++$i){
					$this->addTimeMarkers[] = TimeMarkerData::read($this);
				}
				break;
			case ClockPayloadType::REMOVE_TIME_MARKER:
				$this->removeClockId = $this->getUnsignedVarLong();
				for($i = 0, $count = $this->getUnsignedVarInt(); $i < $count; ++$i){
					$this->removeTimeMarkerIds[] = $this->getUnsignedVarLong();
				}
				break;
			default:
				throw new PacketDecodeException("Unknown world clock payload type $this->payloadType");
		}
	}

	protected function encodePayload() : void{
		$this->putUnsignedVarInt($this->payloadType);
		switch($this->payloadType){
			case ClockPayloadType::SYNC_STATE:
				$this->putUnsignedVarInt(count($this->syncStates));
				foreach($this->syncStates as $syncState){
					$syncState->write($this);
				}
				break;
			case ClockPayloadType::INITIALIZE_REGISTRY:
				$this->putUnsignedVarInt(count($this->clocks));
				foreach($this->clocks as $clock){
					$clock->write($this);
				}
				break;
			case ClockPayloadType::ADD_TIME_MARKER:
				$this->putUnsignedVarLong($this->addClockId);
				$this->putUnsignedVarInt(count($this->addTimeMarkers));
				foreach($this->addTimeMarkers as $timeMarker){
					$timeMarker->write($this);
				}
				break;
			case ClockPayloadType::REMOVE_TIME_MARKER:
				$this->putUnsignedVarLong($this->removeClockId);
				$this->putUnsignedVarInt(count($this->removeTimeMarkerIds));
				foreach($this->removeTimeMarkerIds as $timeMarkerId){
					$this->putUnsignedVarLong($timeMarkerId);
				}
				break;
			default:
				throw new InvalidArgumentException("Unknown world clock payload type $this->payloadType");
		}
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleSyncWorldClocks($this);
	}
}
