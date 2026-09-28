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
use function count;

final class CameraAimAssistPresetExclusionDefinition{

	/**
	 * @param string[] $blocks
	 * @param string[] $entities
	 * @param string[] $blockTags
	 * @param string[] $entityTypeFamilies
	 */
	public function __construct(
		private array $blocks,
		private array $entities,
		private array $blockTags,
		private array $entityTypeFamilies
	){}

	/**
	 * @return string[]
	 */
	public function getBlocks() : array{ return $this->blocks; }

	/**
	 * @return string[]
	 */
	public function getEntities() : array{ return $this->entities; }

	/**
	 * @return string[]
	 */
	public function getBlockTags() : array{ return $this->blockTags; }

	/**
	 * @return string[]
	 */
	public function getEntityTypeFamilies() : array{ return $this->entityTypeFamilies; }

	public static function read(NetworkBinaryStream $in) : self{
		$blocks = [];
		for($i = 0, $len = $in->getUnsignedVarInt(); $i < $len; ++$i){
			$blocks[] = $in->getString();
		}

		$entities = [];
		for($i = 0, $len = $in->getUnsignedVarInt(); $i < $len; ++$i){
			$entities[] = $in->getString();
		}

		$blockTags = [];
		for($i = 0, $len = $in->getUnsignedVarInt(); $i < $len; ++$i){
			$blockTags[] = $in->getString();
		}

		$entityTypeFamilies = [];
		if ($in->getProtocol() >= ProtocolInfo::PROTOCOL_924) {
			for ($i = 0, $len = $in->getUnsignedVarInt(); $i < $len; ++$i) {
				$entityTypeFamilies[] = $in->getString();
			}
		}

		return new self(
			$blocks,
			$entities,
			$blockTags,
			$entityTypeFamilies
		);
	}

	public function write(NetworkBinaryStream $out) : void{
		$out->putUnsignedVarInt(count($this->blocks));
		foreach($this->blocks as $block){
			$out->putString($block);
		}

		$out->putUnsignedVarInt(count($this->entities));
		foreach($this->entities as $entity){
			$out->putString($entity);
		}

		$out->putUnsignedVarInt(count($this->blockTags));
		foreach($this->blockTags as $blockTag){
			$out->putString($blockTag);
		}

		if ($out->getProtocol() >= ProtocolInfo::PROTOCOL_924) {
			$out->putUnsignedVarInt(count($this->entityTypeFamilies));
			foreach($this->entityTypeFamilies as $entityTypeFamily){
				$out->putString($entityTypeFamily);
			}
		}
	}
}
