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

use pocketmine\level\generator\vanilla\network\mcpe\protocol\ProtocolInfo;
use function count;

final class AttributeLayer{

	/**
	 * @param AttributeEnvironment[] $environmentAttributes
	 * @phpstan-param list<AttributeEnvironment> $environmentAttributes
	 */
	public function __construct(
		public string $name,
		public ?string $noiseName,
		public int $dimensionId,
		public AttributeLayerSettings $settings,
		public array $environmentAttributes
	){}

	public static function read(NetworkBinaryStream $in) : self{
		$name = $in->getString();
		if ($in->getProtocol() >= ProtocolInfo::PROTOCOL_1001) {
			$noiseName = $in->getOptional($in->getString(...));
		}
		$dimensionId = $in->getVarInt();
		$settings = AttributeLayerSettings::read($in);

		$environmentAttributes = [];
		for($i = 0, $count = $in->getUnsignedVarInt(); $i < $count; ++$i){
			$environmentAttributes[] = AttributeEnvironment::read($in);
		}

		return new self($name, $noiseName ?? "", $dimensionId, $settings, $environmentAttributes);
	}

	public function write(NetworkBinaryStream $out) : void{
		$out->putString($this->name);
		if ($out->getProtocol() >= ProtocolInfo::PROTOCOL_1001) {
			$out->putOptional($this->noiseName, $out->putString(...));
		}
		$out->putVarInt($this->dimensionId);
		$this->settings->write($out);
		$out->putUnsignedVarInt(count($this->environmentAttributes));
		foreach($this->environmentAttributes as $environmentAttribute){
			$environmentAttribute->write($out);
		}
	}
}
