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

use pocketmine\level\generator\vanilla\network\mcpe\NetworkBinaryStream;

final class CameraAimAssistActorPriorityData{

	public function __construct(
		private int $presetIndex,
		private int $categoryIndex,
		private int $actorIndex,
		private int $priority,
	){}

	public function getPresetIndex() : int { return $this->presetIndex; }

	public function getCategoryIndex() : int { return $this->categoryIndex; }

	public function getActorIndex() : int { return $this->actorIndex; }

	public function getPriority() : int { return $this->priority; }

	public static function read(NetworkBinaryStream $in) : self{
		$presetIndex = $in->getLInt();
		$categoryIndex = $in->getLInt();
		$actorIndex = $in->getLInt();
		$priority = $in->getLInt();
		return new self($presetIndex, $categoryIndex, $actorIndex, $priority);
	}

	public function write(NetworkBinaryStream $out) : void{
		$out->putLInt($this->presetIndex);
		$out->putLInt($this->categoryIndex);
		$out->putLInt($this->actorIndex);
		$out->putLInt($this->priority);
	}
}
