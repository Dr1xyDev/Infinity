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

namespace pocketmine\level\generator\vanilla\network\mcpe\protocol\types;

use pocketmine\level\generator\vanilla\network\mcpe\NetworkBinaryStream;

use function count;

final class WorldClockData{

	/**
	 * @param TimeMarkerData[] $timeMarkers
	 * @phpstan-param list<TimeMarkerData> $timeMarkers
	 */
	public function __construct(
		public int $id,
		public string $name,
		public int $time,
		public bool $paused,
		public array $timeMarkers
	){}

	public static function read(NetworkBinaryStream $in) : self{
		$id = $in->getUnsignedVarLong();
		$name = $in->getString();
		$time = $in->getVarInt();
		$paused = $in->getBool();

		$timeMarkers = [];
		for($i = 0, $count = $in->getUnsignedVarInt(); $i < $count; ++$i){
			$timeMarkers[] = TimeMarkerData::read($in);
		}

		return new self($id, $name, $time, $paused, $timeMarkers);
	}

	public function write(NetworkBinaryStream $out) : void{
		$out->putUnsignedVarLong($this->id);
		$out->putString($this->name);
		$out->putVarInt($this->time);
		$out->putBool($this->paused);
		$out->putUnsignedVarInt(count($this->timeMarkers));
		foreach($this->timeMarkers as $timeMarker){
			$timeMarker->write($out);
		}
	}
}
