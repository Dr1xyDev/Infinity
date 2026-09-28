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

/**
 * Handled only in Education mode. Used to fire telemetry reporting on the client.
 */
class LessonProgressPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::LESSON_PROGRESS_PACKET;

	public const ACTION_START = 0;
	public const ACTION_FINISH = 1;
	public const ACTION_RESTART = 2;

	private int $action;
	private int $score;
	private string $activityId;

	/**
	 * @generate-create-func
	 */
	public static function create(int $action, int $score, string $activityId) : self
	{
		$result = new self();
		$result->action = $action;
		$result->score = $score;
		$result->activityId = $activityId;
		return $result;
	}

	public function getAction() : int
	{
		return $this->action;
	}

	public function getScore() : int
	{
		return $this->score;
	}

	public function getActivityId() : string
	{
		return $this->activityId;
	}

	protected function decodePayload() : void
	{
		$this->action = $this->getVarInt();
		$this->score = $this->getVarInt();
		$this->activityId = $this->getString();
	}

	protected function encodePayload() : void
	{
		$this->putVarInt($this->action);
		$this->putVarInt($this->score);
		$this->putString($this->activityId);
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleLessonProgress($this);
	}
}
