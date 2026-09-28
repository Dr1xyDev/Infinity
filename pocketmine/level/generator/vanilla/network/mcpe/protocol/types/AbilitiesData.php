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

use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\command\CommandPermissions;
use function count;

final class AbilitiesData
{
	/**
	 * @param AbilitiesLayer[] $abilityLayers
	 * @phpstan-param array<int, AbilitiesLayer> $abilityLayers
	 */
	public function __construct(
		private CommandPermissions $commandPermission,
		private int $playerPermission,
		private int $targetActorUniqueId, //This is a little-endian long, NOT a var-long. (WTF Mojang)
		private array $abilityLayers
	) {
	}

	public function getCommandPermission() : CommandPermissions
	{
		return $this->commandPermission;
	}

	public function getPlayerPermission() : int
	{
		return $this->playerPermission;
	}

	public function getTargetActorUniqueId() : int
	{
		return $this->targetActorUniqueId;
	}

	/**
	 * @return AbilitiesLayer[]
	 * @phpstan-return array<int, AbilitiesLayer>
	 */
	public function getAbilityLayers() : array
	{
		return $this->abilityLayers;
	}

	public static function decode(NetworkBinaryStream $in) : self
	{
		$targetActorUniqueId = $in->getLLong(); //WHY IS THIS NON-STANDARD?
		$playerPermission = $in->getByte();
		$commandPermission = CommandPermissions::fromPacket($in->getByte());

		$abilityLayers = [];
		for ($i = 0, $len = $in->getByte(); $i < $len; $i++) {
			$abilityLayers[] = AbilitiesLayer::decode($in);
		}

		return new self($commandPermission, $playerPermission, $targetActorUniqueId, $abilityLayers);
	}

	public function encode(NetworkBinaryStream $out) : void
	{
		$out->putLLong($this->targetActorUniqueId);
		$out->putByte($this->playerPermission);
		$out->putByte($this->commandPermission->value);

		$out->putByte(count($this->abilityLayers));
		foreach ($this->abilityLayers as $abilityLayer) {
			$abilityLayer->encode($out);
		}
	}
}
