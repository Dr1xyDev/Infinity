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

use pocketmine\level\generator\vanilla\network\mcpe\convert\PacketIdTranslator;
use pocketmine\level\generator\vanilla\utils\Binary;
use SplFixedArray;

class PacketPool
{
	/** @var SplFixedArray<DataPacket> */
	protected static $pool = null;

	public static function init() : void
	{
		self::registerPacket(new LoginPacket());
		self::registerPacket(new PlayStatusPacket());
		self::registerPacket(new ServerToClientHandshakePacket());
		self::registerPacket(new ClientToServerHandshakePacket());
		self::registerPacket(new DisconnectPacket());
		self::registerPacket(new ResourcePacksInfoPacket());
		self::registerPacket(new ResourcePackStackPacket());
		self::registerPacket(new ResourcePackClientResponsePacket());
		self::registerPacket(new TextPacket());
		self::registerPacket(new SetTimePacket());
		self::registerPacket(new StartGamePacket());
		self::registerPacket(new AddPlayerPacket());
		self::registerPacket(new AddActorPacket());
		self::registerPacket(new RemoveActorPacket());
		self::registerPacket(new AddItemActorPacket());
		self::registerPacket(new ServerPlayerPostMovePositionPacket());
		self::registerPacket(new TakeItemActorPacket());
		self::registerPacket(new MoveActorAbsolutePacket());
		self::registerPacket(new MovePlayerPacket());
		self::registerPacket(new RiderJumpPacket());
		self::registerPacket(new UpdateBlockPacket());
		self::registerPacket(new AddPaintingPacket());
		self::registerPacket(new TickSyncPacket());
		self::registerPacket(new LevelSoundEventPacketV1());
		self::registerPacket(new LevelEventPacket());
		self::registerPacket(new BlockEventPacket());
		self::registerPacket(new ActorEventPacket());
		self::registerPacket(new MobEffectPacket());
		self::registerPacket(new UpdateAttributesPacket());
		self::registerPacket(new InventoryTransactionPacket());
		self::registerPacket(new MobEquipmentPacket());
		self::registerPacket(new MobArmorEquipmentPacket());
		self::registerPacket(new InteractPacket());
		self::registerPacket(new BlockPickRequestPacket());
		self::registerPacket(new ActorPickRequestPacket());
		self::registerPacket(new PlayerActionPacket());
		self::registerPacket(new ActorFallPacket());
		self::registerPacket(new HurtArmorPacket());
		self::registerPacket(new SetActorDataPacket());
		self::registerPacket(new SetActorMotionPacket());
		self::registerPacket(new SetActorLinkPacket());
		self::registerPacket(new SetHealthPacket());
		self::registerPacket(new SetSpawnPositionPacket());
		self::registerPacket(new AnimatePacket());
		self::registerPacket(new RespawnPacket());
		self::registerPacket(new ContainerOpenPacket());
		self::registerPacket(new ContainerClosePacket());
		self::registerPacket(new PlayerHotbarPacket());
		self::registerPacket(new InventoryContentPacket());
		self::registerPacket(new InventorySlotPacket());
		self::registerPacket(new ContainerSetDataPacket());
		self::registerPacket(new CraftingDataPacket());
		self::registerPacket(new CraftingEventPacket());
		self::registerPacket(new GuiDataPickItemPacket());
		self::registerPacket(new AdventureSettingsPacket());
		self::registerPacket(new BlockActorDataPacket());
		self::registerPacket(new PlayerInputPacket());
		self::registerPacket(new LevelChunkPacket());
		self::registerPacket(new SetCommandsEnabledPacket());
		self::registerPacket(new SetDifficultyPacket());
		self::registerPacket(new ChangeDimensionPacket());
		self::registerPacket(new SetPlayerGameTypePacket());
		self::registerPacket(new PlayerListPacket());
		self::registerPacket(new SimpleEventPacket());
		self::registerPacket(new LegacyTelemetryEventPacket());
		self::registerPacket(new SpawnExperienceOrbPacket());
		self::registerPacket(new ClientboundMapItemDataPacket());
		self::registerPacket(new MapInfoRequestPacket());
		self::registerPacket(new RequestChunkRadiusPacket());
		self::registerPacket(new ChunkRadiusUpdatedPacket());
		self::registerPacket(new ItemFrameDropItemPacket());
		self::registerPacket(new GameRulesChangedPacket());
		self::registerPacket(new CameraPacket());
		self::registerPacket(new BossEventPacket());
		self::registerPacket(new ShowCreditsPacket());
		self::registerPacket(new AvailableCommandsPacket());
		self::registerPacket(new CommandRequestPacket());
		self::registerPacket(new CommandBlockUpdatePacket());
		self::registerPacket(new CommandOutputPacket());
		self::registerPacket(new UpdateTradePacket());
		self::registerPacket(new UpdateEquipPacket());
		self::registerPacket(new ResourcePackDataInfoPacket());
		self::registerPacket(new ResourcePackChunkDataPacket());
		self::registerPacket(new ResourcePackChunkRequestPacket());
		self::registerPacket(new TransferPacket());
		self::registerPacket(new PlaySoundPacket());
		self::registerPacket(new StopSoundPacket());
		self::registerPacket(new SetTitlePacket());
		self::registerPacket(new AddBehaviorTreePacket());
		self::registerPacket(new StructureBlockUpdatePacket());
		self::registerPacket(new ShowStoreOfferPacket());
		self::registerPacket(new PurchaseReceiptPacket());
		self::registerPacket(new PlayerSkinPacket());
		self::registerPacket(new SubClientLoginPacket());
		self::registerPacket(new AutomationClientConnectPacket());
		self::registerPacket(new SetLastHurtByPacket());
		self::registerPacket(new BookEditPacket());
		self::registerPacket(new NpcRequestPacket());
		self::registerPacket(new PhotoTransferPacket());
		self::registerPacket(new ModalFormRequestPacket());
		self::registerPacket(new ModalFormResponsePacket());
		self::registerPacket(new ServerSettingsRequestPacket());
		self::registerPacket(new ServerSettingsResponsePacket());
		self::registerPacket(new ShowProfilePacket());
		self::registerPacket(new SetDefaultGameTypePacket());
		self::registerPacket(new RemoveObjectivePacket());
		self::registerPacket(new SetDisplayObjectivePacket());
		self::registerPacket(new SetScorePacket());
		self::registerPacket(new LabTablePacket());
		self::registerPacket(new UpdateBlockSyncedPacket());
		self::registerPacket(new MoveActorDeltaPacket());
		self::registerPacket(new MovementEffectPacket());
		self::registerPacket(new ClientMovementPredictionSyncPacket());
		self::registerPacket(new SetScoreboardIdentityPacket());
		self::registerPacket(new SetLocalPlayerAsInitializedPacket());
		self::registerPacket(new SetMovementAuthorityPacket());
		self::registerPacket(new UpdateSoftEnumPacket());
		self::registerPacket(new NetworkStackLatencyPacket());
		self::registerPacket(new ScriptCustomEventPacket());
		self::registerPacket(new SpawnParticleEffectPacket());
		self::registerPacket(new AvailableActorIdentifiersPacket());
		self::registerPacket(new LevelSoundEventPacketV2());
		self::registerPacket(new NetworkChunkPublisherUpdatePacket());
		self::registerPacket(new BiomeDefinitionListPacket());
		self::registerPacket(new LevelSoundEventPacket());
		self::registerPacket(new LevelEventGenericPacket());
		self::registerPacket(new LecternUpdatePacket());
		self::registerPacket(new VideoStreamConnectPacket());
		self::registerPacket(new ClientCacheStatusPacket());
		self::registerPacket(new OnScreenTextureAnimationPacket());
		self::registerPacket(new MapCreateLockedCopyPacket());
		self::registerPacket(new StructureTemplateDataRequestPacket());
		self::registerPacket(new StructureTemplateDataResponsePacket());
		self::registerPacket(new UpdateBlockPropertiesPacket());
		self::registerPacket(new ClientCacheBlobStatusPacket());
		self::registerPacket(new ClientCacheMissResponsePacket());
		self::registerPacket(new EducationSettingsPacket());
		self::registerPacket(new EmotePacket());
		self::registerPacket(new MultiplayerSettingsPacket());
		self::registerPacket(new SettingsCommandPacket());
		self::registerPacket(new AnvilDamagePacket());
		self::registerPacket(new CompletedUsingItemPacket());
		self::registerPacket(new NetworkSettingsPacket());
		self::registerPacket(new PlayerAuthInputPacket());
		self::registerPacket(new CreativeContentPacket());
		self::registerPacket(new PlayerEnchantOptionsPacket());
		self::registerPacket(new ItemStackRequestPacket());
		self::registerPacket(new ItemStackResponsePacket());
		self::registerPacket(new PlayerArmorDamagePacket());
		self::registerPacket(new CodeBuilderPacket());
		self::registerPacket(new UpdatePlayerGameTypePacket());
		self::registerPacket(new EmoteListPacket());
		self::registerPacket(new PositionTrackingDBServerBroadcastPacket());
		self::registerPacket(new PositionTrackingDBClientRequestPacket());
		self::registerPacket(new DebugInfoPacket());
		self::registerPacket(new PacketViolationWarningPacket());
		self::registerPacket(new MotionPredictionHintsPacket());
		self::registerPacket(new AnimateEntityPacket());
		self::registerPacket(new CameraShakePacket());
		self::registerPacket(new PlayerFogPacket());
		self::registerPacket(new CorrectPlayerMovePredictionPacket());
		self::registerPacket(new ItemComponentPacket());
		self::registerPacket(new ItemRegistryPacket());
		self::registerPacket(new FilterTextPacket());
		self::registerPacket(new ClientboundDebugRendererPacket());
		self::registerPacket(new SyncActorPropertyPacket());
		self::registerPacket(new AddVolumeEntityPacket());
		self::registerPacket(new RemoveVolumeEntityPacket());
		self::registerPacket(new SimulationTypePacket());
		self::registerPacket(new NpcDialoguePacket());
		self::registerPacket(new EduUriResourcePacket());
		self::registerPacket(new CreatePhotoPacket());
		self::registerPacket(new UpdateSubChunkBlocksPacket());
		self::registerPacket(new PhotoInfoRequestPacket());
		self::registerPacket(new PlayerStartItemCooldownPacket());
		self::registerPacket(new ScriptMessagePacket());
		self::registerPacket(new CodeBuilderSourcePacket());
		self::registerPacket(new TickingAreasLoadStatusPacket());
		self::registerPacket(new DimensionDataPacket());
		self::registerPacket(new AgentActionEventPacket());
		self::registerPacket(new ChangeMobPropertyPacket());
		self::registerPacket(new LessonProgressPacket());
		self::registerPacket(new RequestAbilityPacket());
		self::registerPacket(new RequestPermissionsPacket());
		self::registerPacket(new ToastRequestPacket());
		self::registerPacket(new UpdateAbilitiesPacket());
		self::registerPacket(new UpdateAdventureSettingsPacket());
		self::registerPacket(new DeathInfoPacket());
		self::registerPacket(new EditorNetworkPacket());
		self::registerPacket(new FeatureRegistryPacket());
		self::registerPacket(new ServerStatsPacket());
		self::registerPacket(new RequestNetworkSettingsPacket());
		self::registerPacket(new GameTestRequestPacket());
		self::registerPacket(new GameTestResultsPacket());
		self::registerPacket(new UpdateClientInputLocksPacket());
		self::registerPacket(new ClientCheatAbilityPacket());
		self::registerPacket(new CameraPresetsPacket());
		self::registerPacket(new UnlockedRecipesPacket());
		self::registerPacket(new CameraInstructionPacket());
		self::registerPacket(new CompressedBiomeDefinitionListPacket());
		self::registerPacket(new TrimDataPacket());
		self::registerPacket(new OpenSignPacket());
		self::registerPacket(new AgentAnimationPacket());
		self::registerPacket(new RefreshEntitlementsPacket());
		self::registerPacket(new PlayerToggleCrafterSlotRequestPacket());
		self::registerPacket(new SetPlayerInventoryOptionsPacket());
		self::registerPacket(new SetHudPacket());
		self::registerPacket(new AwardAchievementPacket());
		self::registerPacket(new CameraAimAssistPacket());
		self::registerPacket(new CameraAimAssistPresetsPacket());
		self::registerPacket(new ContainerRegistryCleanupPacket());
		self::registerPacket(new CurrentStructureFeaturePacket());
		self::registerPacket(new JigsawStructureDataPacket());
		self::registerPacket(new ServerboundDiagnosticsPacket());
		self::registerPacket(new ServerboundLoadingScreenPacket());
		self::registerPacket(new CameraAimAssistInstructionPacket());
		self::registerPacket(new UpdateClientOptionsPacket());
		self::registerPacket(new PlayerUpdateEntityOverridesPacket());
		self::registerPacket(new PlayerVideoCapturePacket());
		self::registerPacket(new PlayerLocationPacket());
		self::registerPacket(new ClientboundControlSchemeSetPacket());
		self::registerPacket(new DebugDrawePrimitiveShapesPacket());
		self::registerPacket(new ServerboundPackSettingChangePacket());
		self::registerPacket(new SubChunkRequestPacket());
		self::registerPacket(new SubChunkPacket());
		self::registerPacket(new ClientboundDataStorePacket());
		self::registerPacket(new GraphicsOverrideParameterPacket());
		self::registerPacket(new ServerboundDataStorePacket());
		self::registerPacket(new CameraAimAssistActorPriorityPacket());
		self::registerPacket(new CameraSplinePacket());
		self::registerPacket(new ClientboundDataDrivenUICloseAllScreensPacket());
		self::registerPacket(new ClientboundDataDrivenUIReloadPacket());
		self::registerPacket(new ClientboundDataDrivenUIShowScreenPacket());
		self::registerPacket(new ClientboundTextureShiftPacket());
		self::registerPacket(new VoxelShapesPacket());
		self::registerPacket(new ResourcePacksReadyForValidationPacket());
		self::registerPacket(new LocatorBarPacket());
		self::registerPacket(new PartyChangedPacket());
		self::registerPacket(new ServerboundDataDrivenScreenClosedPacket());
		self::registerPacket(new SyncWorldClocksPacket());
		self::registerPacket(new ClientboundAttributeLayerSyncPacket());
		self::registerPacket(new ServerStoreInfoPacket());
		self::registerPacket(new ServerPresenceInfoPacket());
		self::registerPacket(new ClientboundUpdateSoundDataPacket());
		self::registerPacket(new PartyDestinationCookieResponsePacket());
		self::registerPacket(new SendPartyDestinationCookiePacket());
		self::registerPacket(new SetPlayerFurnaceOptionsPacket());
		self::registerPacket(new RecordStartedPacket());
		self::registerPacket(new ClientboundCloseFormPacket());
		// ExplodePacket - TickSyncPacket
		self::registerPacket(new ExplodePacket());
		// 1.1
		self::registerPacket(new RemoveBlockPacket());
		self::registerPacket(new UseItemPacket());
		self::registerPacket(new DropItemPacket());
		self::registerPacket(new ContainerSetContentPacket());
		self::registerPacket(new ContainerSetSlotPacket());
		self::registerPacket(new CommandStepPacket());
	}

	public static function registerPacket(DataPacket $packet) : void
	{
		self::$pool[$packet->pid()] = clone $packet;
	}

	public static function getPacketById(int $pid, int $playerProtocol) : DataPacket
	{
		$packetId = PacketIdTranslator::getInstance()->fromNetworkId($playerProtocol, $pid);
		return isset(self::$pool[$packetId]) ? clone self::$pool[$packetId] : new UnknownPacket();
	}

	public static function getPacket(string $buffer, int $playerProtocol) : DataPacket
	{
		$offset = 0;
		$pk = self::getPacketById(Binary::readUnsignedVarInt($buffer, $offset) & DataPacket::PID_MASK, $playerProtocol);
		$pk->setBuffer($buffer, $offset);

		return $pk;
	}
}
