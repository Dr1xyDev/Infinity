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

final class CameraFovInstruction
{
	/**
	 * @see CameraSetInstructionEaseType
	 */
	public function __construct(
		private float $fieldOfView,
		private float $easeTime,
		private int|string $easeType,
		private bool $clear,
	) {
	}

	public function getFieldOfView() : float
	{
		return $this->fieldOfView;
	}

	public function getEaseTime() : float
	{
		return $this->easeTime;
	}

	/**
	 * @see CameraSetInstructionEaseType
	 */
	public function getEaseType() : int
	{
		return is_int($this->easeType) ? $this->easeType : CameraSetInstructionEaseType::fromName($this->easeType);
	}

	/** Ease name, sent as a string since 1.26.10 */
	public function getEaseTypeName() : string
	{
		return is_int($this->easeType) ? CameraSetInstructionEaseType::toName($this->easeType) : $this->easeType;
	}

	public function getClear() : bool
	{
		return $this->clear;
	}

	public static function read(NetworkBinaryStream $in) : self
	{
		$fieldOfView = $in->getLFloat();
		$easeTime = $in->getLFloat();
		$easeType = $in->getProtocol() >= ProtocolInfo::PROTOCOL_944 ? $in->getString() : $in->getByte();
		$clear = $in->getBool();
		return new self(
			$fieldOfView,
			$easeTime,
			$easeType,
			$clear
		);
	}

	public function write(NetworkBinaryStream $out) : void
	{
		$out->putLFloat($this->fieldOfView);
		$out->putLFloat($this->easeTime);
		if ($out->getProtocol() >= ProtocolInfo::PROTOCOL_944) {
			$out->putString($this->getEaseTypeName());
		} else {
			$out->putByte($this->getEaseType());
		}
		$out->putBool($this->clear);
	}
}
