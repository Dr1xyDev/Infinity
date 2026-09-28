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
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\ShowStoreOfferRedirectType;
use pocketmine\level\generator\vanilla\utils\UUID;

class ShowStoreOfferPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::SHOW_STORE_OFFER_PACKET;

	/** A UUID since 1.21.120, a string before (either form is accepted and converted when encoding) */
	public string|UUID $offerId;
	public bool $showAll = false;
	public ShowStoreOfferRedirectType $redirectType = ShowStoreOfferRedirectType::MARKETPLACE;

	protected function decodePayload() : void
	{
		if ($this->protocol >= ProtocolInfo::PROTOCOL_859) {
			$this->offerId = $this->getUUID();
		} else {
			$this->offerId = $this->getString();
		}

		if ($this->protocol >= ProtocolInfo::PROTOCOL_630) {
			$this->redirectType = ShowStoreOfferRedirectType::fromPacket($this->getByte());
		} else {
			$this->showAll = $this->getBool();
		}
	}

	protected function encodePayload() : void
	{
		if ($this->protocol >= ProtocolInfo::PROTOCOL_859) {
			$offerId = $this->offerId;
			if (is_string($offerId)) {
				$offerId = preg_match('/^[0-9a-f]{8}-?[0-9a-f]{4}-?[0-9a-f]{4}-?[0-9a-f]{4}-?[0-9a-f]{12}$/i', $offerId) === 1 ? UUID::fromString($offerId) : UUID::fromData($offerId);
			}
			$this->putUUID($offerId);
		} else {
			$this->putString($this->offerId instanceof UUID ? $this->offerId->toString() : $this->offerId);
		}

		if ($this->protocol >= ProtocolInfo::PROTOCOL_630) {
			$this->putByte($this->redirectType->value);
		} else {
			$this->putBool($this->showAll);
		}
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleShowStoreOffer($this);
	}
}
