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

namespace pocketmine\level\generator\vanilla\network\mcpe\convert;

use pocketmine\level\generator\vanilla\entity\Entity;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ProtocolInfo;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\level\generator\vanilla\utils\SingletonTrait;

use function decbin;
use function strlen;
use function strrev;

class EntityMetadataTranslator
{
	use SingletonTrait;

	public function fromNetworkIds(array $translateMetadata, int $protocolVersion) : array
	{
		$metadata = [];

		$properties = ConstantTranslator::getInstance()->fromNetworkIds(EntityMetadataProperties::class, $protocolVersion);
		foreach ($translateMetadata as $propertyId => $value) {
			if (isset($properties[$propertyId])) {
				$metadata[$properties[$propertyId]] = $value;
			}
		}

		$flags = $flags2 = null;

		if (isset($metadata[EntityMetadataProperties::DATA_FLAGS])) {
			$translateFlags = $metadata[EntityMetadataProperties::DATA_FLAGS][1];
			unset($metadata[EntityMetadataProperties::DATA_FLAGS]);

			$this->translateFlags(
				$translateFlags,
				ConstantTranslator::getInstance()->fromNetworkIds(EntityMetadataFlags::class, $protocolVersion),
				0,
				$flags,
				$flags2
			);
		}

		if (isset($metadata[EntityMetadataProperties::DATA_FLAGS2])) {
			$translateFlags2 = $metadata[EntityMetadataProperties::DATA_FLAGS2][1];
			unset($metadata[EntityMetadataProperties::DATA_FLAGS2]);

			$this->translateFlags(
				$translateFlags2,
				ConstantTranslator::getInstance()->fromNetworkIds(EntityMetadataFlags::class, $protocolVersion),
				64,
				$flags,
				$flags2
			);

			if ($translateFlags2 === 0 && $flags2 === null) {
				$flags2 = 0;
			}
		}

		if ($flags !== null) {
			$metadata[EntityMetadataProperties::DATA_FLAGS] = [Entity::DATA_TYPE_LONG, $flags];
		}

		if ($flags2 !== null) {
			$metadata[EntityMetadataProperties::DATA_FLAGS2] = [Entity::DATA_TYPE_LONG, $flags2];
		}

		if (
			$protocolVersion < ProtocolInfo::PROTOCOL_407 &&
			isset($metadata[EntityMetadataProperties::DATA_TARGET_EID]) &&
			$metadata[EntityMetadataProperties::DATA_TARGET_EID][1] === -1
		) {
			$metadata[EntityMetadataProperties::DATA_TARGET_EID][1] = 0;
		}

		return $metadata;
	}

	public function toNetworkIds(array $translateMetadata, int $protocolVersion) : array
	{
		$metadata = [];

		$flags = $flags2 = null;

		if (isset($translateMetadata[EntityMetadataProperties::DATA_FLAGS])) {
			$translateFlags = $translateMetadata[EntityMetadataProperties::DATA_FLAGS][1];
			unset($translateMetadata[EntityMetadataProperties::DATA_FLAGS]);

			$metadata[EntityMetadataProperties::DATA_ALWAYS_SHOW_NAMETAG] = [Entity::DATA_TYPE_BYTE, ($translateFlags & (1 << EntityMetadataFlags::DATA_FLAG_ALWAYS_SHOW_NAMETAG)) > 0 ? 1 : 0];

			$this->translateFlags(
				$translateFlags,
				ConstantTranslator::getInstance()->toNetworkIds(EntityMetadataFlags::class, $protocolVersion),
				0,
				$flags,
				$flags2
			);
		}

		if (isset($translateMetadata[EntityMetadataProperties::DATA_FLAGS2])) {
			$translateFlags2 = $translateMetadata[EntityMetadataProperties::DATA_FLAGS2][1];
			unset($translateMetadata[EntityMetadataProperties::DATA_FLAGS2]);

			$this->translateFlags(
				$translateFlags2,
				ConstantTranslator::getInstance()->toNetworkIds(EntityMetadataFlags::class, $protocolVersion),
				64,
				$flags,
				$flags2
			);

			if ($translateFlags2 === 0 && $flags2 === null) {
				$flags2 = 0;
			}
		}

		if ($flags !== null) {
			$translateMetadata[EntityMetadataProperties::DATA_FLAGS] = [Entity::DATA_TYPE_LONG, $flags];
		}

		if ($flags2 !== null) {
			$translateMetadata[EntityMetadataProperties::DATA_FLAGS2] = [Entity::DATA_TYPE_LONG, $flags2];
		}

		if (
			$protocolVersion < ProtocolInfo::PROTOCOL_407 &&
			isset($translateMetadata[EntityMetadataProperties::DATA_TARGET_EID]) &&
			$translateMetadata[EntityMetadataProperties::DATA_TARGET_EID][1] === 0
		) {
			$translateMetadata[EntityMetadataProperties::DATA_TARGET_EID][1] = -1;
		}

		$properties = ConstantTranslator::getInstance()->toNetworkIds(EntityMetadataProperties::class, $protocolVersion);
		foreach ($translateMetadata as $propertyId => $value) {
			if (isset($properties[$propertyId])) {
				$metadata[$properties[$propertyId]] = $value;
			}
		}

		return $metadata;
	}

	/*
	 * Two flags can be allowed here at once, but we are waiting for more than 128 flags
	 */
	private function translateFlags(
		int $translateFlags,
		array $upgradeFlags,
		int $bit,
		?int &$flags,
		?int &$flags2
	) : void {
		$flagsBinary = strrev(decbin($translateFlags));

		for ($i = 0, $len = strlen($flagsBinary); $i < $len; ++$i) {
			if ($flagsBinary[$i] === "1") {
				$flag = $upgradeFlags[$i + $bit] ?? null;
				if ($flag !== null) {
					if ($flag >= 64) {
						$flags2 |= (1 << ($flag % 64));
					} else {
						$flags |= (1 << $flag);
					}
				}
			}
		}
	}
}
