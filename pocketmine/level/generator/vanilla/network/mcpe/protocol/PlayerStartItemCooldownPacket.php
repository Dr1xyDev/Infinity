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

class PlayerStartItemCooldownPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::PLAYER_START_ITEM_COOLDOWN_PACKET;

	private string $itemCategory;
	private int $cooldownTicks;

	/**
	 * @generate-create-func
	 */
	public static function create(string $itemCategory, int $cooldownTicks) : self
	{
		$result = new self();
		$result->itemCategory = $itemCategory;
		$result->cooldownTicks = $cooldownTicks;
		return $result;
	}

	public function getItemCategory() : string
	{
		return $this->itemCategory;
	}

	public function getCooldownTicks() : int
	{
		return $this->cooldownTicks;
	}

	protected function decodePayload() : void
	{
		$this->itemCategory = $this->getString();
		$this->cooldownTicks = $this->getVarInt();
	}

	protected function encodePayload() : void
	{
		$this->putString($this->itemCategory);
		$this->putVarInt($this->cooldownTicks);
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handlePlayerStartItemCooldown($this);
	}
}
