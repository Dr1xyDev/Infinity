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

class AnvilDamagePacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::ANVIL_DAMAGE_PACKET;

	public int $x = 0;
	public int $y = 0;
	public int $z = 0;
	public int $damageAmount;

	public static function create(int $x, int $y, int $z, int $damageAmount) : self
	{
		$result = new self();
		[$result->x, $result->y, $result->z] = [$x, $y, $z];
		$result->damageAmount = $damageAmount;
		return $result;
	}

	public function getDamageAmount() : int
	{
		return $this->damageAmount;
	}

	public function getX() : int
	{
		return $this->x;
	}

	public function getY() : int
	{
		return $this->y;
	}

	public function getZ() : int
	{
		return $this->z;
	}

	protected function decodePayload() : void
	{
		if ($this->protocol >= ProtocolInfo::PROTOCOL_2168) {
			$this->damageAmount = 0; //removed in 1.26.40, the server decides the damage
		} else {
			$this->damageAmount = $this->getByte();
		}
		$this->getBlockPosition($this->x, $this->y, $this->z);
	}

	protected function encodePayload() : void
	{
		if ($this->protocol < ProtocolInfo::PROTOCOL_2168) {
			$this->putByte($this->damageAmount);
		}
		$this->putBlockPosition($this->x, $this->y, $this->z);
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleAnvilDamage($this);
	}
}
