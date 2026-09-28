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
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\LocatorBarWaypointPayload;

use function count;

class LocatorBarPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::LOCATOR_BAR_PACKET;

	/**
	 * @var LocatorBarWaypointPayload[]
	 * @phpstan-var list<LocatorBarWaypointPayload>
	 */
	public array $waypoints = [];

	/**
	 * @generate-create-func
	 * @param LocatorBarWaypointPayload[] $waypoints
	 * @phpstan-param list<LocatorBarWaypointPayload> $waypoints
	 */
	public static function create(array $waypoints) : self{
		$result = new self();
		$result->waypoints = $waypoints;
		return $result;
	}

	protected function decodePayload() : void{
		$this->waypoints = [];
		for($i = 0, $count = $this->getUnsignedVarInt(); $i < $count; ++$i){
			$this->waypoints[] = LocatorBarWaypointPayload::read($this);
		}
	}

	protected function encodePayload() : void{
		$this->putUnsignedVarInt(count($this->waypoints));
		foreach($this->waypoints as $waypoint){
			$waypoint->write($this);
		}
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleLocatorBar($this);
	}
}
