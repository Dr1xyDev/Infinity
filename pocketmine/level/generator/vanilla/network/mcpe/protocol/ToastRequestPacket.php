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

/**
 * Displays a toast notification on the client's screen (usually a little box at the top, like the one shown when
 * getting an Xbox Live achievement).
 */
class ToastRequestPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::TOAST_REQUEST_PACKET;

	public string $title;
	public string $content;

	protected function decodePayload() : void
	{
		$this->title = $this->getString();
		$this->content = $this->getString();
	}

	protected function encodePayload() : void
	{
		$this->putString($this->title);
		$this->putString($this->content);
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleToastRequest($this);
	}
}
