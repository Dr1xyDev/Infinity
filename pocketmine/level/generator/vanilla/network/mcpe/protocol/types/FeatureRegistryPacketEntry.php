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

final class FeatureRegistryPacketEntry
{
	public function __construct(
		private string $featureName,
		private string $featureJson
	) {
	}

	public function getFeatureName() : string
	{
		return $this->featureName;
	}

	public function getFeatureJson() : string
	{
		return $this->featureJson;
	}

	public static function read(NetworkBinaryStream $in) : self
	{
		$featureName = $in->getString();
		$featureJson = $in->getString();

		return new self($featureName, $featureJson);
	}

	public function write(NetworkBinaryStream $out) : void
	{
		$out->putString($this->featureName);
		$out->putString($this->featureJson);
	}
}
