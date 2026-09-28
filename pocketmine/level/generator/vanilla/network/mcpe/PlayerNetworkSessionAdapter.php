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

namespace pocketmine\level\generator\vanilla\network\mcpe;

use pocketmine\level\generator\vanilla\entity\passive\AbstractHorse;
use pocketmine\level\generator\vanilla\event\server\DataPacketReceiveEvent;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ActorEventPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ActorFallPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ActorPickRequestPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\AdventureSettingsPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\AnimatePacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\BlockActorDataPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\BlockPickRequestPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\BookEditPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\BossEventPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ClientCacheStatusPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ClientToServerHandshakePacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\CommandBlockUpdatePacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\CommandRequestPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\CommandStepPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ContainerClosePacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ContainerSetSlotPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\CraftingEventPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\DataPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\DropItemPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\EmoteListPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\EmotePacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\InteractPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ItemFrameDropItemPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ItemStackRequestPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\LecternUpdatePacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\LevelSoundEventPacketV1;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\LoginPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\MapInfoRequestPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\MobArmorEquipmentPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\MobEquipmentPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ModalFormResponsePacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\MoveActorAbsolutePacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\MovePlayerPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\NetworkStackLatencyPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\PacketDecodeException;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\PacketViolationWarningPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\PlayerActionPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\PlayerAuthInputPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\PlayerHotbarPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\PlayerInputPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\PlayerSkinPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ProtocolInfo;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\RemoveBlockPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\RequestAbilityPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\RequestChunkRadiusPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\RequestNetworkSettingsPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ResourcePackChunkRequestPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ResourcePackClientResponsePacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\RespawnPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\RiderJumpPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ServerSettingsRequestPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\SetActorMotionPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\SetLocalPlayerAsInitializedPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\SetPlayerFurnaceOptionsPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\SetPlayerGameTypePacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ShowCreditsPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\SpawnExperienceOrbPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\TextPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\TickSyncPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\UseItemPacket;
use pocketmine\level\generator\vanilla\network\PacketHandlingException;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\level\generator\vanilla\timings\Timings;
use pocketmine\level\generator\vanilla\utils\BinaryDataException;

use function bin2hex;
use function is_bool;
use function json_decode;
use function strlen;
use function substr;
use const JSON_THROW_ON_ERROR;

class PlayerNetworkSessionAdapter extends NetworkSession {
	private const MAX_FORM_RESPONSE_SIZE = 10 * 1024; //10 KiB should be more than enough
	private const MAX_FORM_RESPONSE_DEPTH = 2; //modal/simple will be 1, custom forms 2 - they will never contain anything other than string|int|float|bool|null

	private const INCOMING_GAME_PACKETS_PER_TICK = 2;
	private const INCOMING_GAME_PACKETS_BUFFER_TICKS = 100;

	/**
	 * Legacy (< 1.16) clients spam InteractPacket(mouseover) which we drop without handling. These packets are counted
	 * by a separate, more generous limiter so that a legitimate legacy client is never kicked for them, while a
	 * malicious client still can't use them to bypass rate limiting entirely.
	 */
	private const INCOMING_LEGACY_NOISE_PACKETS_PER_TICK = 20;
	private const INCOMING_LEGACY_NOISE_PACKETS_BUFFER_TICKS = 100;

	protected ?string $lastRequestedFullSkinId = null;

	protected PacketRateLimiter $gamePacketLimiter;
	protected PacketRateLimiter $legacyNoisePacketLimiter;

	public function __construct(
		private Server $server,
		private Player $player
	){
		$this->gamePacketLimiter = new PacketRateLimiter("Game Packets", self::INCOMING_GAME_PACKETS_PER_TICK, self::INCOMING_GAME_PACKETS_BUFFER_TICKS);
		$this->legacyNoisePacketLimiter = new PacketRateLimiter("Legacy Noise Packets", self::INCOMING_LEGACY_NOISE_PACKETS_PER_TICK, self::INCOMING_LEGACY_NOISE_PACKETS_BUFFER_TICKS);
	}

	public function isCompressionEnabled() : bool{
		return $this->player->hasNetworkCompression();
	}

	public function getProtocol() : int{
		return $this->player->getProtocolVersion();
	}

	public function handleDataPacket(DataPacket $packet) : void{
		if (!$this->player->isConnected()) {
			return;
		}

		if ($this->player->getProtocolVersion() < ProtocolInfo::PROTOCOL_407 && substr($packet->buffer, 0, 2) === "\x21\x04") {
			$this->legacyNoisePacketLimiter->decrement();
			return;
		}

		//Every other packet is counted before any filtering takes place, so that packets which are dropped (e.g. before
		//login) or cancelled by plugins can't be used to bypass the rate limit.
		$this->gamePacketLimiter->decrement();

		if (
			!$this->player->loggedIn &&
			!$this->player->awaitingEncryptionHandshake &&
			!(
				$packet instanceof LoginPacket ||
				$packet instanceof RequestNetworkSettingsPacket
			)
		) { //Ignore any packets before login
			return;
		}

		$timings = Timings::getReceiveDataPacketTimings($packet);
		$timings->startTiming();

		try {
			if (!$packet->wasDecoded && $packet->mustBeDecoded()) { //Allow plugins to decode it
				$decodeTimings = Timings::getDecodeDataPacketTimings($packet);
				$decodeTimings->startTiming();
				try {
					try {
						$packet->decode();
					} catch (PacketDecodeException|BinaryDataException $e) {
						throw $e;
					} catch (\InvalidArgumentException|\UnexpectedValueException|\RuntimeException|\ValueError $e) {
						//invalid client data (bad enum values, broken skin JSON...) is a bad packet, not a server crash;
						//\Error subclasses are still reported, they mean a bug in the core
						throw PacketDecodeException::wrap($e, "Error decoding " . $packet->getName());
					}
					if (!$packet->feof() && !$packet->mayHaveUnreadBytes()) {
						$remains = substr($packet->buffer, $packet->offset);
						$this->server->getLogger()->debug("Still " . strlen($remains) . " bytes unread in " . $packet->getName() . ": 0x" . bin2hex($remains));
					}
				} finally {
					$decodeTimings->stopTiming();
				}
			}

			$ev = new DataPacketReceiveEvent($this->player, $packet);
			$ev->call();
			if ($ev->isCancelled() || !$packet->mustBeDecoded()) {
				return;
			}

			$handlerTimings = Timings::getHandleDataPacketTimings($packet);
			$handlerTimings->startTiming();
			try {
				if (!$packet->handle($this)) {
					$this->server->getLogger()->debug("Unhandled " . $packet->getName() . " received from " . $this->player->getName() . ": 0x" . bin2hex($packet->buffer));
				}
			} finally {
				$handlerTimings->stopTiming();
			}
		} finally {
			$timings->stopTiming();
		}
	}

	/**
	 * Called when a legacy noise packet is dropped before being decoded into a packet object.
	 * @internal
	 * @throws PacketHandlingException if the rate limit has been exceeded
	 */
	public function onLegacyNoisePacketDropped() : void{
		$this->legacyNoisePacketLimiter->decrement();
	}

	public function handleRequestNetworkSettings(RequestNetworkSettingsPacket $packet) : bool{
		return $this->player->handleRequestNetworkSettings($packet);
	}

	public function handleLogin(LoginPacket $packet) : bool{
		return $this->player->handleLogin($packet);
	}

	public function handleClientToServerHandshake(ClientToServerHandshakePacket $packet) : bool{
		return $this->player->onEncryptionHandshake();
	}

	public function handleResourcePackClientResponse(ResourcePackClientResponsePacket $packet) : bool{
		return $this->player->handleResourcePackClientResponse($packet);
	}

	public function handleText(TextPacket $packet) : bool{
		if ($packet->type === TextPacket::TYPE_CHAT) {
			return $this->player->chat($packet->message);
		}

		return false;
	}

	/*
	 * A similar code design was taken from PlayerAuthInputPacket
	 */
	public function handleMovePlayer(MovePlayerPacket $packet) : bool{
		$this->player->updateNextPosition($packet->position, $packet->yaw, $packet->yaw, $packet->pitch);

		return true;
	}

	public function handlePlayerAuthInput(PlayerAuthInputPacket $packet) : bool{
		return $this->player->handlePlayerAuthInput($packet);
	}

	public function handleLevelSoundEventPacketV1(LevelSoundEventPacketV1 $packet) : bool{
		return true; //useless leftover from 1.8
	}

	public function handleActorEvent(ActorEventPacket $packet) : bool{
		return $this->player->handleEntityEvent($packet);
	}

	public function handleInventoryTransaction(InventoryTransactionPacket $packet) : bool{
		return $this->player->handleInventoryTransaction($packet);
	}

	public function handleItemStackRequest(ItemStackRequestPacket $packet) : bool{
		return $this->player->handleItemStackRequest($packet);
	}

	public function handleMobEquipment(MobEquipmentPacket $packet) : bool{
		return $this->player->handleMobEquipment($packet);
	}

	public function handleMobArmorEquipment(MobArmorEquipmentPacket $packet) : bool{
		return true; //Not used
	}

	public function handleTickSync(TickSyncPacket $packet) : bool{
		return true; //Not used
	}

	public function handleEmoteList(EmoteListPacket $packet) : bool{
		return true; // Not used
	}

	public function handleEmote(EmotePacket $packet) : bool {
		return $this->player->handleEmote($packet);
	}

	public function handleInteract(InteractPacket $packet) : bool{
		return $this->player->handleInteract($packet);
	}

	public function handleBlockPickRequest(BlockPickRequestPacket $packet) : bool{
		return $this->player->pickBlock(new Vector3($packet->blockX, $packet->blockY, $packet->blockZ), $packet->addUserData);
	}

	public function handleActorPickRequest(ActorPickRequestPacket $packet) : bool{
		return $this->player->pickEntity($packet->entityUniqueId);
	}

	public function handlePlayerAction(PlayerActionPacket $packet) : bool{
		return $this->player->handlePlayerActionFromData($packet->action, new Vector3($packet->x, $packet->y, $packet->z), $packet->face);
	}

	public function handleActorFall(ActorFallPacket $packet) : bool
	{
		return true; //Not used
	}

	public function handleAnimate(AnimatePacket $packet) : bool{
		return $this->player->handleAnimate($packet);
	}

	public function handleRespawn(RespawnPacket $packet) : bool
	{
		return $this->player->handleRespawn($packet);
	}

	public function handleContainerClose(ContainerClosePacket $packet) : bool
	{
		return $this->player->handleContainerClose($packet);
	}

	public function handlePlayerHotbar(PlayerHotbarPacket $packet) : bool
	{
		return true; //this packet is useless
	}

	public function handleCraftingEvent(CraftingEventPacket $packet) : bool
	{
		if ($this->player->getProtocolVersion() < ProtocolInfo::PROTOCOL_407) {
			return $this->player->handleCraftingEvent($packet); // only <= 1.1
		}

		return true;
	}

	public function handleClientCacheStatus(ClientCacheStatusPacket $packet) : bool
	{
		return true; // Not used
	}

	public function handleAdventureSettings(AdventureSettingsPacket $packet) : bool
	{
		return $this->player->handleAdventureSettings($packet);
	}

	public function handleBlockActorData(BlockActorDataPacket $packet) : bool
	{
		return $this->player->handleBlockEntityData($packet);
	}

	public function handlePlayerInput(PlayerInputPacket $packet) : bool
	{
		$this->player->setMoveForward($packet->motionY);
		$this->player->setMoveStrafing($packet->motionX);

		return true;
	}

	public function handleRiderJump(RiderJumpPacket $packet) : bool
	{
		if ($this->player->isRiding()) {
			$horse = $this->player->getRidingEntity();
			if ($horse instanceof AbstractHorse) {
				$horse->setJumpPower($packet->jumpStrength);

				return true;
			}
		}
		return false;
	}

	public function handleSetPlayerGameType(SetPlayerGameTypePacket $packet) : bool
	{
		return $this->player->handleSetPlayerGameType($packet);
	}

	public function handleSpawnExperienceOrb(SpawnExperienceOrbPacket $packet) : bool{
		return false; //TODO
	}

	public function handleMapInfoRequest(MapInfoRequestPacket $packet) : bool{
		return $this->player->handleMapInfoRequest($packet);
	}

	public function handleRequestChunkRadius(RequestChunkRadiusPacket $packet) : bool{
		if (!$this->player->loginProcessed) {
			return false;
		}

		$this->player->setViewDistance($packet->radius);

		return true;
	}

	public function handleItemFrameDropItem(ItemFrameDropItemPacket $packet) : bool
	{
		return $this->player->handleItemFrameDropItem($packet);
	}

	public function handleBossEvent(BossEventPacket $packet) : bool
	{
		return false; //TODO
	}

	public function handleShowCredits(ShowCreditsPacket $packet) : bool
	{
		return false; //TODO: handle resume
	}

	public function handleCommandRequest(CommandRequestPacket $packet) : bool
	{
		return $this->player->chat($packet->command);
	}

	public function handleCommandBlockUpdate(CommandBlockUpdatePacket $packet) : bool
	{
		return false; //TODO
	}

	public function handleCommandStep(CommandStepPacket $packet) : bool
	{
		return $this->player->handleCommandStep($packet);
	}

	public function handleContainerSetSlot(ContainerSetSlotPacket $packet) : bool
	{
		return $this->player->handleContainerSetSlot($packet);
	}

	public function handleDropItem(DropItemPacket $packet) : bool
	{
		return $this->player->handleDropItem($packet);
	}

	public function handleRemoveBlock(RemoveBlockPacket $packet) : bool
	{
		if (!$this->player->spawned || !$this->player->isAlive()) {
			return true;
		}

		return $this->player->handleRemoveBlock($packet);
	}

	public function handleUseItem(UseItemPacket $packet) : bool{
		return $this->player->handleUseItem($packet);
	}

	public function handleResourcePackChunkRequest(ResourcePackChunkRequestPacket $packet) : bool
	{
		return $this->player->handleResourcePackChunkRequest($packet);
	}

	public function handlePlayerSkin(PlayerSkinPacket $packet) : bool
	{
		$skin = $packet->skin;

		if ($skin->getSerializedSkin()->getFullSkinId() === $this->lastRequestedFullSkinId) {
			//TODO: HACK! In 1.19.60, the client sends its skin back to us if we sent it a skin different from the one
			//it's using. We need to prevent this from causing a feedback loop.
			$this->server->getLogger()->debug("Refused duplicate skin change request");
			return true;
		}
		$this->lastRequestedFullSkinId = $skin->getSerializedSkin()->getFullSkinId();

		if (!$skin->isValid()) {
			return false;
		}

		$this->server->getLogger()->debug("Processing skin change request for " . $this->player->getName());
		return $this->player->changeSkin($skin, $packet->newSkinName, $packet->oldSkinName);
	}

	public function handleBookEdit(BookEditPacket $packet) : bool
	{
		return $this->player->handleBookEdit($packet);
	}

	public function handleLecternUpdate(LecternUpdatePacket $packet) : bool
	{
		return $this->player->handleLecternUpdate($packet);
	}

	public function handleModalFormResponse(ModalFormResponsePacket $packet) : bool{
		if($packet->cancelReason !== null){
			//TODO: make APIs for this to allow plugins to use this information
			return $this->player->onFormSubmit($packet->formId, null);
		}elseif($packet->formData !== null){
			if(strlen($packet->formData) > self::MAX_FORM_RESPONSE_SIZE){
				throw new PacketHandlingException("Form response data too large, refusing to decode (received" . strlen($packet->formData) . " bytes, max " . self::MAX_FORM_RESPONSE_SIZE . " bytes)");
			}
			if(!$this->player->hasPendingForm($packet->formId)){
				$this->server->getLogger()->debug("Got unexpected response for form $packet->formId from " . $this->player->getName());
				return false;
			}
			try{
				$responseData = json_decode($packet->formData, true, self::MAX_FORM_RESPONSE_DEPTH, JSON_THROW_ON_ERROR);
			}catch(\JsonException $e){
				throw PacketHandlingException::wrap($e, "Failed to decode form response data");
			}
			return $this->player->onFormSubmit($packet->formId, $responseData);
		}else{
			throw new PacketHandlingException("Expected either formData or cancelReason to be set in ModalFormResponsePacket");
		}
	}

	public function handleServerSettingsRequest(ServerSettingsRequestPacket $packet) : bool
	{
		return true; //TODO: GUI stuff
	}

	public function handleSetLocalPlayerAsInitialized(SetLocalPlayerAsInitializedPacket $packet) : bool
	{
		$this->player->doFirstSpawn();
		return true;
	}

	public function handleLevelSoundEvent(LevelSoundEventPacket $packet) : bool
	{
		/*
		* We don't handle this - all sounds are handled by the server now.
		* However, some plugins find this useful to detect events like left-click-air, which doesn't have any other
		* action bound to it.
		* In addition, we use this handler to silence debug noise, since this packet is frequently sent by the client.
		*/
		return true;
	}

	public function handleMoveActorAbsolute(MoveActorAbsolutePacket $packet) : bool
	{
		$target = $this->player->getServer()->findEntity($packet->entityRuntimeId);
		if ($target !== null) {
			$target->setClientPositionAndRotation($packet->position, $packet->yaw, $packet->pitch, 3, ($packet->flags & MoveActorAbsolutePacket::FLAG_TELEPORT) !== 0);
			//$target->onGround = ($packet->flags & MoveActorAbsolutePacket::FLAG_GROUND) !== 0;

			return true;
		}

		return false;
	}

	public function handleSetActorMotion(SetActorMotionPacket $packet) : bool
	{
		return true;
	}

	public function handleNetworkStackLatency(NetworkStackLatencyPacket $packet) : bool
	{
		return true; //TODO: implement this properly - this is here to silence debug spam from MCPE dev builds
	}

	public function handleRequestAbility(RequestAbilityPacket $packet) : bool
	{
		if ($packet->getAbilityId() === RequestAbilityPacket::ABILITY_FLYING) {
			$isFlying = $packet->getAbilityValue();
			if (!is_bool($isFlying)) {
				return false;
			}

			if ($isFlying !== $this->player->isFlying()) {
				if (!$this->player->toggleFlight($isFlying)) {
					$this->player->sendAbilities();
				}
			}

			return true;
		}

		return false;
	}

	public function handleSetPlayerFurnaceOptions(SetPlayerFurnaceOptionsPacket $packet) : bool
	{
		return true; //purely client-side UI state (1.26.50+), nothing to do
	}

	public function handlePacketViolationWarning(PacketViolationWarningPacket $packet) : bool
	{
		$this->player->getServer()->getLogger()->notice("PacketViolationWarning from {$this->player->getName()}: (type=" . $packet->getType() . ",severity=" . $packet->getSeverity() . ",packetId=" . $packet->getPacketId() . ",violationContext=" . $packet->getMessage() . ")");
		return true;
	}
}
