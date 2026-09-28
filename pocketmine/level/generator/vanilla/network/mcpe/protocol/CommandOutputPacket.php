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
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\command\CommandOriginData;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\command\CommandOutputMessage;

use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\OutputType;
use function count;

class CommandOutputPacket extends DataPacket {
	public const NETWORK_ID = ProtocolInfo::COMMAND_OUTPUT_PACKET;

	public CommandOriginData $originData;
	public OutputType $outputType;
	public int $successCount;
	/** @var CommandOutputMessage[] */
	public array $messages = [];
	public ?string $data = null;

	protected function decodePayload() : void
	{
		$this->originData = $this->getCommandOriginData();
		if ($this->protocol >= ProtocolInfo::PROTOCOL_897) {
			$this->outputType = OutputType::fromName($this->getString());
		} else {
			$this->outputType = OutputType::fromPacket($this->getByte());
		}

		if ($this->protocol >= ProtocolInfo::PROTOCOL_897) {
			$this->successCount = $this->getLInt();
		} else {
			$this->successCount = $this->getUnsignedVarInt();
		}

		for ($i = 0, $size = $this->getUnsignedVarInt(); $i < $size; ++$i) {
			$this->messages[] = $this->getCommandMessage();
		}

		if ($this->protocol >= ProtocolInfo::PROTOCOL_897) {
			$this->data = $this->getOptional($this->getString(...));
		} else {
			if ($this->outputType === OutputType::DATA_SET) {
				$this->data = $this->getString();
			}
		}
	}

	protected function getCommandMessage() : CommandOutputMessage{
		$message = new CommandOutputMessage();

		if ($this->protocol >= ProtocolInfo::PROTOCOL_897) {
			$message->messageId = $this->getString();
			$message->isInternal = $this->getBool();
		} else {
			$message->isInternal = $this->getBool();
			$message->messageId = $this->getString();
		}

		for ($i = 0, $size = $this->getUnsignedVarInt(); $i < $size; ++$i) {
			$message->parameters[] = $this->getString();
		}

		return $message;
	}

	protected function encodePayload() : void{
		$this->putCommandOriginData($this->originData);
		if ($this->protocol >= ProtocolInfo::PROTOCOL_897) {
			$this->putString($this->outputType->getName());
		} else {
			$this->putByte($this->outputType->value);
		}

		if ($this->protocol >= ProtocolInfo::PROTOCOL_897) {
			$this->putLInt($this->successCount);
		} else {
			$this->putUnsignedVarInt($this->successCount);
		}

		$this->putUnsignedVarInt(count($this->messages));
		foreach ($this->messages as $message) {
			$this->putCommandMessage($message);
		}

		if ($this->protocol >= ProtocolInfo::PROTOCOL_897) {
			$this->putOptional($this->data, $this->putString(...));
		} else {
			if ($this->outputType === OutputType::DATA_SET) {
				$this->putString($this->data);
			}
		}
	}

	protected function putCommandMessage(CommandOutputMessage $message) : void{
		if ($this->protocol >= ProtocolInfo::PROTOCOL_897) {
			$this->putString($message->messageId);
			$this->putBool($message->isInternal);
		} else {
			$this->putBool($message->isInternal);
			$this->putString($message->messageId);
		}

		$this->putUnsignedVarInt(count($message->parameters));
		foreach ($message->parameters as $parameter) {
			$this->putString($parameter);
		}
	}

	public function mustBeDecoded() : bool{
		return false;
	}

	public function handle(NetworkSession $session) : bool{
		return $session->handleCommandOutput($this);
	}
}
