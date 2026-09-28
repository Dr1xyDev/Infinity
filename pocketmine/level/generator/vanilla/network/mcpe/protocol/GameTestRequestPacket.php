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

class GameTestRequestPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::GAME_TEST_REQUEST_PACKET;

	public const ROTATION_0 = 0;
	public const ROTATION_90 = 1;
	public const ROTATION_180 = 2;
	public const ROTATION_270 = 3;

	private int $maxTestsPerBatch;
	private int $repeatCount;
	private int $rotation;
	private bool $stopOnFailure;
	private int $x = 0;
	private int $y = 0;
	private int $z = 0;
	private int $testsPerRow;
	private string $testName;

	/**
	 * @generate-create-func
	 */
	public static function create(
		int $maxTestsPerBatch,
		int $repeatCount,
		int $rotation,
		bool $stopOnFailure,
		int $x,
		int $y,
		int $z,
		int $testsPerRow,
		string $testName,
	) : self {
		$result = new self();
		$result->maxTestsPerBatch = $maxTestsPerBatch;
		$result->repeatCount = $repeatCount;
		$result->rotation = $rotation;
		$result->stopOnFailure = $stopOnFailure;
		$result->x = $x;
		$result->y = $y;
		$result->z = $z;
		$result->testsPerRow = $testsPerRow;
		$result->testName = $testName;
		return $result;
	}

	public function getMaxTestsPerBatch() : int
	{
		return $this->maxTestsPerBatch;
	}

	public function getRepeatCount() : int
	{
		return $this->repeatCount;
	}

	/**
	 * @see self::ROTATION_*
	 */
	public function getRotation() : int
	{
		return $this->rotation;
	}

	public function isStopOnFailure() : bool
	{
		return $this->stopOnFailure;
	}

	public function getX() : int
	{
		return $this->x;
	}

	public function getY() : int
	{
		return $this->y;
	}

	public function getZ() : int
	{
		return $this->z;
	}

	public function getTestsPerRow() : int
	{
		return $this->testsPerRow;
	}

	public function getTestName() : string
	{
		return $this->testName;
	}

	protected function decodePayload() : void
	{
		$this->maxTestsPerBatch = $this->getVarInt();
		$this->repeatCount = $this->getVarInt();
		$this->rotation = $this->getByte();
		$this->stopOnFailure = $this->getBool();
		$this->getSignedBlockPosition($this->x, $this->y, $this->z);
		$this->testsPerRow = $this->getVarInt();
		$this->testName = $this->getString();
	}

	protected function encodePayload() : void
	{
		$this->putVarInt($this->maxTestsPerBatch);
		$this->putVarInt($this->repeatCount);
		$this->putByte($this->rotation);
		$this->putBool($this->stopOnFailure);
		$this->putSignedBlockPosition($this->x, $this->y, $this->z);
		$this->putVarInt($this->testsPerRow);
		$this->putString($this->testName);
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleGameTestRequest($this);
	}
}
