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

use pocketmine\level\generator\vanilla\math\Vector2;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\network\mcpe\convert\ConstantTranslator;
use pocketmine\level\generator\vanilla\network\mcpe\NetworkSession;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\InputMode;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\InteractionMode;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\inventory\stackrequest\ItemStackRequest;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\ItemInteractionData;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\PlayerAuthInputFlags;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\PlayerAuthInputVehicleInfo;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\PlayerBlockAction;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\PlayerBlockActionStopBreak;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\PlayerBlockActionWithBlockInfo;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\PlayMode;
use function assert;
use function count;

class PlayerAuthInputPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::PLAYER_AUTH_INPUT_PACKET;

	private Vector3 $position;
	private float $pitch;
	private float $yaw;
	private float $headYaw;
	private float $moveVecX;
	private float $moveVecZ;
	private int $inputFlags;
	private int $inputMode;
	private int $playMode;
	private int $interactionMode;
	private ?Vector3 $vrGazeDirection = null;
	private Vector2 $interactRotation;
	private int $tick;
	private Vector3 $delta;
	private ?ItemInteractionData $itemInteractionData = null;
	private ?ItemStackRequest $itemStackRequest = null;
	/** @var PlayerBlockAction[]|null */
	private ?array $blockActions = null;
	private ?PlayerAuthInputVehicleInfo $vehicleInfo = null;
	private float $analogMoveVecX;
	private float $analogMoveVecZ;
	private Vector3 $cameraOrientation;
	private Vector2 $rawMove;

	/**
	 * @param int                      $inputFlags      @see PlayerAuthInputFlags
	 * @param int                      $inputMode       @see InputMode
	 * @param int                      $playMode        @see PlayMode
	 * @param int                      $interactionMode @see InteractionMode
	 * @param Vector3|null             $vrGazeDirection only used when PlayMode::VR
	 * @param PlayerBlockAction[]|null $blockActions    Blocks that the client has interacted with
	 */
	public static function create(
		Vector3 $position,
		float $pitch,
		float $yaw,
		float $headYaw,
		float $moveVecX,
		float $moveVecZ,
		int $inputFlags,
		int $inputMode,
		int $playMode,
		int $interactionMode,
		?Vector3 $vrGazeDirection,
		Vector2 $interactRotation,
		int $tick,
		Vector3 $delta,
		?ItemInteractionData $itemInteractionData,
		?ItemStackRequest $itemStackRequest,
		?array $blockActions,
		?PlayerAuthInputVehicleInfo $vehicleInfo,
		float $analogMoveVecX,
		float $analogMoveVecZ,
		Vector3 $cameraOrientation,
		Vector2 $rawMove,
	) : self {
		if ($playMode === PlayMode::VR && $vrGazeDirection === null) {
			//yuck, can we get a properly written packet just once? ...
			throw new \InvalidArgumentException("Gaze direction must be provided for VR play mode");
		}
		$result = new self();
		$result->position = $position->asVector3();
		$result->pitch = $pitch;
		$result->yaw = $yaw;
		$result->headYaw = $headYaw;
		$result->moveVecX = $moveVecX;
		$result->moveVecZ = $moveVecZ;

		$result->inputFlags = $inputFlags & ~((1 << PlayerAuthInputFlags::PERFORM_ITEM_STACK_REQUEST) | (1 << PlayerAuthInputFlags::PERFORM_ITEM_INTERACTION) | (1 << PlayerAuthInputFlags::PERFORM_BLOCK_ACTIONS));
		if ($itemStackRequest !== null) {
			$result->inputFlags |= 1 << PlayerAuthInputFlags::PERFORM_ITEM_STACK_REQUEST;
		}
		if ($itemInteractionData !== null) {
			$result->inputFlags |= 1 << PlayerAuthInputFlags::PERFORM_ITEM_INTERACTION;
		}
		if ($blockActions !== null) {
			$result->inputFlags |= 1 << PlayerAuthInputFlags::PERFORM_BLOCK_ACTIONS;
		}
		if ($vehicleInfo !== null) {
			$result->inputFlags |= 1 << PlayerAuthInputFlags::IN_CLIENT_PREDICTED_VEHICLE;
		}

		$result->inputMode = $inputMode;
		$result->playMode = $playMode;
		$result->interactionMode = $interactionMode;
		if ($vrGazeDirection !== null) {
			$result->vrGazeDirection = $vrGazeDirection->asVector3();
		}
		$result->interactRotation = $interactRotation;
		$result->tick = $tick;
		$result->delta = $delta;
		$result->itemInteractionData = $itemInteractionData;
		$result->itemStackRequest = $itemStackRequest;
		$result->blockActions = $blockActions;
		$result->vehicleInfo = $vehicleInfo;
		$result->analogMoveVecX = $analogMoveVecX;
		$result->analogMoveVecZ = $analogMoveVecZ;
		$result->cameraOrientation = $cameraOrientation;
		$result->rawMove = $rawMove;
		return $result;
	}

	public function getPosition() : Vector3
	{
		return $this->position;
	}

	public function getPitch() : float
	{
		return $this->pitch;
	}

	public function getYaw() : float
	{
		return $this->yaw;
	}

	public function getHeadYaw() : float
	{
		return $this->headYaw;
	}

	public function getMoveVecX() : float
	{
		return $this->moveVecX;
	}

	public function getMoveVecZ() : float
	{
		return $this->moveVecZ;
	}

	/**
	 * @see PlayerAuthInputFlags
	 */
	public function getInputFlags() : int
	{
		return $this->inputFlags;
	}

	/**
	 * @see InputMode
	 */
	public function getInputMode() : int
	{
		return $this->inputMode;
	}

	/**
	 * @see PlayMode
	 */
	public function getPlayMode() : int
	{
		return $this->playMode;
	}

	/**
	 * @see InteractionMode
	 */
	public function getInteractionMode() : int
	{
		return $this->interactionMode;
	}

	public function getVrGazeDirection() : ?Vector3
	{
		return $this->vrGazeDirection;
	}

	public function getInteractRotation() : Vector2
	{
		return $this->interactRotation;
	}

	public function getTick() : int
	{
		return $this->tick;
	}

	public function getDelta() : Vector3
	{
		return $this->delta;
	}

	public function getItemInteractionData() : ?ItemInteractionData
	{
		return $this->itemInteractionData;
	}

	public function getItemStackRequest() : ?ItemStackRequest
	{
		return $this->itemStackRequest;
	}

	/**
	 * @return PlayerBlockAction[]|null
	 */
	public function getBlockActions() : ?array
	{
		return $this->blockActions;
	}

	public function getVehicleInfo() : ?PlayerAuthInputVehicleInfo
	{
		return $this->vehicleInfo;
	}

	public function getAnalogMoveVecX() : float
	{
		return $this->analogMoveVecX;
	}

	public function getAnalogMoveVecZ() : float
	{
		return $this->analogMoveVecZ;
	}

	public function getCameraOrientation() : Vector3
	{
		return $this->cameraOrientation;
	}

	public function getRawMove() : Vector2
	{
		return $this->rawMove;
	}

	public function hasFlag(int $flag) : bool
	{
		return ($this->inputFlags & (1 << $flag)) !== 0;
	}

	/** Input flags are a list of set flag indices since 1.26.40; flags above this can't be stored in an int */
	private const MAX_STORABLE_FLAG = 63;

	private function decodePayloadV2168() : void
	{
		$this->pitch = $this->getLFloat();
		$this->yaw = $this->getLFloat();
		$this->position = $this->getVector3();
		$this->moveVecX = $this->getLFloat();
		$this->moveVecZ = $this->getLFloat();
		$this->headYaw = $this->getLFloat();

		if ($this->protocol < ProtocolInfo::PROTOCOL_2193) {
			$this->getDummyOptional();
		}
		$this->inputFlags = 0;
		$seenFlags = [];
		foreach ($this->getList(fn() => $this->getVarInt(), 256) as $flag) {
			if (isset($seenFlags[$flag])) {
				throw new PacketDecodeException("Duplicate input flag $flag");
			}
			$seenFlags[$flag] = true;
			if ($flag >= 0 && $flag <= self::MAX_STORABLE_FLAG) {
				$this->inputFlags |= 1 << $flag;
			}
		}

		$this->inputMode = $this->getUnsignedVarInt();
		$this->playMode = $this->getUnsignedVarInt();
		$this->interactionMode = $this->getVarInt();
		$this->interactRotation = $this->getVector2();
		$this->tick = $this->getUnsignedVarLong();
		$this->delta = $this->getVector3();

		$this->itemInteractionData = $this->getProtocolOptional(fn() => ItemInteractionData::read($this));
		$this->itemStackRequest = $this->getProtocolOptional(fn() => ItemStackRequest::read($this));
		$this->blockActions = $this->getProtocolOptional(fn() => $this->getList(function() : PlayerBlockAction{
			$actionType = ConstantTranslator::getInstance()->fromNetworkId(PlayerActionPacket::class, $this->getVarInt(), $this->protocol);
			if ($actionType === PlayerActionPacket::ACTION_STOP_BREAK) {
				$x = $y = $z = 0;
				$this->getBlockPosition($x, $y, $z); //since 1.26.40 every block action has a position and face
				$this->getVarInt();
				return new PlayerBlockActionStopBreak();
			}
			if (!PlayerBlockActionWithBlockInfo::isValidActionType($actionType)) {
				throw new PacketDecodeException("Unexpected block action type $actionType");
			}
			return PlayerBlockActionWithBlockInfo::read($this, $actionType);
		}, 256));
		$vehicleRotation = $this->getProtocolOptional(fn() => $this->getVector2());
		$vehicleActorUniqueId = $this->getProtocolOptional(fn() => $this->getEntityUniqueId());
		if ($vehicleRotation !== null && $vehicleActorUniqueId !== null) {
			$this->vehicleInfo = new PlayerAuthInputVehicleInfo($vehicleRotation->x, $vehicleRotation->y, $vehicleActorUniqueId);
		} elseif ($vehicleRotation !== null || $vehicleActorUniqueId !== null) {
			throw new PacketDecodeException("Vehicle rotation and actor unique ID must both be present or both be absent");
		}

		//keep the legacy flags consistent with the data that is actually present
		$this->setFlag(PlayerAuthInputFlags::PERFORM_ITEM_INTERACTION, $this->itemInteractionData !== null);
		$this->setFlag(PlayerAuthInputFlags::PERFORM_ITEM_STACK_REQUEST, $this->itemStackRequest !== null);
		$this->setFlag(PlayerAuthInputFlags::PERFORM_BLOCK_ACTIONS, $this->blockActions !== null);
		$this->setFlag(PlayerAuthInputFlags::IN_CLIENT_PREDICTED_VEHICLE, $this->vehicleInfo !== null);

		$this->analogMoveVecX = $this->getLFloat();
		$this->analogMoveVecZ = $this->getLFloat();
		$this->cameraOrientation = $this->getVector3();
		$this->rawMove = $this->getVector2();
	}

	private function encodePayloadV2168() : void
	{
		$this->putLFloat($this->pitch);
		$this->putLFloat($this->yaw);
		$this->putVector3($this->position);
		$this->putLFloat($this->moveVecX);
		$this->putLFloat($this->moveVecZ);
		$this->putLFloat($this->headYaw);

		if ($this->protocol < ProtocolInfo::PROTOCOL_2193) {
			$this->putDummyOptional();
		}
		$flags = [];
		for ($i = 0; $i <= self::MAX_STORABLE_FLAG; ++$i) {
			if ($this->hasFlag($i)) {
				$flags[] = $i;
			}
		}
		$this->putList($flags, fn(int $flag) => $this->putVarInt($flag));

		$this->putUnsignedVarInt($this->inputMode);
		$this->putUnsignedVarInt($this->playMode);
		$this->putVarInt($this->interactionMode);
		$this->putVector2($this->interactRotation);
		$this->putUnsignedVarLong($this->tick);
		$this->putVector3($this->delta);

		$this->putProtocolOptional($this->itemInteractionData, fn(ItemInteractionData $data) => $data->write($this));
		$this->putProtocolOptional($this->itemStackRequest, fn(ItemStackRequest $request) => $request->write($this));
		$this->putProtocolOptional($this->blockActions, fn(array $actions) => $this->putList($actions, function(PlayerBlockAction $action) : void{
			$this->putVarInt(ConstantTranslator::getInstance()->toNetworkId(PlayerActionPacket::class, $action->getActionType(), $this->protocol));
			if ($action instanceof PlayerBlockActionWithBlockInfo) {
				$action->write($this);
			} else {
				$this->putBlockPosition(0, 0, 0);
				$this->putVarInt(0);
			}
		}));
		$this->putProtocolOptional($this->vehicleInfo !== null ? new Vector2($this->vehicleInfo->getVehicleRotationX(), $this->vehicleInfo->getVehicleRotationZ()) : null, fn(Vector2 $v) => $this->putVector2($v));
		$this->putProtocolOptional($this->vehicleInfo?->getPredictedVehicleActorUniqueId(), fn(int $v) => $this->putEntityUniqueId($v));

		$this->putLFloat($this->analogMoveVecX);
		$this->putLFloat($this->analogMoveVecZ);
		$this->putVector3($this->cameraOrientation);
		$this->putVector2($this->rawMove);
	}

	private function setFlag(int $flag, bool $value) : void
	{
		if ($value) {
			$this->inputFlags |= 1 << $flag;
		} else {
			$this->inputFlags &= ~(1 << $flag);
		}
	}

	protected function decodePayload() : void
	{
		if ($this->protocol >= ProtocolInfo::PROTOCOL_2168) {
			$this->decodePayloadV2168();
			return;
		}

		$this->pitch = $this->getLFloat();
		$this->yaw = $this->getLFloat();
		$this->position = $this->getVector3();
		$this->moveVecX = $this->getLFloat();
		$this->moveVecZ = $this->getLFloat();
		$this->headYaw = $this->getLFloat();
		$this->inputFlags = $this->getUnsignedVarLong();
		$this->inputMode = $this->getUnsignedVarInt();
		$this->playMode = $this->getUnsignedVarInt();
		if ($this->protocol >= ProtocolInfo::PROTOCOL_527) {
			$this->interactionMode = $this->getUnsignedVarInt();
		}
		if ($this->protocol >= ProtocolInfo::PROTOCOL_748) {
			$this->interactRotation = $this->getVector2();
		} else {
			if ($this->playMode === PlayMode::VR) {
				$this->vrGazeDirection = $this->getVector3();
			}
		}
		if ($this->protocol >= ProtocolInfo::PROTOCOL_419) {
			$this->tick = $this->getUnsignedVarLong();
			$this->delta = $this->getVector3();

			if ($this->protocol >= ProtocolInfo::PROTOCOL_428) {
				if ($this->hasFlag(PlayerAuthInputFlags::PERFORM_ITEM_INTERACTION)) {
					$this->itemInteractionData = ItemInteractionData::read($this);
				}
				if ($this->hasFlag(PlayerAuthInputFlags::PERFORM_ITEM_STACK_REQUEST)) {
					$this->itemStackRequest = ItemStackRequest::read($this);
				}
				if ($this->hasFlag(PlayerAuthInputFlags::PERFORM_BLOCK_ACTIONS)) {
					$this->blockActions = [];
					$max = $this->getVarInt();
					for ($i = 0; $i < $max; ++$i) {
						$actionType = ConstantTranslator::getInstance()->fromNetworkId(PlayerActionPacket::class, $this->getVarInt(), $this->protocol);
						$this->blockActions[] = match (true) {
							PlayerBlockActionWithBlockInfo::isValidActionType($actionType) => PlayerBlockActionWithBlockInfo::read($this, $actionType),
							$actionType === PlayerActionPacket::ACTION_STOP_BREAK => new PlayerBlockActionStopBreak(),
							default => throw new PacketDecodeException("Unexpected block action type $actionType")
						};
					}
				}

				if ($this->protocol >= ProtocolInfo::PROTOCOL_649) {
					if ($this->hasFlag(PlayerAuthInputFlags::IN_CLIENT_PREDICTED_VEHICLE)) {
						$this->vehicleInfo = PlayerAuthInputVehicleInfo::read($this);
					}
				}

				if ($this->protocol >= ProtocolInfo::PROTOCOL_575) {
					$this->analogMoveVecX = $this->getLFloat();
					$this->analogMoveVecZ = $this->getLFloat();
					if ($this->protocol >= ProtocolInfo::PROTOCOL_748) {
						$this->cameraOrientation = $this->getVector3();
						if ($this->protocol >= ProtocolInfo::PROTOCOL_766) {
							$this->rawMove = $this->getVector2();
						}
					}
				}
			}
		}
	}

	protected function encodePayload() : void
	{
		if ($this->protocol >= ProtocolInfo::PROTOCOL_2168) {
			$this->encodePayloadV2168();
			return;
		}

		$this->putLFloat($this->pitch);
		$this->putLFloat($this->yaw);
		$this->putVector3($this->position);
		$this->putLFloat($this->moveVecX);
		$this->putLFloat($this->moveVecZ);
		$this->putLFloat($this->headYaw);
		$this->putUnsignedVarLong($this->inputFlags);
		$this->putUnsignedVarInt($this->inputMode);
		$this->putUnsignedVarInt($this->playMode);
		if ($this->protocol >= ProtocolInfo::PROTOCOL_527) {
			$this->putUnsignedVarInt($this->interactionMode);
		}

		if ($this->protocol >= ProtocolInfo::PROTOCOL_748) {
			$this->putVector2($this->interactRotation);
		} else {
			if ($this->playMode === PlayMode::VR) {
				assert($this->vrGazeDirection !== null);
				$this->putVector3($this->vrGazeDirection);
			}
		}
		if ($this->protocol >= ProtocolInfo::PROTOCOL_419) {
			$this->putUnsignedVarLong($this->tick);
			$this->putVector3($this->delta);

			if ($this->protocol >= ProtocolInfo::PROTOCOL_471) {
				if ($this->itemInteractionData !== null) {
					$this->itemInteractionData->write($this);
				}
				if ($this->itemStackRequest !== null) {
					$this->itemStackRequest->write($this);
				}
				if ($this->blockActions !== null) {
					$this->putVarInt(count($this->blockActions));
					foreach ($this->blockActions as $blockAction) {
						$this->putVarInt(ConstantTranslator::getInstance()->toNetworkId(PlayerActionPacket::class, $blockAction->getActionType(), $this->protocol));
						$blockAction->write($this);
					}
				}

				if ($this->protocol >= ProtocolInfo::PROTOCOL_649) {
					if ($this->vehicleInfo !== null) {
						$this->vehicleInfo->write($this);
					}
				}

				if ($this->protocol >= ProtocolInfo::PROTOCOL_575) {
					$this->putLFloat($this->analogMoveVecX);
					$this->putLFloat($this->analogMoveVecZ);
					if ($this->protocol >= ProtocolInfo::PROTOCOL_748) {
						$this->putVector3($this->cameraOrientation);
						if ($this->protocol >= ProtocolInfo::PROTOCOL_766) {
							$this->putVector2($this->rawMove);
						}
					}
				}
			}
		}
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handlePlayerAuthInput($this);
	}
}
