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
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\hud\ServerboundLoadingScreenPacketType;

class ServerboundLoadingScreenPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::SERVERBOUND_LOADING_SCREEN_PACKET;

	private ServerboundLoadingScreenPacketType $loadingScreenType;
	private ?int $loadingScreenId = null;

	/**
	 * @generate-create-func
	 */
	public static function create(ServerboundLoadingScreenPacketType $loadingScreenType, ?int $loadingScreenId) : self
	{
		$result = new self();
		$result->loadingScreenType = $loadingScreenType;
		$result->loadingScreenId = $loadingScreenId;
		return $result;
	}

	public function getLoadingScreenType() : ServerboundLoadingScreenPacketType
	{
		return $this->loadingScreenType;
	}

	public function getLoadingScreenId() : ?int
	{
		return $this->loadingScreenId;
	}

	protected function decodePayload() : void
	{
		$this->loadingScreenType = ServerboundLoadingScreenPacketType::fromPacket($this->getVarInt());
		$this->loadingScreenId = $this->getOptional(fn () => $this->getLInt());
	}

	protected function encodePayload() : void
	{
		$this->putVarInt($this->loadingScreenType->value);
		$this->putOptional($this->loadingScreenId, $this->putLInt(...));
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleServerboundLoadingScreen($this);
	}
}
