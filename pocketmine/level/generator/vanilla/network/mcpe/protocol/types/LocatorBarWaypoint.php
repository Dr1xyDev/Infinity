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

use pocketmine\level\generator\vanilla\math\Vector2;
use pocketmine\level\generator\vanilla\network\mcpe\NetworkBinaryStream;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ProtocolInfo;
use pocketmine\level\generator\vanilla\utils\Color;

final class LocatorBarWaypoint{

	public function __construct(
		public int            $updateFlag,
		public ?bool          $visible,
		public ?WorldPosition $worldPosition,
		public ?int           $textureId,
		public ?string        $texturePath,
		public ?Vector2       $iconSize,
		public ?Color         $color,
		public ?bool          $clientPositionAuthority,
		public ?int           $actorUniqueId
	){}

	public static function read(NetworkBinaryStream $in) : self{
		$updateFlag = $in->getLInt();
		$visible = $in->getOptional($in->getBool(...));
		$worldPosition = $in->getOptional(fn () => WorldPosition::read($in));
		if ($in->getProtocol() >= ProtocolInfo::PROTOCOL_975) {
			$texturePath = $in->getOptional($in->getString(...));
			$iconSize = $in->getOptional($in->getVector2(...));
		} else {
			$textureId = $in->getOptional($in->getLInt(...));
		}

		$color = $in->getOptional(fn() => Color::fromARGB($in->getLInt()));
		$clientPositionAuthority = $in->getOptional($in->getBool(...));
		$actorUniqueId = $in->getOptional($in->getEntityRuntimeId(...));

		return new self(
			$updateFlag,
			$visible,
			$worldPosition,
			$textureId ?? null,
			$texturePath ?? null,
			$iconSize ?? null,
			$color,
			$clientPositionAuthority,
			$actorUniqueId,
		);
	}

	public function write(NetworkBinaryStream $out) : void{
		$out->putLInt($this->updateFlag);
		$out->putOptional($this->visible, $out->putBool(...));
		$out->putOptional($this->worldPosition, fn (WorldPosition $v) => $v->write($out));
		if ($out->getProtocol() >= ProtocolInfo::PROTOCOL_975) {
			$out->putOptional($this->texturePath, $out->putString(...));
			$out->putOptional($this->iconSize, $out->putVector2(...));
		} else {
			$out->putOptional($this->textureId, $out->putLInt(...));
		}

		$out->putOptional($this->color, fn(Color $v) => $out->putLInt($v->toARGB()));
		$out->putOptional($this->clientPositionAuthority, $out->putBool(...));
		$out->putOptional($this->actorUniqueId, $out->putEntityRuntimeId(...));
	}
}
