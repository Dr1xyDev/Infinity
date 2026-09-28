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

use pocketmine\level\generator\vanilla\nbt\NetworkLittleEndianNBTStream;
use pocketmine\level\generator\vanilla\nbt\tag\CompoundTag;
use pocketmine\level\generator\vanilla\network\mcpe\NetworkSession;

/**
 * Unclear purpose, not used in vanilla Bedrock. Seems to be related to a new Minecraft "editor" edition or mode.
 */
class EditorNetworkPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::EDITOR_NETWORK_PACKET;

	private bool $isRouteToManager;
	/** @phpstan-var CompoundTag */
	private CompoundTag $payload;

	/**
	 * @generate-create-func
	 * @phpstan-param CompoundTag $payload
	 */
	public static function create(bool $isRouteToManager, CompoundTag $payload) : self
	{
		$result = new self();
		$result->isRouteToManager = $isRouteToManager;
		$result->payload = $payload;
		return $result;
	}

	/** @phpstan-return CompoundTag */
	public function getPayload() : CompoundTag
	{
		return $this->payload;
	}

	public function isRouteToManager() : bool
	{
		return $this->isRouteToManager;
	}

	protected function decodePayload() : void
	{
		if ($this->protocol >= ProtocolInfo::PROTOCOL_712) {
			$this->isRouteToManager = $this->getBool();
		}
		$this->payload = $this->getNbtCompoundRoot();
	}

	protected function encodePayload() : void
	{
		if ($this->protocol >= ProtocolInfo::PROTOCOL_712) {
			$this->putBool($this->isRouteToManager);
		}
		$this->put((new NetworkLittleEndianNBTStream())->write($this->payload));
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleEditorNetwork($this);
	}
}
