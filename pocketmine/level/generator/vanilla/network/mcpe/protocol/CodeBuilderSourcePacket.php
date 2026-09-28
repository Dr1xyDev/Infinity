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

class CodeBuilderSourcePacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::CODE_BUILDER_SOURCE_PACKET;

	private int $operation;
	private int $category;
	private string $value;
	private int $codeStatus;

	/**
	 * @generate-create-func
	 */
	public static function create(int $operation, int $category, string $value, int $codeStatus) : self
	{
		$result = new self();
		$result->operation = $operation;
		$result->category = $category;
		$result->value = $value;
		$result->codeStatus = $codeStatus;
		return $result;
	}

	public function getOperation() : int
	{
		return $this->operation;
	}

	public function getCategory() : int
	{
		return $this->category;
	}

	public function getValue() : string
	{
		return $this->value;
	}

	public function getCodeStatus() : int
	{
		return $this->codeStatus;
	}

	protected function decodePayload() : void
	{
		$this->operation = $this->getByte();
		$this->category = $this->getByte();
		if ($this->protocol >= ProtocolInfo::PROTOCOL_685) {
			$this->codeStatus = $this->getByte();
		} else {
			$this->value = $this->getString();
		}
	}

	protected function encodePayload() : void
	{
		$this->putByte($this->operation);
		$this->putByte($this->category);
		if ($this->protocol >= ProtocolInfo::PROTOCOL_685) {
			$this->putByte($this->codeStatus);
		} else {
			$this->putString($this->value);
		}
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleCodeBuilderSource($this);
	}
}
