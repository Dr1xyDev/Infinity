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

use InvalidArgumentException;
use pocketmine\level\generator\vanilla\network\mcpe\NetworkSession;

class BookEditPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::BOOK_EDIT_PACKET;

	public const TYPE_REPLACE_PAGE = 0;
	public const TYPE_ADD_PAGE = 1;
	public const TYPE_DELETE_PAGE = 2;
	public const TYPE_SWAP_PAGES = 3;
	public const TYPE_SIGN_BOOK = 4;

	/** @var int */
	public $type;
	/** @var int */
	public $inventorySlot;
	/** @var int */
	public $pageNumber;
	/** @var int */
	public $secondaryPageNumber;

	/** @var string */
	public $text;
	/** @var string */
	public $photoName;

	/** @var string */
	public $title;
	/** @var string */
	public $author;
	/** @var string */
	public $xuid;

	protected function decodePayload() : void{
		if ($this->protocol >= ProtocolInfo::PROTOCOL_924) {
			$this->inventorySlot = $this->getUnsignedVarInt();
			$this->type = $this->getUnsignedVarInt();
		} else {
			$this->type = $this->getByte();
			$this->inventorySlot = $this->getByte();
		}

		switch ($this->type) {
			case self::TYPE_REPLACE_PAGE:
			case self::TYPE_ADD_PAGE:
				if ($this->protocol >= ProtocolInfo::PROTOCOL_924) {
					$this->pageNumber = $this->getUnsignedVarInt();
				} else {
					$this->pageNumber = $this->getByte();
				}

				$this->text = $this->getString();
				$this->photoName = $this->getString();
				break;
			case self::TYPE_DELETE_PAGE:
				if ($this->protocol >= ProtocolInfo::PROTOCOL_924) {
					$this->pageNumber = $this->getUnsignedVarInt();
				} else {
					$this->pageNumber = $this->getByte();
				}
				break;
			case self::TYPE_SWAP_PAGES:
				if ($this->protocol >= ProtocolInfo::PROTOCOL_924) {
					$this->pageNumber = $this->getUnsignedVarInt();
					$this->secondaryPageNumber = $this->getUnsignedVarInt();
				} else {
					$this->pageNumber = $this->getByte();
					$this->secondaryPageNumber = $this->getByte();
				}
				break;
			case self::TYPE_SIGN_BOOK:
				$this->title = $this->getString();
				$this->author = $this->getString();
				if ($this->protocol >= ProtocolInfo::PROTOCOL_407) {
					$this->xuid = $this->getString();
				}
				break;
			default:
				throw new PacketDecodeException("Unknown book edit type $this->type!");
		}
	}

	protected function encodePayload() : void{
		if ($this->protocol >= ProtocolInfo::PROTOCOL_924) {
			$this->putUnsignedVarInt($this->inventorySlot);
			$this->putUnsignedVarInt($this->type);
		} else {
			$this->putByte($this->type);
			$this->putByte($this->inventorySlot);
		}

		switch ($this->type) {
			case self::TYPE_REPLACE_PAGE:
			case self::TYPE_ADD_PAGE:
				if ($this->protocol >= ProtocolInfo::PROTOCOL_924) {
					$this->putUnsignedVarInt($this->pageNumber);
				} else {
					$this->putByte($this->pageNumber);
				}

				$this->putString($this->text);
				$this->putString($this->photoName);
				break;
			case self::TYPE_DELETE_PAGE:
				if ($this->protocol >= ProtocolInfo::PROTOCOL_924) {
					$this->putUnsignedVarInt($this->pageNumber);
				} else {
					$this->putByte($this->pageNumber);
				}
				break;
			case self::TYPE_SWAP_PAGES:
				if ($this->protocol >= ProtocolInfo::PROTOCOL_924) {
					$this->putUnsignedVarInt($this->pageNumber);
					$this->putUnsignedVarInt($this->secondaryPageNumber);
				} else {
					$this->putByte($this->pageNumber);
					$this->putByte($this->secondaryPageNumber);
				}
				break;
			case self::TYPE_SIGN_BOOK:
				$this->putString($this->title);
				$this->putString($this->author);
				if ($this->protocol >= ProtocolInfo::PROTOCOL_407) {
					$this->putString($this->xuid);
				}
				break;
			default:
				throw new InvalidArgumentException("Unknown book edit type $this->type!");
		}
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleBookEdit($this);
	}
}
