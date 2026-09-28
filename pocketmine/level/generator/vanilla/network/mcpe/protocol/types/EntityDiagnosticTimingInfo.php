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

use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\network\mcpe\NetworkBinaryStream;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ProtocolInfo;

final class EntityDiagnosticTimingInfo{

	public function __construct(
		private string $displayName,
		private string $entity,
		private int $timeInNS,
		private int $percentOfTotal,
		private ?Vector3 $position = null, //since 1.26.50
		private string $dimension = "" //since 1.26.50
	){}

	public function getDisplayName() : string{ return $this->displayName; }

	public function getEntity() : string{ return $this->entity; }

	public function getTimeInNS() : int{ return $this->timeInNS; }

	public function getPercentOfTotal() : int{ return $this->percentOfTotal; }

	public function getPosition() : ?Vector3{ return $this->position; }

	public function getDimension() : string{ return $this->dimension; }

	public static function read(NetworkBinaryStream $in) : self{
		$displayName = $in->getString();
		$entity = $in->getString();
		$timeInNS = $in->getLLong();
		$percentOfTotal = $in->getByte();
		if ($in->getProtocol() >= ProtocolInfo::PROTOCOL_2193) {
			$position = $in->getVector3();
			$dimension = $in->getString();
		}

		return new self(
			$displayName,
			$entity,
			$timeInNS,
			$percentOfTotal,
			$position ?? null,
			$dimension ?? ""
		);
	}

	public function write(NetworkBinaryStream $out) : void{
		$out->putString($this->displayName);
		$out->putString($this->entity);
		$out->putLLong($this->timeInNS);
		$out->putByte($this->percentOfTotal);
		if ($out->getProtocol() >= ProtocolInfo::PROTOCOL_2193) {
			$out->putVector3Nullable($this->position);
			$out->putString($this->dimension);
		}
	}
}
