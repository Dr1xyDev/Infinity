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

final class AttributeEnvironment{

	public function __construct(
		public string $attributeName,
		public ?AttributeData $fromAttribute,
		public AttributeData $attribute,
		public ?AttributeData $toAttribute,
		public int $currentTransitionTicks,
		public int $totalTransitionTicks,
		public string $easeType,
		public int $localTransitionTicks,
		public bool $noiseTransition,
		public int $noiseAlignmentType = 0, //since 1.26.50 (0 = MIN_LOCAL_TRANSITION_END)
		public int $noiseAlignmentValue = 0 //since 1.26.50
	){}

	public static function read(NetworkBinaryStream $in) : self{
		$attributeName = $in->getString();
		$fromAttribute = $in->getOptional(fn () => AttributeData::read($in));
		$attribute = AttributeData::read($in);
		$toAttribute = $in->getOptional(fn () => AttributeData::read($in));
		$currentTransitionTicks = $in->getLInt();
		$totalTransitionTicks = $in->getLInt();
		$easeType = $in->getString();
		if ($in->getProtocol() >= ProtocolInfo::PROTOCOL_1001) {
			$localTransitionTicks = $in->getLInt();
			$noiseTransition = $in->getBool();
			if ($in->getProtocol() >= ProtocolInfo::PROTOCOL_2193) {
				$noiseAlignmentType = $in->getByte();
				$noiseAlignmentValue = $in->getUnsignedVarInt();
			}
		}

		return new self(
			$attributeName,
			$fromAttribute,
			$attribute,
			$toAttribute,
			$currentTransitionTicks,
			$totalTransitionTicks,
			$easeType,
			$localTransitionTicks ?? 0,
			$noiseTransition ?? false,
			$noiseAlignmentType ?? 0,
			$noiseAlignmentValue ?? 0
		);
	}

	public function write(NetworkBinaryStream $out) : void{
		$out->putString($this->attributeName);
		$out->putOptional($this->fromAttribute, fn (AttributeData $v) => $v->write($out));
		$this->attribute->write($out);
		$out->putOptional($this->toAttribute, fn (AttributeData $v) => $v->write($out));
		$out->putLInt($this->currentTransitionTicks);
		$out->putLInt($this->totalTransitionTicks);
		$out->putString($this->easeType);
		if ($out->getProtocol() >= ProtocolInfo::PROTOCOL_1001) {
			$out->putLInt($this->localTransitionTicks);
			$out->putBool($this->noiseTransition);
			if ($out->getProtocol() >= ProtocolInfo::PROTOCOL_2193) {
				$out->putByte($this->noiseAlignmentType);
				$out->putUnsignedVarInt($this->noiseAlignmentValue);
			}
		}
	}
}
