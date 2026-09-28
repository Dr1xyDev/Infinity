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
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ProtocolInfo;
use function is_int;

final class CameraProgressOption{

	/**
	 * @param int|string $easeType CameraSetInstructionEaseType constant, or its name (sent as a string since 1.26.10)
	 * @see CameraSetInstructionEaseType
	 */
	public function __construct(
		private float $value,
		private float $time,
		private int|string $easeType,
	){}

	public function getValue() : float{ return $this->value; }

	public function getTime() : float{ return $this->time; }

	/**
	 * @see CameraSetInstructionEaseType
	 */
	public function getEaseType() : int{
		return is_int($this->easeType) ? $this->easeType : CameraSetInstructionEaseType::fromName($this->easeType);
	}

	public function getEaseTypeName() : string{
		return is_int($this->easeType) ? CameraSetInstructionEaseType::toName($this->easeType) : $this->easeType;
	}

	public static function read(NetworkBinaryStream $in) : self{
		$value = $in->getLFloat();
		$time = $in->getLFloat();
		$easeType = $in->getProtocol() >= ProtocolInfo::PROTOCOL_944 ? $in->getString() : $in->getLInt();

		return new self(
			$value,
			$time,
			$easeType
		);
	}

	public function write(NetworkBinaryStream $out) : void{
		$out->putLFloat($this->value);
		$out->putLFloat($this->time);
		if ($out->getProtocol() >= ProtocolInfo::PROTOCOL_944) {
			$out->putString($this->getEaseTypeName());
		} else {
			$out->putLInt($this->getEaseType());
		}
	}
}
