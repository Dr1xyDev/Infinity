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

class CurrentStructureFeaturePacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::CURRENT_STRUCTURE_FEATURE_PACKET;

	public string $currentStructureFeature;

	/**
	 * @generate-create-func
	 */
	public static function create(string $currentStructureFeature) : self
	{
		$result = new self();
		$result->currentStructureFeature = $currentStructureFeature;
		return $result;
	}

	protected function decodePayload() : void
	{
		$this->currentStructureFeature = $this->getString();
	}

	protected function encodePayload() : void
	{
		$this->putString($this->currentStructureFeature);
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleCurrentStructureFeature($this);
	}
}
