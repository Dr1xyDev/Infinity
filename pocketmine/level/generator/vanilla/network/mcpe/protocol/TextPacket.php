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

use pocketmine\level\generator\vanilla\network\mcpe\convert\ConstantTranslator;
use pocketmine\level\generator\vanilla\network\mcpe\NetworkSession;

use function count;

class TextPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::TEXT_PACKET;

	private const CATEGORY_MESSAGE_ONLY = 0;
	private const CATEGORY_AUTHORED_MESSAGE = 1;
	private const CATEGORY_MESSAGE_WITH_PARAMETERS = 2;

	private const CATEGORY_DUMMY_STRINGS = [
		self::CATEGORY_MESSAGE_ONLY => [
			'raw',
			'tip',
			'systemMessage',
			'textObjectWhisper',
			'textObjectAnnouncement',
			'textObject'
		],
		self::CATEGORY_AUTHORED_MESSAGE => [
			'chat',
			'whisper',
			'announcement'
		],
		self::CATEGORY_MESSAGE_WITH_PARAMETERS => [
			'translate',
			'popup',
			'jukeboxPopup',
		]
	];

	public const TYPE_RAW = 0;
	public const TYPE_CHAT = 1;
	public const TYPE_TRANSLATION = 2;
	public const TYPE_POPUP = 3;
	public const TYPE_JUKEBOX_POPUP = 4;
	public const TYPE_TIP = 5;
	public const TYPE_SYSTEM = 6;
	public const TYPE_WHISPER = 7;
	public const TYPE_ANNOUNCEMENT = 8;
	public const TYPE_JSON_WHISPER = 9;
	public const TYPE_JSON = 10;
	public const TYPE_JSON_ANNOUNCEMENT = 11;

	public const PARAMETERS_LIMIT = 5;

	public int $type;
	public bool $needsTranslation = false;
	public string $sourceName = "";
	public string $message;
	/** @var string[] */
	public array $parameters = [];
	public string $xboxUserId = "";
	public string $platformChatId = "";
	public ?string $filteredMessage = null;

	protected function decodePayload() : void
	{
		if ($this->protocol >= ProtocolInfo::PROTOCOL_897) {
			$this->needsTranslation = $this->getBool();

			$category = $this->getByte();
			if ($this->protocol < ProtocolInfo::PROTOCOL_924) {
				$expectedDummyStrings = self::CATEGORY_DUMMY_STRINGS[$category] ?? throw new PacketDecodeException("Unknown category ID $category");
				foreach ($expectedDummyStrings as $k => $expectedDummyString) {
					$actual = $this->getString();
					if ($expectedDummyString !== $actual) {
						throw new PacketDecodeException("Dummy string mismatch for category $category at position $k: expected $expectedDummyString, got $actual");
					}
				}
			}
		}

		$this->type = ConstantTranslator::getInstance()->fromNetworkId(TextPacket::class, $this->getByte(), $this->protocol);
		if ($this->protocol >= ProtocolInfo::PROTOCOL_407 && $this->protocol < ProtocolInfo::PROTOCOL_897) {
			$this->needsTranslation = $this->getBool();
		}

		switch ($this->type) {
			case self::TYPE_CHAT:
			case self::TYPE_WHISPER:
			case self::TYPE_ANNOUNCEMENT:
				$this->sourceName = $this->getString();
			// no break
			case self::TYPE_RAW:
			case self::TYPE_TIP:
			case self::TYPE_SYSTEM:
			case self::TYPE_JSON_WHISPER:
			case self::TYPE_JSON:
			case self::TYPE_JSON_ANNOUNCEMENT:
				$this->message = $this->getString();
				break;
			case self::TYPE_POPUP:
				if ($this->protocol < ProtocolInfo::PROTOCOL_407) {
					$this->sourceName = $this->getString();
					$this->message = $this->getString();
					break;
				}
			// no break
			case self::TYPE_TRANSLATION:
			case self::TYPE_JUKEBOX_POPUP:
				$this->message = $this->getString();
				$count = $this->getUnsignedVarInt();
				if ($count > self::PARAMETERS_LIMIT) {
					throw new PacketDecodeException("Too many translation parameters count: $count");
				}
				for ($i = 0; $i < $count; ++$i) {
					$this->parameters[] = $this->getString();
				}
				break;
		}

		if ($this->protocol >= ProtocolInfo::PROTOCOL_407) {
			$this->xboxUserId = $this->getString();
			$this->platformChatId = $this->getString();
			if ($this->protocol >= ProtocolInfo::PROTOCOL_685) {
				if ($this->protocol >= ProtocolInfo::PROTOCOL_897) {
					$this->filteredMessage = $this->getOptional($this->getString(...));
				} else {
					$filteredMessage = $this->getString();
					$this->filteredMessage = $filteredMessage === "" ? null : $filteredMessage;
				}
			}
		}
	}

	protected function encodePayload() : void
	{
		if ($this->protocol >= ProtocolInfo::PROTOCOL_897) {
			$this->putBool($this->needsTranslation);

			$category = match ($this->type) {
				self::TYPE_RAW,
				self::TYPE_TIP,
				self::TYPE_SYSTEM,
				self::TYPE_JSON_WHISPER,
				self::TYPE_JSON_ANNOUNCEMENT,
				self::TYPE_JSON => self::CATEGORY_MESSAGE_ONLY,

				self::TYPE_CHAT,
				self::TYPE_WHISPER,
				self::TYPE_ANNOUNCEMENT => self::CATEGORY_AUTHORED_MESSAGE,

				self::TYPE_TRANSLATION,
				self::TYPE_POPUP,
				self::TYPE_JUKEBOX_POPUP => self::CATEGORY_MESSAGE_WITH_PARAMETERS,

				default => throw new \LogicException("Invalid TextPacket type: $this->type")
			};
			$this->putByte($category);

			if ($this->protocol < ProtocolInfo::PROTOCOL_924) {
				foreach (self::CATEGORY_DUMMY_STRINGS[$category] as $dummyString) {
					$this->putString($dummyString);
				}
			}
		}

		$this->putByte(ConstantTranslator::getInstance()->toNetworkId(TextPacket::class, $this->type, $this->protocol));
		if ($this->protocol >= ProtocolInfo::PROTOCOL_407 && $this->protocol < ProtocolInfo::PROTOCOL_897) {
			$this->putBool($this->needsTranslation);
		}

		$message = $this->message === "" ? " " : $this->message;
		switch ($this->type) {
			case self::TYPE_CHAT:
			case self::TYPE_WHISPER:
			case self::TYPE_ANNOUNCEMENT:
				$this->putString($this->sourceName);
			// no break
			case self::TYPE_RAW:
			case self::TYPE_TIP:
			case self::TYPE_SYSTEM:
			case self::TYPE_JSON_WHISPER:
			case self::TYPE_JSON:
			case self::TYPE_JSON_ANNOUNCEMENT:
				$this->putString($message);
				break;
			case self::TYPE_POPUP:
				if ($this->protocol < ProtocolInfo::PROTOCOL_407) {
					$this->putString($this->sourceName);
					$this->putString($message);
					break;
				}
			// no break
			case self::TYPE_TRANSLATION:
			case self::TYPE_JUKEBOX_POPUP:
				$this->putString($message);
				$this->putUnsignedVarInt(count($this->parameters));
				foreach ($this->parameters as $p) {
					$this->putString($p);
				}
				break;
		}

		if ($this->protocol >= ProtocolInfo::PROTOCOL_407) {
			$this->putString($this->xboxUserId);
			$this->putString($this->platformChatId);
			if ($this->protocol >= ProtocolInfo::PROTOCOL_685) {
				if ($this->protocol >= ProtocolInfo::PROTOCOL_897) {
					$this->putOptional($this->filteredMessage, $this->putString(...));
				} else {
					$this->putString($this->filteredMessage === null ? "" : $this->filteredMessage);
				}
			}
		}
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleText($this);
	}
}
