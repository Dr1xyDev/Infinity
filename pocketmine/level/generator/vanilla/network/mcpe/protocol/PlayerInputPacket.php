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

class PlayerInputPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::PLAYER_INPUT_PACKET;

	/** @var float */
	public $motionX;
	/** @var float */
	public $motionY;
	/** @var bool */
	public $jumping;
	/** @var bool */
	public $sneaking;

	protected function decodePayload() : void
	{
		$this->motionX = $this->getLFloat();
		$this->motionY = $this->getLFloat();
		$this->jumping = $this->getBool();
		$this->sneaking = $this->getBool();
	}

	protected function encodePayload() : void
	{
		$this->putLFloat($this->motionX);
		$this->putLFloat($this->motionY);
		$this->putBool($this->jumping);
		$this->putBool($this->sneaking);
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handlePlayerInput($this);
	}
}
