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

class AddBehaviorTreePacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::ADD_BEHAVIOR_TREE_PACKET;

	public string $behaviorTreeJson;

	/**
	 * @generate-create-func
	 */
	public static function create(string $behaviorTreeJson) : self
	{
		$result = new self();
		$result->behaviorTreeJson = $behaviorTreeJson;
		return $result;
	}

	protected function decodePayload() : void
	{
		$this->behaviorTreeJson = $this->getString();
	}

	protected function encodePayload() : void
	{
		$this->putString($this->behaviorTreeJson);
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleAddBehaviorTree($this);
	}
}
