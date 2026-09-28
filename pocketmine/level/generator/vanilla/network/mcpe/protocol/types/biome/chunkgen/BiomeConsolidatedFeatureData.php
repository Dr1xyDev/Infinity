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

namespace pocketmine\level\generator\vanilla\network\mcpe\protocol\types\biome\chunkgen;

use pocketmine\level\generator\vanilla\network\mcpe\NetworkBinaryStream;

final class BiomeConsolidatedFeatureData
{
	public function __construct(
		private BiomeScatterParamData $scatter,
		private int $feature,
		private int $identifier,
		private int $pass,
		private bool $useInternal
	) {
	}

	public function getScatter() : BiomeScatterParamData
	{
		return $this->scatter;
	}

	public function getFeature() : int
	{
		return $this->feature;
	}

	public function getIdentifier() : int
	{
		return $this->identifier;
	}

	public function getPass() : int
	{
		return $this->pass;
	}

	public function canUseInternal() : bool
	{
		return $this->useInternal;
	}

	public static function read(NetworkBinaryStream $in) : self
	{
		$scatter = BiomeScatterParamData::read($in);
		$feature = $in->getLShort();
		$identifier = $in->getLShort();
		$pass = $in->getUnsignedVarInt();
		$useInternal = $in->getBool();

		return new self(
			$scatter,
			$feature,
			$identifier,
			$pass,
			$useInternal
		);
	}

	public function write(NetworkBinaryStream $out) : void
	{
		$this->scatter->write($out);
		$out->putLShort($this->feature);
		$out->putLShort($this->identifier);
		$out->putUnsignedVarInt($this->pass);
		$out->putBool($this->useInternal);
	}
}
