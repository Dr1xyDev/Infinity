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

use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\nbt\NetworkLittleEndianNBTStream;
use pocketmine\level\generator\vanilla\nbt\tag\CompoundTag;
use pocketmine\level\generator\vanilla\nbt\tag\ListTag;
use pocketmine\level\generator\vanilla\network\mcpe\NetworkSession;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\BlockPaletteEntry;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\ChatRestrictionLevel;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\EditorWorldType;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\EducationEditionOffer;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\EducationUriResource;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\Experiments;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\GameRuleType;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\GeneratorType;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\ItemTypeEntry;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\MultiplayerGameVisibility;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\NetworkPermissions;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\PlayerMovementSettings;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\ServerJoinInformation;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\ServerTelemetryData;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\SpawnSettings;
use pocketmine\level\generator\vanilla\utils\UUID;

use function count;

class StartGamePacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::START_GAME_PACKET;

	public int $entityUniqueId;
	public int $entityRuntimeId;
	public int $playerGamemode;

	public Vector3 $playerPosition;

	public float $pitch;
	public float $yaw;

	public int $seed;
	public SpawnSettings $spawnSettings;
	public int $generator = GeneratorType::OVERWORLD;
	public int $worldGamemode;
	public bool $hardcore = false;
	public int $difficulty;
	public int $spawnX = 0;
	public int $spawnY = 0;
	public int $spawnZ = 0;
	public bool $hasAchievementsDisabled = true;
	public bool $isEditorMode = false;
	public int $editorWorldType = EditorWorldType::NON_EDITOR;
	public bool $createdInEditorMode = false;
	public bool $exportedFromEditorMode = false;
	public int $time = -1;
	public int $eduEditionOffer = EducationEditionOffer::NONE;
	public bool $eduMode = false;
	public bool $hasEduFeaturesEnabled = false;
	public string $eduProductUUID = "";
	public float $rainLevel;
	public float $lightningLevel;
	public bool $hasConfirmedPlatformLockedContent = false;
	public bool $isMultiplayerGame = true;
	public bool $hasLANBroadcast = true;
	public bool $hasXboxLiveBroadcast = false;
	public int $xboxLiveBroadcastMode = MultiplayerGameVisibility::PUBLIC;
	public int $platformBroadcastMode = MultiplayerGameVisibility::PUBLIC;
	public bool $commandsEnabled;
	public bool $isTexturePacksRequired = true;
	public array $gameRules = [ //TODO: implement this
		"naturalregeneration" => [GameRuleType::BOOL, false, false] //Hack for client side regeneration
	];
	public Experiments $experiments;
	public bool $hasBonusChestEnabled = false;
	public bool $hasStartWithMapEnabled = false;
	public bool $hasTrustPlayersEnabled = false;
	public int $defaultPlayerPermission = PlayerPermissions::MEMBER; //TODO

	public int $serverChunkTickRadius = 4; //TODO (leave as default for now)

	public bool $hasPlatformBroadcast = false;
	public bool $xboxLiveBroadcastIntent = false;
	public bool $hasLockedBehaviorPack = false;
	public bool $hasLockedResourcePack = false;
	public bool $isFromLockedWorldTemplate = false;
	public bool $useMsaGamertagsOnly = false;
	public bool $isFromWorldTemplate = false;
	public bool $isWorldTemplateOptionLocked = false;
	public bool $onlySpawnV1Villagers = false;
	public bool $disablePersona = false;
	public bool $disableCustomSkins = false;
	public bool $muteEmoteAnnouncements = false;
	public string $vanillaVersion = ProtocolInfo::MINECRAFT_VERSION_NETWORK;
	public int $limitedWorldWidth = 0;
	public int $limitedWorldLength = 0;
	public bool $isNewNether = false;
	public ?EducationUriResource $eduSharedUriResource = null;
	public ?bool $experimentalGameplayOverride = null;
	public int $chatRestrictionLevel = ChatRestrictionLevel::NONE;
	public bool $disablePlayerInteractions = false;
	public int $serverEditorConnectionPolicy = 0;
	public bool $allowAnonymousBlockDropsInEditorWorlds = false;

	public string $levelId = ""; //base64 string, usually the same as world folder name in vanilla
	public string $worldName;
	public string $premiumWorldTemplateId = "";
	public bool $isTrial = false;
	public PlayerMovementSettings $playerMovementSettings;
	public bool $isMovementServerAuthoritative = false;
	public int $currentTick = 0; //only used if isTrial is true
	public int $enchantmentSeed = 0;
	public string $multiplayerCorrelationId = ""; //TODO: this should be filled with a UUID of some sort
	public bool $enableNewInventorySystem = true;
	public string $serverSoftwareVersion;
	public CompoundTag $playerActorProperties;
	public int $blockPaletteChecksum;
	public UUID $worldTemplateId;
	public bool $enableClientSideChunkGeneration = false;
	public bool $blockNetworkIdsAreHashes = false; //new in 1.19.80, possibly useful for multi version
	public bool $enableTickDeathSystems = false;
	public NetworkPermissions $networkPermissions;
	public bool $isLoggingChat = false;
	public ?ServerJoinInformation $serverJoinInformation;
	public ServerTelemetryData $serverTelemetryData;

	/** @var array|ListTag|null ["name" (string), "data" (int16), "legacy_id" (int16)] */
	public array|ListTag|null $blockTable = null;
	/** @var BlockPaletteEntry[] */
	public array $blockPalette = [];
	/**
	 * @var ItemTypeEntry[]
	 * @phpstan-var list<ItemTypeEntry>
	 */
	public array $itemTable = [];
	/** @var int[]  */
	public array $legacyItemTable = [];

	public string $blockEncodePalette;

	protected function decodePayload() : void
	{
		$this->entityUniqueId = $this->getEntityUniqueId();
		$this->entityRuntimeId = $this->getEntityRuntimeId();
		$this->playerGamemode = $this->getVarInt();

		$this->playerPosition = $this->getVector3();

		$this->pitch = $this->getLFloat();
		$this->yaw = $this->getLFloat();

		//Level settings
		if ($this->protocol >= ProtocolInfo::PROTOCOL_503) {
			$this->seed = $this->getLLong();
		} else {
			$this->seed = $this->getVarInt();
		}
		$this->spawnSettings = SpawnSettings::read($this);
		$this->generator = $this->getVarInt();
		$this->worldGamemode = $this->getVarInt();
		if ($this->protocol >= ProtocolInfo::PROTOCOL_671) {
			$this->hardcore = $this->getBool();
		}
		$this->difficulty = $this->getVarInt();
		$this->getBlockPosition($this->spawnX, $this->spawnY, $this->spawnZ);
		$this->hasAchievementsDisabled = $this->getBool();
		if ($this->protocol >= ProtocolInfo::PROTOCOL_534) {
			if ($this->protocol >= ProtocolInfo::PROTOCOL_618) {
				$this->editorWorldType = $this->getVarInt();
			} else {
				$this->isEditorMode = $this->getBool();
			}
			if ($this->protocol >= ProtocolInfo::PROTOCOL_582) {
				$this->createdInEditorMode = $this->getBool();
				$this->exportedFromEditorMode = $this->getBool();
			}
		}
		$this->time = $this->getVarInt();
		if ($this->protocol >= ProtocolInfo::PROTOCOL_407) {
			$this->eduEditionOffer = $this->getVarInt();
		} else {
			$this->eduMode = $this->getBool();
		}
		if ($this->protocol >= ProtocolInfo::PROTOCOL_407) {
			$this->hasEduFeaturesEnabled = $this->getBool();
			$this->eduProductUUID = $this->getString();
		}
		$this->rainLevel = $this->getLFloat();
		$this->lightningLevel = $this->getLFloat();
		if ($this->protocol >= ProtocolInfo::PROTOCOL_407) {
			$this->hasConfirmedPlatformLockedContent = $this->getBool();
			$this->isMultiplayerGame = $this->getBool();
			$this->hasLANBroadcast = $this->getBool();
			$this->xboxLiveBroadcastMode = $this->getVarInt();
			$this->platformBroadcastMode = $this->getVarInt();
		}
		$this->commandsEnabled = $this->getBool();
		$this->isTexturePacksRequired = $this->getBool();
		$this->gameRules = $this->getGameRules(false);
		if ($this->protocol >= ProtocolInfo::PROTOCOL_407) {
			if ($this->protocol >= ProtocolInfo::PROTOCOL_419) {
				$this->experiments = Experiments::read($this);
			}
			$this->hasBonusChestEnabled = $this->getBool();
			$this->hasStartWithMapEnabled = $this->getBool();
			$this->defaultPlayerPermission = $this->getVarInt();
			$this->serverChunkTickRadius = $this->getLInt();
			$this->hasLockedBehaviorPack = $this->getBool();
			$this->hasLockedResourcePack = $this->getBool();
			$this->isFromLockedWorldTemplate = $this->getBool();
			$this->useMsaGamertagsOnly = $this->getBool();
			$this->isFromWorldTemplate = $this->getBool();
			$this->isWorldTemplateOptionLocked = $this->getBool();
			$this->onlySpawnV1Villagers = $this->getBool();
			if ($this->protocol >= ProtocolInfo::PROTOCOL_544) {
				$this->disablePersona = $this->getBool();
				$this->disableCustomSkins = $this->getBool();
				if ($this->protocol >= ProtocolInfo::PROTOCOL_567) {
					$this->muteEmoteAnnouncements = $this->getBool();
				}
			}

			$this->vanillaVersion = $this->getString();
			$this->limitedWorldWidth = $this->getLInt();
			$this->limitedWorldLength = $this->getLInt();
			$this->isNewNether = $this->getBool();
			if ($this->protocol >= ProtocolInfo::PROTOCOL_465) {
				$this->eduSharedUriResource = EducationUriResource::read($this);
			}
			if ($this->getBool()) {
				$this->experimentalGameplayOverride = $this->getBool();
			} else {
				$this->experimentalGameplayOverride = null;
			}
			if ($this->protocol >= ProtocolInfo::PROTOCOL_544) {
				$this->chatRestrictionLevel = $this->getByte();
				$this->disablePlayerInteractions = $this->getBool();
				if ($this->protocol >= ProtocolInfo::PROTOCOL_1001) {
					$this->serverEditorConnectionPolicy = $this->getVarInt();
					$this->allowAnonymousBlockDropsInEditorWorlds = $this->getBool();
				}
				if ($this->protocol >= ProtocolInfo::PROTOCOL_685 && $this->protocol < ProtocolInfo::PROTOCOL_924) {
					$this->serverTelemetryData = ServerTelemetryData::read($this);
				}
			}
		}

		$this->levelId = $this->getString();
		$this->worldName = $this->getString();
		$this->premiumWorldTemplateId = $this->getString();
		$this->isTrial = $this->getBool();
		if ($this->protocol >= ProtocolInfo::PROTOCOL_407) {
			$this->playerMovementSettings = PlayerMovementSettings::read($this);
		}

		$this->currentTick = $this->getLLong();

		if ($this->protocol >= ProtocolInfo::PROTOCOL_407) {
			$this->enchantmentSeed = $this->getVarInt();

			if ($this->protocol >= ProtocolInfo::PROTOCOL_419) {
				$this->blockPalette = [];
				for ($i = 0, $len = $this->getUnsignedVarInt(); $i < $len; ++$i) {
					$blockName = $this->getString();
					$state = $this->getNbtCompoundRoot();
					$this->blockPalette[] = new BlockPaletteEntry($blockName, $state);
				}
			} else {
				$blockTable = (new NetworkLittleEndianNBTStream())->read($this->buffer, false, $this->offset, 512);
				if (!($blockTable instanceof ListTag)) {
					throw new PacketDecodeException("Wrong block table root NBT tag type");
				}
				$this->blockTable = $blockTable;
			}

			if ($this->protocol < ProtocolInfo::PROTOCOL_776) {
				if ($this->protocol >= ProtocolInfo::PROTOCOL_419) {
					$this->itemTable = [];
					$emptyNBT = new CompoundTag();
					for ($i = 0, $count = $this->getUnsignedVarInt(); $i < $count; ++$i) {
						$stringId = $this->getString();
						$numericId = $this->getSignedLShort();
						$isComponentBased = $this->getBool();

						$this->itemTable[] = new ItemTypeEntry($stringId, $numericId, $isComponentBased, 0, $emptyNBT);
					}
				} else {
					$this->legacyItemTable = [];
					for ($i = 0, $count = $this->getUnsignedVarInt(); $i < $count; ++$i) {
						$stringId = $this->getString();
						$legacyId = $this->getSignedLShort();

						$this->legacyItemTable[$stringId] = $legacyId;
					}
				}
			}

			$this->multiplayerCorrelationId = $this->getString();
			$this->enableNewInventorySystem = $this->getBool();
			if ($this->protocol >= ProtocolInfo::PROTOCOL_440) {
				$this->serverSoftwareVersion = $this->getString();
				if ($this->protocol >= ProtocolInfo::PROTOCOL_527) {
					$this->playerActorProperties = $this->getNbtCompoundRoot();
				}
				if ($this->protocol >= ProtocolInfo::PROTOCOL_475) {
					$this->blockPaletteChecksum = $this->getLLong();
					if ($this->protocol >= ProtocolInfo::PROTOCOL_527) {
						$this->worldTemplateId = $this->getUUID();
						if ($this->protocol >= ProtocolInfo::PROTOCOL_544) {
							$this->enableClientSideChunkGeneration = $this->getBool();
							if ($this->protocol >= ProtocolInfo::PROTOCOL_582) {
								$this->blockNetworkIdsAreHashes = $this->getBool();
								if ($this->protocol >= ProtocolInfo::PROTOCOL_589) {
									if ($this->protocol >= ProtocolInfo::PROTOCOL_827 && $this->protocol < ProtocolInfo::PROTOCOL_897) {
										$this->enableTickDeathSystems = $this->getBool();
									}

									$this->networkPermissions = NetworkPermissions::decode($this);
									if ($this->protocol >= ProtocolInfo::PROTOCOL_924) {
										if ($this->protocol >= ProtocolInfo::PROTOCOL_1001 && $this->protocol < ProtocolInfo::PROTOCOL_2168) {
											$this->isLoggingChat = $this->getBool();
										}
										$this->serverJoinInformation = $this->getOptional(fn() => ServerJoinInformation::read($this));
										$this->serverTelemetryData = ServerTelemetryData::read($this);
									}
								}
							}
						}
					}
				}
			}
		}
	}

	protected function encodePayload() : void
	{
		$this->putEntityUniqueId($this->entityUniqueId);
		$this->putEntityRuntimeId($this->entityRuntimeId);
		$this->putVarInt($this->playerGamemode);

		$this->putVector3($this->playerPosition);

		$this->putLFloat($this->pitch);
		$this->putLFloat($this->yaw);

		//Level settings
		if ($this->protocol >= ProtocolInfo::PROTOCOL_503) {
			$this->putLLong($this->seed);
		} else {
			$this->putVarInt($this->seed);
		}
		$this->spawnSettings->write($this);
		$this->putVarInt($this->generator);
		$this->putVarInt($this->worldGamemode);
		if ($this->protocol >= ProtocolInfo::PROTOCOL_671) {
			$this->putBool($this->hardcore);
		}
		$this->putVarInt($this->difficulty);
		$this->putBlockPosition($this->spawnX, $this->spawnY, $this->spawnZ);
		$this->putBool($this->hasAchievementsDisabled);
		if ($this->protocol >= ProtocolInfo::PROTOCOL_534) {
			if ($this->protocol >= ProtocolInfo::PROTOCOL_618) {
				$this->putVarInt($this->editorWorldType);
			} else {
				$this->putBool($this->isEditorMode);
			}
			if ($this->protocol >= ProtocolInfo::PROTOCOL_582) {
				$this->putBool($this->createdInEditorMode);
				$this->putBool($this->exportedFromEditorMode);
			}
		}
		$this->putVarInt($this->time);
		if ($this->protocol >= ProtocolInfo::PROTOCOL_407) {
			$this->putVarInt($this->eduEditionOffer);
		} else {
			$this->putBool($this->eduMode);
		}
		if ($this->protocol >= ProtocolInfo::PROTOCOL_407) {
			$this->putBool($this->hasEduFeaturesEnabled);
			$this->putString($this->eduProductUUID);
		}
		$this->putLFloat($this->rainLevel);
		$this->putLFloat($this->lightningLevel);
		if ($this->protocol >= ProtocolInfo::PROTOCOL_407) {
			$this->putBool($this->hasConfirmedPlatformLockedContent);
			$this->putBool($this->isMultiplayerGame);
			$this->putBool($this->hasLANBroadcast);
			$this->putVarInt($this->xboxLiveBroadcastMode);
			$this->putVarInt($this->platformBroadcastMode);
		}
		$this->putBool($this->commandsEnabled);
		$this->putBool($this->isTexturePacksRequired);
		$this->putGameRules($this->gameRules, true);
		if ($this->protocol >= ProtocolInfo::PROTOCOL_407) {
			if ($this->protocol >= ProtocolInfo::PROTOCOL_419) {
				$this->experiments->write($this);
			}
			$this->putBool($this->hasBonusChestEnabled);
			$this->putBool($this->hasStartWithMapEnabled);
			$this->putVarInt($this->defaultPlayerPermission);
			$this->putLInt($this->serverChunkTickRadius);
			$this->putBool($this->hasLockedBehaviorPack);
			$this->putBool($this->hasLockedResourcePack);
			$this->putBool($this->isFromLockedWorldTemplate);
			$this->putBool($this->useMsaGamertagsOnly);
			$this->putBool($this->isFromWorldTemplate);
			$this->putBool($this->isWorldTemplateOptionLocked);
			$this->putBool($this->onlySpawnV1Villagers);
			if ($this->protocol >= ProtocolInfo::PROTOCOL_544) {
				$this->putBool($this->disablePersona);
				$this->putBool($this->disableCustomSkins);
				if ($this->protocol >= ProtocolInfo::PROTOCOL_567) {
					$this->putBool($this->muteEmoteAnnouncements);
				}
			}

			$this->putString($this->vanillaVersion);
			$this->putLInt($this->limitedWorldWidth);
			$this->putLInt($this->limitedWorldLength);
			$this->putBool($this->isNewNether);
			if ($this->protocol >= ProtocolInfo::PROTOCOL_465) {
				($this->eduSharedUriResource ?? new EducationUriResource("", ""))->write($this);
			}
			$this->putBool($this->experimentalGameplayOverride !== null);
			if ($this->experimentalGameplayOverride !== null) {
				$this->putBool($this->experimentalGameplayOverride);
			}
			if ($this->protocol >= ProtocolInfo::PROTOCOL_544) {
				$this->putByte($this->chatRestrictionLevel);
				$this->putBool($this->disablePlayerInteractions);
				if ($this->protocol >= ProtocolInfo::PROTOCOL_1001) {
					$this->putVarInt($this->serverEditorConnectionPolicy);
					$this->putBool($this->allowAnonymousBlockDropsInEditorWorlds);
				}
				if ($this->protocol >= ProtocolInfo::PROTOCOL_685 && $this->protocol < ProtocolInfo::PROTOCOL_924) {
					$this->serverTelemetryData->write($this);
				}
			}
		}

		$this->putString($this->levelId);
		$this->putString($this->worldName);
		$this->putString($this->premiumWorldTemplateId);
		$this->putBool($this->isTrial);
		if ($this->protocol >= ProtocolInfo::PROTOCOL_407) {
			$this->playerMovementSettings->write($this);
		}

		$this->putLLong($this->currentTick);

		if ($this->protocol >= ProtocolInfo::PROTOCOL_407) {
			$this->putVarInt($this->enchantmentSeed);

			if ($this->protocol < ProtocolInfo::PROTOCOL_419) {
				$this->put($this->blockEncodePalette);
			} else {
				$this->putUnsignedVarInt(count($this->blockPalette));
				$nbtWriter = new NetworkLittleEndianNBTStream();
				foreach ($this->blockPalette as $entry) {
					$this->putString($entry->getName());
					$this->put($nbtWriter->write($entry->getStates()));
				}
			}

			if ($this->protocol < ProtocolInfo::PROTOCOL_776) {
				if ($this->protocol >= ProtocolInfo::PROTOCOL_419) {
					$this->putUnsignedVarInt(count($this->itemTable));
					foreach ($this->itemTable as $entry) {
						$this->putString($entry->getStringId());
						$this->putLShort($entry->getNumericId());
						$this->putBool($entry->isComponentBased());
					}
				} else {
					$this->putUnsignedVarInt(count($this->legacyItemTable));
					foreach ($this->legacyItemTable as $name => $legacyId) {
						$this->putString($name);
						$this->putLShort($legacyId);
					}
				}
			}

			$this->putString($this->multiplayerCorrelationId);
			$this->putBool($this->enableNewInventorySystem);
			if ($this->protocol >= ProtocolInfo::PROTOCOL_440) {
				$this->putString($this->serverSoftwareVersion);
				if ($this->protocol >= ProtocolInfo::PROTOCOL_527) {
					$this->put((new NetworkLittleEndianNBTStream())->write($this->playerActorProperties));
				}
				if ($this->protocol >= ProtocolInfo::PROTOCOL_475) {
					$this->putLLong($this->blockPaletteChecksum);
					if ($this->protocol >= ProtocolInfo::PROTOCOL_527) {
						$this->putUUID($this->worldTemplateId);
						if ($this->protocol >= ProtocolInfo::PROTOCOL_544) {
							$this->putBool($this->enableClientSideChunkGeneration);
							if ($this->protocol >= ProtocolInfo::PROTOCOL_582) {
								$this->putBool($this->blockNetworkIdsAreHashes);
								if ($this->protocol >= ProtocolInfo::PROTOCOL_589) {
									if ($this->protocol >= ProtocolInfo::PROTOCOL_827 && $this->protocol < ProtocolInfo::PROTOCOL_897) {
										$this->putBool($this->enableTickDeathSystems);
									}

									$this->networkPermissions->encode($this);
									if ($this->protocol >= ProtocolInfo::PROTOCOL_924) {
										if ($this->protocol >= ProtocolInfo::PROTOCOL_1001 && $this->protocol < ProtocolInfo::PROTOCOL_2168) { //removed in 1.26.40
											$this->putBool($this->isLoggingChat);
										}
										$this->putOptional($this->serverJoinInformation, fn(ServerJoinInformation $info) => $info->write($this));
										$this->serverTelemetryData->write($this);
									}
								}
							}
						}
					}
				}
			}
		}
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleStartGame($this);
	}
}
