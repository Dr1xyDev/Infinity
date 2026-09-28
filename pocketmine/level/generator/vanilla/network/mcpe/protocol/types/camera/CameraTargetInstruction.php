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

namespace pocketmine\level\generator\vanilla\network\mcpe\protocol\types\camera;

use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\network\mcpe\NetworkBinaryStream;

final class CameraTargetInstruction
{
	public function __construct(
		private ?Vector3 $targetCenterOffset,
		private int $actorUniqueId
	) {
	}

	public function getTargetCenterOffset() : ?Vector3
	{
		return $this->targetCenterOffset;
	}

	public function getActorUniqueId() : int
	{
		return $this->actorUniqueId;
	}

	public static function read(NetworkBinaryStream $in) : self
	{
		$targetCenterOffset = $in->getOptional(fn () => $in->getVector3());
		$actorUniqueId = $in->getLLong();
		return new self(
			$targetCenterOffset,
			$actorUniqueId
		);
	}

	public function write(NetworkBinaryStream $out) : void
	{
		$out->putOptional($this->targetCenterOffset, fn (Vector3 $v) => $out->putVector3($v));
		$out->putLLong($this->actorUniqueId);
	}
}
