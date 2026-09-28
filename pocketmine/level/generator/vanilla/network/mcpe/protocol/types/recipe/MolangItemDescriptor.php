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

namespace pocketmine\level\generator\vanilla\network\mcpe\protocol\types\recipe;

use pocketmine\level\generator\vanilla\network\mcpe\NetworkBinaryStream;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ProtocolInfo;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\GetTypeIdFromConstTrait;

final class MolangItemDescriptor implements ItemDescriptor{
	use GetTypeIdFromConstTrait;

	public const ID = ItemDescriptorType::MOLANG;

	public function __construct(
		private string $molangExpression,
		private int $molangVersion
	){}

	public function getMolangExpression() : string{ return $this->molangExpression; }

	public function getMolangVersion() : int{ return $this->molangVersion; }

	public static function read(NetworkBinaryStream $in) : self{
		$expression = $in->getString();
		$version = $in->getProtocol() >= ProtocolInfo::PROTOCOL_2168 ? $in->getLShort() : $in->getByte();

		return new self($expression, $version);
	}

	public function write(NetworkBinaryStream $out) : void{
		$out->putString($this->molangExpression);
		if($out->getProtocol() >= ProtocolInfo::PROTOCOL_2168){
			$out->putLShort($this->molangVersion);
		}else{
			$out->putByte($this->molangVersion);
		}
	}
}
