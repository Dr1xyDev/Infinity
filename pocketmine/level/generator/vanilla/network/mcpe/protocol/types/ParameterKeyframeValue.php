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

final class ParameterKeyframeValue{

	public function __construct(
		private float $time,
		private Vector3 $value,
	){}

	public function getTime() : float{ return $this->time; }

	public function getValue() : Vector3{ return $this->value; }

	public static function read(NetworkBinaryStream $in) : self{
		$time = $in->getLFloat();
		$value = $in->getVector3();

		return new self(
			$time,
			$value
		);
	}

	public function write(NetworkBinaryStream $out) : void{
		$out->putLFloat($this->time);
		$out->putVector3($this->value);
	}
}
