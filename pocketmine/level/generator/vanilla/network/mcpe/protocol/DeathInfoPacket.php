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

use function count;

/**
 * Sets the message shown on the death screen instead of "You died!"
 */
class DeathInfoPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::DEATH_INFO_PACKET;

	private string $messageTranslationKey;
	/** @var string[] */
	private array $messageParameters = [];

	/**
	 * @generate-create-func
	 * @param string[] $messageParameters
	 */
	public static function create(string $messageTranslationKey, array $messageParameters) : self
	{
		$result = new self();
		$result->messageTranslationKey = $messageTranslationKey;
		$result->messageParameters = $messageParameters;
		return $result;
	}

	public function getMessageTranslationKey() : string
	{
		return $this->messageTranslationKey;
	}

	/** @return string[] */
	public function getMessageParameters() : array
	{
		return $this->messageParameters;
	}

	protected function decodePayload() : void
	{
		$this->messageTranslationKey = $this->getString();

		$this->messageParameters = [];
		for ($i = 0, $len = $this->getUnsignedVarInt(); $i < $len; $i++) {
			$this->messageParameters[] = $this->getString();
		}
	}

	protected function encodePayload() : void
	{
		$this->putString($this->messageTranslationKey);

		$this->putUnsignedVarInt(count($this->messageParameters));
		foreach ($this->messageParameters as $parameter) {
			$this->putString($parameter);
		}
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleDeathInfo($this);
	}
}
