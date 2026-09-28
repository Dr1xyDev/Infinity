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

/**
 * Tells 1.26.50+ clients that a record (music disc) started playing in a jukebox at the given position, linked to a
 * server sound handle (see PlaySoundPacket::$serverSoundHandle).
 */
class RecordStartedPacket extends DataPacket{
	public const NETWORK_ID = ProtocolInfo::RECORD_STARTED_PACKET;

	private int $x = 0;
	private int $y = 0;
	private int $z = 0;
	private int $serverSoundHandle;

	/**
	 * @generate-create-func
	 */
	public static function create(int $x, int $y, int $z, int $serverSoundHandle) : self{
		$result = new self();
		[$result->x, $result->y, $result->z] = [$x, $y, $z];
		$result->serverSoundHandle = $serverSoundHandle;
		return $result;
	}

	public function getX() : int{ return $this->x; }

	public function getY() : int{ return $this->y; }

	public function getZ() : int{ return $this->z; }

	public function getServerSoundHandle() : int{ return $this->serverSoundHandle; }

	protected function decodePayload() : void{
		$this->getBlockPosition($this->x, $this->y, $this->z);
		$this->serverSoundHandle = $this->getLLong();
	}

	protected function encodePayload() : void{
		$this->putBlockPosition($this->x, $this->y, $this->z);
		$this->putLLong($this->serverSoundHandle);
	}

	public function mustBeDecoded() : bool{
		return false;
	}

	public function handle(NetworkSession $session) : bool{
		return $session->handleRecordStarted($this);
	}
}
