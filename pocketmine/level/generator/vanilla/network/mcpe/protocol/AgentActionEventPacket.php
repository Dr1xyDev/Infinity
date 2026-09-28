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
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\AgentActionType;

/**
 * Used by code builder, exact purpose unclear
 */
class AgentActionEventPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::AGENT_ACTION_EVENT_PACKET;

	private string $requestId;
	/** @see AgentActionType */
	private int $action;
	private string $responseJson;

	/**
	 * @generate-create-func
	 */
	public static function create(string $requestId, int $action, string $responseJson) : self
	{
		$result = new self();
		$result->requestId = $requestId;
		$result->action = $action;
		$result->responseJson = $responseJson;
		return $result;
	}

	public function getRequestId() : string
	{
		return $this->requestId;
	}

	/** @see AgentActionType */
	public function getAction() : int
	{
		return $this->action;
	}

	public function getResponseJson() : string
	{
		return $this->responseJson;
	}

	protected function decodePayload() : void
	{
		$this->requestId = $this->getString();
		$this->action = $this->getLInt();
		$this->responseJson = $this->getString();
	}

	protected function encodePayload() : void
	{
		$this->putString($this->requestId);
		$this->putLInt($this->action);
		$this->putString($this->responseJson);
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleAgentActionEvent($this);
	}
}
