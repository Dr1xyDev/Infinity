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

use function count;

class AnimateEntityPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::ANIMATE_ENTITY_PACKET;

	public string $animation;
	public string $nextState;
	public string $stopExpression;
	public int $stopExpressionVersion;
	public string $controller;
	public float $blendOutTime;
	/**
	 * @var int[]
	 * @phpstan-var list<int>
	 */
	public array $actorRuntimeIds = [];

	/**
	 * @param int[] $actorRuntimeIds
	 * @phpstan-param list<int> $actorRuntimeIds
	 */
	public static function create(string $animation, string $nextState, string $stopExpression, int $stopExpressionVersion, string $controller, float $blendOutTime, array $actorRuntimeIds) : self
	{
		$result = new self();
		$result->animation = $animation;
		$result->nextState = $nextState;
		$result->stopExpression = $stopExpression;
		$result->stopExpressionVersion = $stopExpressionVersion;
		$result->controller = $controller;
		$result->blendOutTime = $blendOutTime;
		$result->actorRuntimeIds = $actorRuntimeIds;
		return $result;
	}

	public function getAnimation() : string
	{
		return $this->animation;
	}

	public function getNextState() : string
	{
		return $this->nextState;
	}

	public function getStopExpression() : string
	{
		return $this->stopExpression;
	}

	public function getStopExpressionVersion() : int
	{
		return $this->stopExpressionVersion;
	}

	public function getController() : string
	{
		return $this->controller;
	}

	public function getBlendOutTime() : float
	{
		return $this->blendOutTime;
	}

	/**
	 * @return int[]
	 * @phpstan-return list<int>
	 */
	public function getActorRuntimeIds() : array
	{
		return $this->actorRuntimeIds;
	}

	protected function decodePayload() : void
	{
		$this->animation = $this->getString();
		$this->nextState = $this->getString();
		$this->stopExpression = $this->getString();
		if ($this->protocol >= ProtocolInfo::PROTOCOL_465) {
			$this->stopExpressionVersion = $this->getLInt();
		}
		$this->controller = $this->getString();
		$this->blendOutTime = $this->getLFloat();
		$this->actorRuntimeIds = [];
		$len = $this->getUnsignedVarInt();
		if ($len > 128) {
			throw new PacketDecodeException("Too many actor runtime ID in AnimateEntity: $len");
		}
		for ($i = 0; $i < $len; ++$i) {
			$this->actorRuntimeIds[] = $this->getEntityRuntimeId();
		}
	}

	protected function encodePayload() : void
	{
		$this->putString($this->animation);
		$this->putString($this->nextState);
		$this->putString($this->stopExpression);
		if ($this->protocol >= ProtocolInfo::PROTOCOL_465) {
			$this->putLInt($this->stopExpressionVersion);
		}
		$this->putString($this->controller);
		$this->putLFloat($this->blendOutTime);
		$this->putUnsignedVarInt(count($this->actorRuntimeIds));
		foreach ($this->actorRuntimeIds as $id) {
			$this->putEntityRuntimeId($id);
		}
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleAnimateEntity($this);
	}
}
