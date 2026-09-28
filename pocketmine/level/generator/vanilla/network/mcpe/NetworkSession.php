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

use pocketmine\level\generator\vanilla\network\mcpe\protocol\ActorEventPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ActorFallPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ActorPickRequestPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\AddActorPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\AddBehaviorTreePacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\AddItemActorPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\AddPaintingPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\AddPlayerPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\AddVolumeEntityPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\AdventureSettingsPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\AgentActionEventPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\AgentAnimationPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\AnimateEntityPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\AnimatePacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\AnvilDamagePacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\AutomationClientConnectPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\AvailableActorIdentifiersPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\AwardAchievementPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\BiomeDefinitionListPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\BlockActorDataPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\BlockEventPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\BlockPickRequestPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\BookEditPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\BossEventPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\CameraAimAssistActorPriorityPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\CameraAimAssistInstructionPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\CameraAimAssistPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\CameraAimAssistPresetsPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\CameraInstructionPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\CameraPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\CameraPresetsPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\CameraShakePacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\CameraSplinePacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ChangeDimensionPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ChangeMobPropertyPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ChunkRadiusUpdatedPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ClientboundAttributeLayerSyncPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ClientboundCloseFormPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ClientboundControlSchemeSetPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ClientboundDataDrivenUICloseAllScreensPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ClientboundDataDrivenUIReloadPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ClientboundDataDrivenUIShowScreenPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ClientboundDataStorePacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ClientboundDebugRendererPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ClientboundMapItemDataPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ClientboundTextureShiftPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ClientboundUpdateSoundDataPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ClientCacheBlobStatusPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ClientCacheMissResponsePacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ClientCacheStatusPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ClientCheatAbilityPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ClientMovementPredictionSyncPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ClientToServerHandshakePacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\CodeBuilderPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\CodeBuilderSourcePacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\CommandBlockUpdatePacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\CommandOutputPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\CommandRequestPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\CommandStepPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\CompletedUsingItemPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\CompressedBiomeDefinitionListPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ContainerClosePacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ContainerOpenPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ContainerRegistryCleanupPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ContainerSetContentPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ContainerSetDataPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ContainerSetSlotPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\CorrectPlayerMovePredictionPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\CraftingDataPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\CraftingEventPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\CreatePhotoPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\CreativeContentPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\CurrentStructureFeaturePacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\DataPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\DeathInfoPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\DebugDrawePrimitiveShapesPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\DebugInfoPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\DimensionDataPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\DisconnectPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\DropItemPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\EditorNetworkPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\EducationSettingsPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\EduUriResourcePacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\EmoteListPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\EmotePacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ExplodePacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\FeatureRegistryPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\FilterTextPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\GameRulesChangedPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\GameTestRequestPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\GameTestResultsPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\GraphicsOverrideParameterPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\GuiDataPickItemPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\HurtArmorPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\InteractPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\InventoryContentPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\InventorySlotPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ItemComponentPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ItemFrameDropItemPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ItemRegistryPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ItemStackRequestPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ItemStackResponsePacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\JigsawStructureDataPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\LabTablePacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\LecternUpdatePacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\LegacyTelemetryEventPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\LessonProgressPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\LevelChunkPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\LevelEventGenericPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\LevelEventPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\LevelSoundEventPacketV1;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\LevelSoundEventPacketV2;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\LocatorBarPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\LoginPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\MapCreateLockedCopyPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\MapInfoRequestPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\MobArmorEquipmentPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\MobEffectPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\MobEquipmentPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ModalFormResponsePacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\MotionPredictionHintsPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\MoveActorAbsolutePacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\MoveActorDeltaPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\MovementEffectPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\MovePlayerPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\MultiplayerSettingsPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\NetworkChunkPublisherUpdatePacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\NetworkSettingsPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\NetworkStackLatencyPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\NpcDialoguePacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\NpcRequestPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\OnScreenTextureAnimationPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\OpenSignPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\PacketViolationWarningPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\PartyChangedPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\PartyDestinationCookieResponsePacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\PhotoInfoRequestPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\PhotoTransferPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\PlayerActionPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\PlayerArmorDamagePacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\PlayerAuthInputPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\PlayerEnchantOptionsPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\PlayerFogPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\PlayerHotbarPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\PlayerInputPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\PlayerListPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\PlayerLocationPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\PlayerSkinPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\PlayerStartItemCooldownPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\PlayerToggleCrafterSlotRequestPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\PlayerUpdateEntityOverridesPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\PlayerVideoCapturePacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\PlayStatusPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\PositionTrackingDBClientRequestPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\PositionTrackingDBServerBroadcastPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ProtocolInfo;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\PurchaseReceiptPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\RefreshEntitlementsPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\RecordStartedPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\RemoveActorPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\RemoveBlockPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\RemoveObjectivePacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\RemoveVolumeEntityPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\RequestAbilityPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\RequestChunkRadiusPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\RequestNetworkSettingsPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\RequestPermissionsPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ResourcePackChunkDataPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ResourcePackChunkRequestPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ResourcePackClientResponsePacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ResourcePackDataInfoPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ResourcePacksInfoPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ResourcePacksReadyForValidationPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ResourcePackStackPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\RespawnPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\RiderJumpPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ScriptCustomEventPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ScriptMessagePacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\SendPartyDestinationCookiePacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ServerboundDataDrivenScreenClosedPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ServerboundDataStorePacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ServerboundDiagnosticsPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ServerboundLoadingScreenPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ServerPlayerPostMovePositionPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ServerPresenceInfoPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ServerSettingsRequestPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ServerSettingsResponsePacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ServerStatsPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ServerStoreInfoPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ServerToClientHandshakePacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\SetActorDataPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\SetActorLinkPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\SetActorMotionPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\SetCommandsEnabledPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\SetDefaultGameTypePacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\SetDifficultyPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\SetDisplayObjectivePacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\SetHealthPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\SetHudPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\SetLastHurtByPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\SetLocalPlayerAsInitializedPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\SetMovementAuthorityPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\SetPlayerGameTypePacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\SetPlayerFurnaceOptionsPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\SetPlayerInventoryOptionsPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\SetScoreboardIdentityPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\SetScorePacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\SetSpawnPositionPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\SetTimePacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\SettingsCommandPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\SetTitlePacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ShowCreditsPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ShowProfilePacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ShowStoreOfferPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\SimpleEventPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\SimulationTypePacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\SpawnExperienceOrbPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\SpawnParticleEffectPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\StartGamePacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\StopSoundPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\StructureBlockUpdatePacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\StructureTemplateDataRequestPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\StructureTemplateDataResponsePacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\SubChunkPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\SubChunkRequestPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\SubClientLoginPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\SyncActorPropertyPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\SyncWorldClocksPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\TakeItemActorPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\TextPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\TickingAreasLoadStatusPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\TickSyncPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ToastRequestPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\TransferPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\TrimDataPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\UnlockedRecipesPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\UpdateAbilitiesPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\UpdateAdventureSettingsPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\UpdateAttributesPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\UpdateBlockPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\UpdateBlockPropertiesPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\UpdateBlockSyncedPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\UpdateClientInputLocksPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\UpdateClientOptionsPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\UpdateEquipPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\UpdatePlayerGameTypePacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\UpdateSoftEnumPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\UpdateSubChunkBlocksPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\UpdateTradePacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\UseItemPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\VideoStreamConnectPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\VoxelShapesPacket;

abstract class NetworkSession
{
	abstract public function handleDataPacket(DataPacket $packet);

	public function isCompressionEnabled() : bool
	{
		return true;
	}

	public function getProtocol() : int
	{
		return ProtocolInfo::CURRENT_PROTOCOL;
	}

	public function handleRequestNetworkSettings(RequestNetworkSettingsPacket $packet) : bool
	{
		return false;
	}

	public function handleLogin(LoginPacket $packet) : bool
	{
		return false;
	}

	public function handlePlayStatus(PlayStatusPacket $packet) : bool
	{
		return false;
	}

	public function handleServerToClientHandshake(ServerToClientHandshakePacket $packet) : bool
	{
		return false;
	}

	public function handleClientToServerHandshake(ClientToServerHandshakePacket $packet) : bool
	{
		return false;
	}

	public function handleDisconnect(DisconnectPacket $packet) : bool
	{
		return false;
	}

	public function handleResourcePacksInfo(ResourcePacksInfoPacket $packet) : bool
	{
		return false;
	}

	public function handleResourcePackStack(ResourcePackStackPacket $packet) : bool
	{
		return false;
	}

	public function handleResourcePackClientResponse(ResourcePackClientResponsePacket $packet) : bool
	{
		return false;
	}

	public function handleText(TextPacket $packet) : bool
	{
		return false;
	}

	public function handleSetTime(SetTimePacket $packet) : bool
	{
		return false;
	}

	public function handleStartGame(StartGamePacket $packet) : bool
	{
		return false;
	}

	public function handleAddPlayer(AddPlayerPacket $packet) : bool
	{
		return false;
	}

	public function handleAddActor(AddActorPacket $packet) : bool
	{
		return false;
	}

	public function handleRemoveActor(RemoveActorPacket $packet) : bool
	{
		return false;
	}

	public function handleAddItemActor(AddItemActorPacket $packet) : bool
	{
		return false;
	}

	public function handleTakeItemActor(TakeItemActorPacket $packet) : bool
	{
		return false;
	}

	public function handleMoveActorAbsolute(MoveActorAbsolutePacket $packet) : bool
	{
		return false;
	}

	public function handleMovePlayer(MovePlayerPacket $packet) : bool
	{
		return false;
	}

	public function handleUpdateBlock(UpdateBlockPacket $packet) : bool
	{
		return false;
	}

	public function handleAddPainting(AddPaintingPacket $packet) : bool
	{
		return false;
	}

	public function handleExplode(ExplodePacket $packet) : bool
	{
		return false;
	}

	public function handleTickSync(TickSyncPacket $packet) : bool
	{
		return false;
	}

	public function handleLevelEvent(LevelEventPacket $packet) : bool
	{
		return false;
	}

	public function handleBlockEvent(BlockEventPacket $packet) : bool
	{
		return false;
	}

	public function handleActorEvent(ActorEventPacket $packet) : bool
	{
		return false;
	}

	public function handleMobEffect(MobEffectPacket $packet) : bool
	{
		return false;
	}

	public function handleUpdateAdventureSettings(UpdateAdventureSettingsPacket $packet) : bool
	{
		return false;
	}

	public function handleUpdateAbilities(UpdateAbilitiesPacket $packet) : bool
	{
		return false;
	}

	public function handleUnlockedRecipes(UnlockedRecipesPacket $packet) : bool
	{
		return false;
	}

	public function handleUpdateAttributes(UpdateAttributesPacket $packet) : bool
	{
		return false;
	}

	public function handleInventoryTransaction(InventoryTransactionPacket $packet) : bool
	{
		return false;
	}

	public function handleMobEquipment(MobEquipmentPacket $packet) : bool
	{
		return false;
	}

	public function handleMobArmorEquipment(MobArmorEquipmentPacket $packet) : bool
	{
		return false;
	}

	public function handleInteract(InteractPacket $packet) : bool
	{
		return false;
	}

	public function handleBlockPickRequest(BlockPickRequestPacket $packet) : bool
	{
		return false;
	}

	public function handlePlayerAction(PlayerActionPacket $packet) : bool
	{
		return false;
	}

	public function handleSetActorData(SetActorDataPacket $packet) : bool
	{
		return false;
	}

	public function handleSetActorMotion(SetActorMotionPacket $packet) : bool
	{
		return false;
	}

	public function handleSetActorLink(SetActorLinkPacket $packet) : bool
	{
		return false;
	}

	public function handleSetSpawnPosition(SetSpawnPositionPacket $packet) : bool
	{
		return false;
	}

	public function handleAnimate(AnimatePacket $packet) : bool
	{
		return false;
	}

	public function handleRespawn(RespawnPacket $packet) : bool
	{
		return false;
	}

	public function handleContainerOpen(ContainerOpenPacket $packet) : bool
	{
		return false;
	}

	public function handleContainerClose(ContainerClosePacket $packet) : bool
	{
		return false;
	}

	public function handlePlayerHotbar(PlayerHotbarPacket $packet) : bool
	{
		return false;
	}

	public function handleInventoryContent(InventoryContentPacket $packet) : bool
	{
		return false;
	}

	public function handleInventorySlot(InventorySlotPacket $packet) : bool
	{
		return false;
	}

	public function handleContainerSetData(ContainerSetDataPacket $packet) : bool
	{
		return false;
	}

	public function handleContainerSetSlot(ContainerSetSlotPacket $packet) : bool
	{
		return false;
	}

	public function handleContainerSetContent(ContainerSetContentPacket $packet) : bool
	{
		return false;
	}

	public function handleContainerRegistryCleanup(ContainerRegistryCleanupPacket $packet) : bool
	{
		return false;
	}

	public function handleDropItem(DropItemPacket $packet) : bool
	{
		return false;
	}

	public function handleRemoveBlock(RemoveBlockPacket $packet) : bool
	{
		return false;
	}

	public function handleUseItem(UseItemPacket $packet) : bool
	{
		return false;
	}

	public function handleCraftingData(CraftingDataPacket $packet) : bool
	{
		return false;
	}

	public function handleCraftingEvent(CraftingEventPacket $packet) : bool
	{
		return false;
	}

	public function handleAdventureSettings(AdventureSettingsPacket $packet) : bool
	{
		return false;
	}

	public function handleBlockActorData(BlockActorDataPacket $packet) : bool
	{
		return false;
	}

	public function handlePlayerInput(PlayerInputPacket $packet) : bool
	{
		return false;
	}

	public function handleLevelChunk(LevelChunkPacket $packet) : bool
	{
		return false;
	}

	public function handleSetCommandsEnabled(SetCommandsEnabledPacket $packet) : bool
	{
		return false;
	}

	public function handleSetDifficulty(SetDifficultyPacket $packet) : bool
	{
		return false;
	}

	public function handleChangeDimension(ChangeDimensionPacket $packet) : bool
	{
		return false;
	}

	public function handleSetPlayerGameType(SetPlayerGameTypePacket $packet) : bool
	{
		return false;
	}

	public function handlePlayerList(PlayerListPacket $packet) : bool
	{
		return false;
	}

	public function handlePlayerLocation(PlayerLocationPacket $packet) : bool
	{
		return false;
	}

	public function handleSpawnExperienceOrb(SpawnExperienceOrbPacket $packet) : bool
	{
		return false;
	}

	public function handleClientboundMapItemData(ClientboundMapItemDataPacket $packet) : bool
	{
		return false;
	}

	public function handleMapInfoRequest(MapInfoRequestPacket $packet) : bool
	{
		return false;
	}

	public function handleRequestChunkRadius(RequestChunkRadiusPacket $packet) : bool
	{
		return false;
	}

	public function handleChunkRadiusUpdated(ChunkRadiusUpdatedPacket $packet) : bool
	{
		return false;
	}

	public function handleItemFrameDropItem(ItemFrameDropItemPacket $packet) : bool
	{
		return false;
	}

	public function handleItemRegistry(ItemRegistryPacket $packet) : bool
	{
		return false;
	}

	public function handleGameRulesChanged(GameRulesChangedPacket $packet) : bool
	{
		return false;
	}

	public function handleBossEvent(BossEventPacket $packet) : bool
	{
		return false;
	}

	public function handleCameraAimAssistInstruction(CameraAimAssistInstructionPacket $packet) : bool
	{
		return false;
	}

	public function handleAvailableCommands(AvailableCommandsPacket $packet) : bool
	{
		return false;
	}

	public function handleCommandRequest(CommandRequestPacket $packet) : bool
	{
		return false;
	}

	public function handleCommandStep(CommandStepPacket $packet) : bool
	{
		return false;
	}

	public function handleResourcePackDataInfo(ResourcePackDataInfoPacket $packet) : bool
	{
		return false;
	}

	public function handleResourcePackChunkData(ResourcePackChunkDataPacket $packet) : bool
	{
		return false;
	}

	public function handleResourcePackChunkRequest(ResourcePackChunkRequestPacket $packet) : bool
	{
		return false;
	}

	public function handleTransfer(TransferPacket $packet) : bool
	{
		return false;
	}

	public function handlePlaySound(PlaySoundPacket $packet) : bool
	{
		return false;
	}

	public function handleSetTitle(SetTitlePacket $packet) : bool
	{
		return false;
	}

	public function handleShowStoreOffer(ShowStoreOfferPacket $packet) : bool
	{
		return false;
	}

	public function handlePurchaseReceipt(PurchaseReceiptPacket $packet) : bool
	{
		return false;
	}

	public function handlePlayerSkin(PlayerSkinPacket $packet) : bool
	{
		return false;
	}

	public function handleSubClientLogin(SubClientLoginPacket $packet) : bool
	{
		return false;
	}

	public function handleBookEdit(BookEditPacket $packet) : bool
	{
		return false;
	}

	public function handleModalFormRequest(ModalFormRequestPacket $packet) : bool
	{
		return false;
	}

	public function handleModalFormResponse(ModalFormResponsePacket $packet) : bool
	{
		return false;
	}

	public function handleServerSettingsRequest(ServerSettingsRequestPacket $packet) : bool
	{
		return false;
	}

	public function handleServerSettingsResponse(ServerSettingsResponsePacket $packet) : bool
	{
		return false;
	}

	public function handleShowProfile(ShowProfilePacket $packet) : bool
	{
		return false;
	}

	public function handleSetDefaultGameType(SetDefaultGameTypePacket $packet) : bool
	{
		return false;
	}

	public function handleRemoveObjective(RemoveObjectivePacket $packet) : bool
	{
		return false;
	}

	public function handleSetDisplayObjective(SetDisplayObjectivePacket $packet) : bool
	{
		return false;
	}

	public function handleSetScore(SetScorePacket $packet) : bool
	{
		return false;
	}

	public function handleSetScoreboardIdentity(SetScoreboardIdentityPacket $packet) : bool
	{
		return false;
	}

	public function handleSetLocalPlayerAsInitialized(SetLocalPlayerAsInitializedPacket $packet) : bool
	{
		return false;
	}

	public function handleSetMovementAuthority(SetMovementAuthorityPacket $packet) : bool
	{
		return false;
	}

	public function handleSpawnParticleEffect(SpawnParticleEffectPacket $packet) : bool
	{
		return false;
	}

	public function handleAvailableActorIdentifiers(AvailableActorIdentifiersPacket $packet) : bool
	{
		return false;
	}

	public function handleNetworkChunkPublisherUpdate(NetworkChunkPublisherUpdatePacket $packet) : bool
	{
		return false;
	}

	public function handleBiomeDefinitionList(BiomeDefinitionListPacket $packet) : bool
	{
		return false;
	}

	public function handleLevelSoundEvent(LevelSoundEventPacket $packet) : bool
	{
		return false;
	}

	public function handleLevelSoundEventPacketV2(LevelSoundEventPacketV2 $packet) : bool
	{
		return false;
	}

	public function handleLabTable(LabTablePacket $packet) : bool
	{
		return false;
	}

	public function handleUpdateBlockSynced(UpdateBlockSyncedPacket $packet) : bool
	{
		return false;
	}

	public function handleMoveActorDelta(MoveActorDeltaPacket $packet) : bool
	{
		return false;
	}

	public function handleNpcRequest(NpcRequestPacket $packet) : bool
	{
		return false;
	}

	public function handlePhotoTransfer(PhotoTransferPacket $packet) : bool
	{
		return false;
	}

	public function handleAutomationClientConnect(AutomationClientConnectPacket $packet) : bool
	{
		return false;
	}

	public function handleSetLastHurtBy(SetLastHurtByPacket $packet) : bool
	{
		return false;
	}

	public function handleAddBehaviorTree(AddBehaviorTreePacket $packet) : bool
	{
		return false;
	}

	public function handleStructureBlockUpdate(StructureBlockUpdatePacket $packet) : bool
	{
		return false;
	}

	public function handleStopSound(StopSoundPacket $packet) : bool
	{
		return false;
	}

	public function handleCommandBlockUpdate(CommandBlockUpdatePacket $packet) : bool
	{
		return false;
	}

	public function handleCommandOutput(CommandOutputPacket $packet) : bool
	{
		return false;
	}

	public function handleUpdateTrade(UpdateTradePacket $packet) : bool
	{
		return false;
	}

	public function handleUpdateEquip(UpdateEquipPacket $packet) : bool
	{
		return false;
	}

	public function handleUpdateClientOptions(UpdateClientOptionsPacket $packet) : bool
	{
		return false;
	}

	public function handlePlayerVideoCapturePacket(PlayerVideoCapturePacket $packet) : bool
	{
		return false;
	}

	public function handlePlayerUpdateEntityOverridesPacket(PlayerUpdateEntityOverridesPacket $packet) : bool
	{
		return false;
	}

	public function handleShowCredits(ShowCreditsPacket $packet) : bool
	{
		return false;
	}

	public function handleCamera(CameraPacket $packet) : bool
	{
		return false;
	}

	public function handleCameraInstruction(CameraInstructionPacket $packet) : bool
	{
		return false;
	}

	public function handleSimpleEvent(SimpleEventPacket $packet) : bool
	{
		return false;
	}

	public function handleLegacyTelemetryEvent(LegacyTelemetryEventPacket $packet) : bool
	{
		return false;
	}

	public function handleGuiDataPickItem(GuiDataPickItemPacket $packet) : bool
	{
		return false;
	}

	public function handleSetHealth(SetHealthPacket $packet) : bool
	{
		return false;
	}

	public function handleActorFall(ActorFallPacket $packet) : bool
	{
		return false;
	}

	public function handleHurtArmor(HurtArmorPacket $packet) : bool
	{
		return false;
	}

	public function handleActorPickRequest(ActorPickRequestPacket $packet) : bool
	{
		return false;
	}

	public function handleLevelSoundEventPacketV1(LevelSoundEventPacketV1 $packet) : bool
	{
		return false;
	}

	public function handleRiderJump(RiderJumpPacket $packet) : bool
	{
		return false;
	}

	public function handleUpdateSoftEnum(UpdateSoftEnumPacket $packet) : bool
	{
		return false;
	}

	public function handleNetworkStackLatency(NetworkStackLatencyPacket $packet) : bool
	{
		return false;
	}

	public function handleScriptCustomEvent(ScriptCustomEventPacket $packet) : bool
	{
		return false;
	}

	public function handleLevelEventGeneric(LevelEventGenericPacket $packet) : bool
	{
		return false;
	}

	public function handleLecternUpdate(LecternUpdatePacket $packet) : bool
	{
		return false;
	}

	public function handleVideoStreamConnect(VideoStreamConnectPacket $packet) : bool
	{
		return false;
	}

	public function handleClientCacheStatus(ClientCacheStatusPacket $packet) : bool
	{
		return false;
	}

	public function handleOnScreenTextureAnimation(OnScreenTextureAnimationPacket $packet) : bool
	{
		return false;
	}

	public function handleMapCreateLockedCopy(MapCreateLockedCopyPacket $packet) : bool
	{
		return false;
	}

	public function handleStructureTemplateDataRequest(StructureTemplateDataRequestPacket $packet) : bool
	{
		return false;
	}

	public function handleStructureTemplateDataResponse(StructureTemplateDataResponsePacket $packet) : bool
	{
		return false;
	}

	public function handleSubChunkRequest(SubChunkRequestPacket $packet) : bool
	{
		return false;
	}

	public function handleSubChunk(SubChunkPacket $packet) : bool
	{
		return false;
	}

	public function handleUpdateBlockProperties(UpdateBlockPropertiesPacket $packet) : bool
	{
		return false;
	}

	public function handleClientCacheBlobStatus(ClientCacheBlobStatusPacket $packet) : bool
	{
		return false;
	}

	public function handleClientCacheMissResponse(ClientCacheMissResponsePacket $packet) : bool
	{
		return false;
	}

	public function handleEducationSettings(EducationSettingsPacket $packet) : bool
	{
		return false;
	}

	public function handleEditorNetwork(EditorNetworkPacket $packet) : bool
	{
		return false;
	}

	public function handleEmote(EmotePacket $packet) : bool
	{
		return false;
	}

	public function handleMultiplayerSettings(MultiplayerSettingsPacket $packet) : bool
	{
		return false;
	}

	public function handleSettingsCommand(SettingsCommandPacket $packet) : bool
	{
		return false;
	}

	public function handleAnvilDamage(AnvilDamagePacket $packet) : bool
	{
		return false;
	}

	public function handleCompletedUsingItem(CompletedUsingItemPacket $packet) : bool
	{
		return false;
	}

	public function handleNetworkSettings(NetworkSettingsPacket $packet) : bool
	{
		return false;
	}

	public function handlePlayerAuthInput(PlayerAuthInputPacket $packet) : bool
	{
		return false;
	}

	public function handleCreativeContent(CreativeContentPacket $packet) : bool
	{
		return false;
	}

	public function handlePlayerEnchantOptions(PlayerEnchantOptionsPacket $packet) : bool
	{
		return false;
	}

	public function handleItemStackRequest(ItemStackRequestPacket $packet) : bool
	{
		return false;
	}

	public function handleItemStackResponse(ItemStackResponsePacket $packet) : bool
	{
		return false;
	}

	public function handleJigsawStructureData(JigsawStructureDataPacket $packet) : bool
	{
		return false;
	}

	public function handleCurrentStructureFeature(CurrentStructureFeaturePacket $packet) : bool
	{
		return false;
	}

	public function handlePlayerArmorDamage(PlayerArmorDamagePacket $packet) : bool
	{
		return false;
	}

	public function handleCodeBuilder(CodeBuilderPacket $packet) : bool
	{
		return false;
	}

	public function handleUpdatePlayerGameType(UpdatePlayerGameTypePacket $packet) : bool
	{
		return false;
	}

	public function handleEmoteList(EmoteListPacket $packet) : bool
	{
		return false;
	}

	public function handlePositionTrackingDBServerBroadcast(PositionTrackingDBServerBroadcastPacket $packet) : bool
	{
		return false;
	}

	public function handlePositionTrackingDBClientRequest(PositionTrackingDBClientRequestPacket $packet) : bool
	{
		return false;
	}

	public function handleDebugInfo(DebugInfoPacket $packet) : bool
	{
		return false;
	}

	public function handleDeathInfo(DeathInfoPacket $packet) : bool
	{
		return false;
	}

	public function handlePacketViolationWarning(PacketViolationWarningPacket $packet) : bool
	{
		return false;
	}

	public function handleMotionPredictionHints(MotionPredictionHintsPacket $packet) : bool
	{
		return false;
	}

	public function handleAnimateEntity(AnimateEntityPacket $packet) : bool
	{
		return false;
	}

	public function handleCameraShake(CameraShakePacket $packet) : bool
	{
		return false;
	}

	public function handleCameraPresets(CameraPresetsPacket $packet) : bool
	{
		return false;
	}

	public function handlePlayerFog(PlayerFogPacket $packet) : bool
	{
		return false;
	}

	public function handleCorrectPlayerMovePrediction(CorrectPlayerMovePredictionPacket $packet) : bool
	{
		return false;
	}

	public function handleItemComponent(ItemComponentPacket $packet) : bool
	{
		return false;
	}

	public function handleFilterText(FilterTextPacket $packet) : bool
	{
		return false;
	}

	public function handleClientboundDebugRenderer(ClientboundDebugRendererPacket $packet) : bool
	{
		return false;
	}

	public function handleSyncActorProperty(SyncActorPropertyPacket $packet) : bool
	{
		return false;
	}

	public function handleAddVolumeEntity(AddVolumeEntityPacket $packet) : bool
	{
		return false;
	}

	public function handleRemoveVolumeEntity(RemoveVolumeEntityPacket $packet) : bool
	{
		return false;
	}

	public function handleSimulationType(SimulationTypePacket $packet) : bool
	{
		return false;
	}

	public function handleNpcDialogue(NpcDialoguePacket $packet) : bool
	{
		return false;
	}

	public function handleEduUriResource(EduUriResourcePacket $packet) : bool
	{
		return false;
	}

	public function handleCreatePhoto(CreatePhotoPacket $packet) : bool
	{
		return false;
	}

	public function handleUpdateSubChunkBlocks(UpdateSubChunkBlocksPacket $packet) : bool
	{
		return false;
	}

	public function handlePhotoInfoRequest(PhotoInfoRequestPacket $packet) : bool
	{
		return false;
	}

	public function handleCodeBuilderSource(CodeBuilderSourcePacket $packet) : bool
	{
		return false;
	}

	public function handlePlayerStartItemCooldown(PlayerStartItemCooldownPacket $packet) : bool
	{
		return false;
	}

	public function handleScriptMessage(ScriptMessagePacket $packet) : bool
	{
		return false;
	}

	public function handleServerboundDiagnostics(ServerboundDiagnosticsPacket $packet) : bool
	{
		return false;
	}

	public function handleServerboundLoadingScreen(ServerboundLoadingScreenPacket $packet) : bool
	{
		return false;
	}

	public function handleDimensionData(DimensionDataPacket $packet) : bool
	{
		return false;
	}

	public function handleTickingAreasLoadStatus(TickingAreasLoadStatusPacket $packet) : bool
	{
		return false;
	}

	public function handleAgentActionEvent(AgentActionEventPacket $packet) : bool
	{
		return false;
	}

	public function handleChangeMobProperty(ChangeMobPropertyPacket $packet) : bool
	{
		return false;
	}

	public function handleLessonProgress(LessonProgressPacket $packet) : bool
	{
		return false;
	}

	public function handleRequestAbility(RequestAbilityPacket $packet) : bool
	{
		return false;
	}

	public function handleRequestPermissions(RequestPermissionsPacket $packet) : bool
	{
		return false;
	}

	public function handleToastRequest(ToastRequestPacket $packet) : bool
	{
		return false;
	}

	public function handleFeatureRegistry(FeatureRegistryPacket $packet) : bool
	{
		return false;
	}

	public function handleGameTestRequest(GameTestRequestPacket $packet) : bool
	{
		return false;
	}

	public function handleCameraAimAssist(CameraAimAssistPacket $packet) : bool
	{
		return false;
	}

	public function handleCameraAimAssistPresets(CameraAimAssistPresetsPacket $packet) : bool
	{
		return false;
	}

	public function handleGameTestResults(GameTestResultsPacket $packet) : bool
	{
		return false;
	}

	public function handleClientboundDataStore(ClientboundDataStorePacket $packet) : bool
	{
		return false;
	}

	public function handleGraphicsOverrideParameter(GraphicsOverrideParameterPacket $packet) : bool
	{
		return false;
	}

	public function handleServerboundDataStore(ServerboundDataStorePacket $packet) : bool
	{
		return false;
	}

	public function handleServerStats(ServerStatsPacket $packet) : bool
	{
		return false;
	}

	public function handleServerPresenceInfo(ServerPresenceInfoPacket $packet) : bool
	{
		return false;
	}

	public function handleUpdateClientInputLocks(UpdateClientInputLocksPacket $packet) : bool
	{
		return false;
	}

	public function handleClientCheatAbility(ClientCheatAbilityPacket $packet) : bool
	{
		return false;
	}

	public function handleCompressedBiomeDefinitionList(CompressedBiomeDefinitionListPacket $packet) : bool
	{
		return false;
	}

	public function handleTrimData(TrimDataPacket $packet) : bool
	{
		return false;
	}

	public function handleOpenSign(OpenSignPacket $packet) : bool
	{
		return false;
	}

	public function handleAgentAnimation(AgentAnimationPacket $packet) : bool
	{
		return false;
	}

	public function handleRefreshEntitlements(RefreshEntitlementsPacket $packet) : bool
	{
		return false;
	}

	public function handlePlayerToggleCrafterSlotRequest(PlayerToggleCrafterSlotRequestPacket $packet) : bool
	{
		return false;
	}

	public function handleSetPlayerInventoryOptions(SetPlayerInventoryOptionsPacket $packet) : bool
	{
		return false;
	}

	public function handleServerPlayerPostMovePosition(ServerPlayerPostMovePositionPacket $packet) : bool
	{
		return false;
	}

	public function handleDebugDrawer(DebugDrawePrimitiveShapesPacket $packet) : bool
	{
		return false;
	}

	public function handleSetHud(SetHudPacket $packet) : bool
	{
		return false;
	}

	public function handleAwardAchievement(AwardAchievementPacket $packet) : bool
	{
		return false;
	}

	public function handleClientboundCloseForm(ClientboundCloseFormPacket $packet) : bool
	{
		return false;
	}

	public function handleMovementEffect(MovementEffectPacket $packet) : bool
	{
		return false;
	}

	public function handleClientMovementPredictionSync(ClientMovementPredictionSyncPacket $packet) : bool
	{
		return false;
	}

	public function handleClientboundControlSchemeSet(ClientboundControlSchemeSetPacket $packet) : bool
	{
		return false;
	}

	public function handleCameraAimAssistActorPriority(CameraAimAssistActorPriorityPacket $packet) : bool
	{
		return false;
	}

	public function handleCameraSpline(CameraSplinePacket $packet) : bool
	{
		return false;
	}

	public function handleClientboundDataDrivenUICloseAllScreens(ClientboundDataDrivenUICloseAllScreensPacket $packet) : bool
	{
		return false;
	}

	public function handleClientboundDataDrivenUIReload(ClientboundDataDrivenUIReloadPacket $packet) : bool
	{
		return false;
	}

	public function handleClientboundDataDrivenUIShowScreen(ClientboundDataDrivenUIShowScreenPacket $packet) : bool
	{
		return false;
	}

	public function handleClientboundTextureShift(ClientboundTextureShiftPacket $packet) : bool
	{
		return false;
	}

	public function handleResourcePacksReadyForValidation(ResourcePacksReadyForValidationPacket $packet) : bool
	{
		return false;
	}

	public function handleLocatorBar(LocatorBarPacket $packet) : bool
	{
		return false;
	}

	public function handlePartyChanged(PartyChangedPacket $packet) : bool
	{
		return false;
	}

	public function handleServerboundDataDrivenScreenClosed(ServerboundDataDrivenScreenClosedPacket $packet) : bool
	{
		return false;
	}

	public function handleSyncWorldClocks(SyncWorldClocksPacket $packet) : bool
	{
		return false;
	}

	public function handleClientboundAttributeLayerSync(ClientboundAttributeLayerSyncPacket $packet) : bool
	{
		return false;
	}

	public function handleVoxelShapes(VoxelShapesPacket $packet) : bool
	{
		return false;
	}

	public function handleServerStoreInfo(ServerStoreInfoPacket $packet) : bool
	{
		return false;
	}

	public function handleSendPartyDestinationCookie(SendPartyDestinationCookiePacket $packet) : bool
	{
		return false;
	}

	public function handlePartyDestinationCookieResponse(PartyDestinationCookieResponsePacket $packet) : bool
	{
		return false;
	}

	public function handleClientboundUpdateSoundData(ClientboundUpdateSoundDataPacket $packet) : bool
	{
		return false;
	}

	public function handleSetPlayerFurnaceOptions(SetPlayerFurnaceOptionsPacket $packet) : bool
	{
		return false;
	}

	public function handleRecordStarted(RecordStartedPacket $packet) : bool
	{
		return false;
	}
}
