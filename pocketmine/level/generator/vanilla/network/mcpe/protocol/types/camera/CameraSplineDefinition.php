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

final class CameraSplineDefinition{

	public function __construct(
		private string $name,
		private CameraSplineInstruction $instruction,
	){}

	public function getName() : string { return $this->name; }

	public function getInstruction() : CameraSplineInstruction { return $this->instruction; }

	public static function read(NetworkBinaryStream $in) : self{
		$name = $in->getString();
		$instruction = CameraSplineInstruction::read($in);
		return new self($name, $instruction);
	}

	public function write(NetworkBinaryStream $out) : void{
		$out->putString($this->name);
		$this->instruction->write($out);
	}
}
