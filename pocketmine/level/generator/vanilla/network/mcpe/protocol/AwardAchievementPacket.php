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

class AwardAchievementPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::AWARD_ACHIEVEMENT_PACKET;

	private int $achievementId;

	/**
	 * @generate-create-func
	 */
	public static function create(int $achievementId) : self
	{
		$result = new self();
		$result->achievementId = $achievementId;
		return $result;
	}

	public function getAchievementId() : int
	{
		return $this->achievementId;
	}

	protected function decodePayload() : void
	{
		$this->achievementId = $this->getLInt();
	}

	protected function encodePayload() : void
	{
		$this->putLInt($this->achievementId);
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleAwardAchievement($this);
	}
}
