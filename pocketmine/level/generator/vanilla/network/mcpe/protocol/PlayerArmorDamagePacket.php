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

namespace pocketmine\level\generator\vanilla\network\mcpe\protocol;

use pocketmine\level\generator\vanilla\network\mcpe\NetworkSession;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\ArmorSlot;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\ArmorSlotAndDamagePair;
use function count;

class PlayerArmorDamagePacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::PLAYER_ARMOR_DAMAGE_PACKET;

	/**
	 * @var ArmorSlotAndDamagePair[]
	 * @phpstan-var list<ArmorSlotAndDamagePair>
	 */
	private array $armorSlotAndDamagePairs = [];

	/**
	 * @generate-create-func
	 * @param ArmorSlotAndDamagePair[] $armorSlotAndDamagePairs
	 * @phpstan-param list<ArmorSlotAndDamagePair> $armorSlotAndDamagePairs
	 */
	public static function create(array $armorSlotAndDamagePairs) : self
	{
		$result = new self();
		$result->armorSlotAndDamagePairs = $armorSlotAndDamagePairs;
		return $result;
	}

	/**
	 * @return ArmorSlotAndDamagePair[]
	 * @phpstan-return list<ArmorSlotAndDamagePair>
	 */
	public function getArmorSlotAndDamagePairs() : array{
		return $this->armorSlotAndDamagePairs;
	}

	protected function decodePayload() : void
	{
		if ($this->protocol >= ProtocolInfo::PROTOCOL_844) {
			for ($i = 0, $count = $this->getUnsignedVarInt(); $i < $count; ++$i) {
				$this->armorSlotAndDamagePairs[] = ArmorSlotAndDamagePair::read($this);
			}
		} else {
			$flags = $this->getByte();
			$armorSlotAndDamagePairs = [];
			foreach (ArmorSlot::cases() as $armorSlot) {
				if (($flags & (1 << $armorSlot->value)) !== 0) {
					$armorSlotAndDamagePairs[] = new ArmorSlotAndDamagePair($armorSlot, $this->getVarInt());
				}
			}

			$this->armorSlotAndDamagePairs = $armorSlotAndDamagePairs;
		}
	}

	protected function encodePayload() : void
	{
		if ($this->protocol >= ProtocolInfo::PROTOCOL_844) {
			$this->putUnsignedVarInt(count($this->armorSlotAndDamagePairs));
			foreach ($this->armorSlotAndDamagePairs as $pair) {
				$pair->write($this);
			}
		} else {
			//the client reads the damages in slot order, one per flag bit
			$damages = [];
			foreach ($this->armorSlotAndDamagePairs as $pair) {
				if ($this->protocol < ProtocolInfo::PROTOCOL_712 && $pair->getSlot() === ArmorSlot::BODY) {
					continue;
				}
				$damages[$pair->getSlot()->value] = $pair->getDamage();
			}
			ksort($damages);

			$flags = 0;
			foreach ($damages as $slot => $_) {
				$flags |= (1 << $slot);
			}
			$this->putByte($flags);

			foreach ($damages as $damage) {
				$this->putVarInt($damage);
			}
		}
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handlePlayerArmorDamage($this);
	}
}
