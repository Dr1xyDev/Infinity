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

/**
 * Version numbers and packet IDs for the current Minecraft PE protocol
 */
interface ProtocolInfo
{
	/**
	 * NOTE TO DEVELOPERS
	 * Do not waste your time or ours submitting pull requests changing game and/or protocol version numbers.
	 * Pull requests changing game and/or protocol version numbers will be closed.
	 *
	 * This file is generated automatically, do not edit it manually.
	 */

	/** Actual Minecraft: PE protocol version */
	public const CURRENT_PROTOCOL = self::PROTOCOL_2193;

	/** Current Minecraft PE version reported by the server. This is usually the earliest currently supported version. */
	public const MINECRAFT_VERSION = "1.26.50";

	/** Version number sent to clients in ping responses. */
	public const MINECRAFT_VERSION_NETWORK = "1.26.50";

	public const ACCEPTED_PROTOCOLS = [
		ProtocolInfo::PROTOCOL_113,
		ProtocolInfo::PROTOCOL_407,
		ProtocolInfo::PROTOCOL_408,
		ProtocolInfo::PROTOCOL_419,
		ProtocolInfo::PROTOCOL_422,
		ProtocolInfo::PROTOCOL_428,
		ProtocolInfo::PROTOCOL_431,
		ProtocolInfo::PROTOCOL_440,
		ProtocolInfo::PROTOCOL_448,
		ProtocolInfo::PROTOCOL_465,
		ProtocolInfo::PROTOCOL_471,
		ProtocolInfo::PROTOCOL_475,
		ProtocolInfo::PROTOCOL_486,
		ProtocolInfo::PROTOCOL_503,
		ProtocolInfo::PROTOCOL_527,
		ProtocolInfo::PROTOCOL_534,
		ProtocolInfo::PROTOCOL_544,
		ProtocolInfo::PROTOCOL_545,
		ProtocolInfo::PROTOCOL_554,
		ProtocolInfo::PROTOCOL_557,
		ProtocolInfo::PROTOCOL_560,
		ProtocolInfo::PROTOCOL_567,
		ProtocolInfo::PROTOCOL_568,
		ProtocolInfo::PROTOCOL_575,
		ProtocolInfo::PROTOCOL_582,
		ProtocolInfo::PROTOCOL_589,
		ProtocolInfo::PROTOCOL_594,
		ProtocolInfo::PROTOCOL_618,
		ProtocolInfo::PROTOCOL_622,
		ProtocolInfo::PROTOCOL_630,
		ProtocolInfo::PROTOCOL_649,
		ProtocolInfo::PROTOCOL_662,
		ProtocolInfo::PROTOCOL_671,
		ProtocolInfo::PROTOCOL_685,
		ProtocolInfo::PROTOCOL_686,
		ProtocolInfo::PROTOCOL_712,
		ProtocolInfo::PROTOCOL_729,
		ProtocolInfo::PROTOCOL_748,
		ProtocolInfo::PROTOCOL_766,
		ProtocolInfo::PROTOCOL_776,
		ProtocolInfo::PROTOCOL_786,
		ProtocolInfo::PROTOCOL_800,
		ProtocolInfo::PROTOCOL_818,
		ProtocolInfo::PROTOCOL_819,
		ProtocolInfo::PROTOCOL_827,
		ProtocolInfo::PROTOCOL_844,
		ProtocolInfo::PROTOCOL_859,
		ProtocolInfo::PROTOCOL_860,
		ProtocolInfo::PROTOCOL_897,
		ProtocolInfo::PROTOCOL_898,
		ProtocolInfo::PROTOCOL_924,
		ProtocolInfo::PROTOCOL_944,
		ProtocolInfo::PROTOCOL_975,
		ProtocolInfo::PROTOCOL_1001,
		ProtocolInfo::PROTOCOL_2168,
		ProtocolInfo::PROTOCOL_2169,
		ProtocolInfo::PROTOCOL_2193
	];

	//Pocket Edition 1.1
	public const PROTOCOL_113 = 113; // 1.1.0, 1.1.1.0, 1.1.1.1, 1.1.1, 1.1.2, 1.1.3.0, 1.1.3.1, 1.1.3, 1.1.4, 1.1.5, 1.1.7

	//Bedrock Edition 1.2
	public const PROTOCOL_137 = 137; // 1.2.0, 1.2.1, 1.2.2, 1.2.3
	public const PROTOCOL_141 = 141; // 1.2.5
	public const PROTOCOL_150 = 150; // 1.2.6
	public const PROTOCOL_160 = 160; // 1.2.7, 1.2.8, 1.2.9
	public const PROTOCOL_201 = 201; // 1.2.10, 1.2.11
	public const PROTOCOL_223 = 223; // 1.2.13, 1.2.14, 1.2.15, 1.2.13.60

	//Bedrock Edition 1.4
	public const PROTOCOL_261 = 261; // 1.4.0, 1.4.1, 1.4.2, 1.4.3, 1.4.4

	//Bedrock Edition 1.5
	public const PROTOCOL_274 = 274; // 1.5.0.10, 1.5.0, 1.5.1, 1.5.2, 1.5.3

	//Bedrock Edition 1.6
	public const PROTOCOL_282 = 282; // 1.6.0.8, 1.6.0.30, 1.6.0, 1.6.1, 1.6.2

	//Bedrock Edition 1.7
	public const PROTOCOL_291 = 291; // 1.7.0.5, 1.7.0.7, 1.7.0.9, 1.7.0, 1.7.1

	//Bedrock Edition 1.8
	public const PROTOCOL_313 = 313; // 1.8.0, 1.8.1

	//Bedrock Edition 1.9
	public const PROTOCOL_332 = 332; // 1.9.0.3, 1.9.0.5, 1.9.0

	//Bedrock Edition 1.10
	public const PROTOCOL_340 = 340; // 1.10.0.3, 1.10.0.4, 1.10.0, 1.10.1

	//Bedrock Edition 1.11
	public const PROTOCOL_354 = 354; // 1.11.0.7, 1.11.0.8, 1.11.0.9, 1.11.0.10, 1.11.0, 1.11.1, 1.11.2, 1.11.3, 1.11.4

	//Bedrock Edition 1.12
	public const PROTOCOL_361 = 361; // 1.12.0.3, 1.12.0.4, 1.12.0.6, 1.12.0.9, 1.12.0.10, 1.12.0.11, 1.12.0.12, 1.12.0.13, 1.12.0.14

	//Bedrock Edition 1.13
	public const PROTOCOL_370 = 370; // 1.13.0.1, 1.13.0.2
	public const PROTOCOL_388 = 388; // 1.13.0.16, 1.13.0.17, 1.13.0, 1.13.1, 1.13.2, 1.13.3

	//Bedrock Edition 1.14
	public const PROTOCOL_389 = 389; // 1.13.0.18, 1.14.0.2, 1.14.0.3, 1.14.0.4, 1.14.0.6, 1.14.0.50, 1.14.0.51, 1.14.0.52, 1.14.0, 1.14.0.12, 1.14.1.2, 1.14.1.3, 1.14.1, 1.14.2.50, 1.14.2.51, 1.14.25.1, 1.14.20, 1.14.30.51, 1.14.30, 1.14.41
	public const PROTOCOL_390 = 390; // 1.14.0.1, 1.14.60

	//Bedrock Edition 1.16
	public const PROTOCOL_407 = 407; // 1.16.0.64, 1.16.0.65, 1.16.0.66, 1.16.0.67, 1.16.0.68, 1.16.0, 1.16.1, 1.16.1.03, 1.16.1.04, 1.16.10, 1.16.20.50, 1.16.20.51, 1.16.20.52, 1.16.30.52, 1.16.30.53
	public const PROTOCOL_408 = 408; // 1.16.20.53, 1.16.20.54, 1.16.20, 1.16.21, 1.16.40, 1.16.42, 1.16.50, 1.16.60, 1.16.61
	public const PROTOCOL_419 = 419; // 1.16.100.60, 1.16.100, 1.16.101
	public const PROTOCOL_422 = 422; // 1.16.200.53, 1.16.200.54, 1.16.200.55, 1.16.200.56, 1.16.200.57, 1.16.200, 1.16.201
	public const PROTOCOL_428 = 428; // 1.16.210.58, 1.16.210.59, 1.16.210.60, 1.16.210.61, 1.16.210
	public const PROTOCOL_431 = 431; // 1.16.220.52, 1.16.220, 1.16.221

	//Bedrock Edition 1.17
	public const PROTOCOL_440 = 440; // 1.17.0.54, 1.17.0.56, 1.17.0.58, 1.17.0, 1.17.1, 1.17.2
	public const PROTOCOL_448 = 448; // 1.17.10.22, 1.17.10.23, 1.17.10, 1.17.11
	public const PROTOCOL_465 = 465; // 1.17.30.25, 1.17.30, 1.17.32, 1.17.33, 1.17.34
	public const PROTOCOL_471 = 471; // 1.17.40.20, 1.17.40.21, 1.17.40.23, 1.17.40, 1.17.41

	//Bedrock Edition 1.18
	public const PROTOCOL_475 = 475; // 1.18.0.24, 1.18.0.25, 1.18.0.27, 1.18.0, 1.18.1, 1.18.2
	public const PROTOCOL_486 = 486; // 1.18.10.28, 1.18.10, 1.18.11, 1.18.12
	public const PROTOCOL_503 = 503; // 1.18.30.29, 1.18.30.31, 1.18.30.33, 1.18.30, 1.18.31, 1.18.32, 1.18.33

	//Bedrock Edition 1.19
	public const PROTOCOL_527 = 527; // 1.19.0, 1.19.2
	public const PROTOCOL_534 = 534; // 1.19.10.23, 1.19.10.24, 1.19.10, 1.19.11
	public const PROTOCOL_544 = 544; // 1.19.20.24, 1.19.20
	public const PROTOCOL_545 = 545; // 1.19.21, 1.19.22
	public const PROTOCOL_554 = 554; // 1.19.30, 1.19.31, 1.19.40.20
	public const PROTOCOL_557 = 557; // 1.19.40.23, 1.19.40.24, 1.19.40, 1.19.41
	public const PROTOCOL_560 = 560; // 1.19.50.22, 1.19.50.23, 1.19.50.24, 1.19.50.25, 1.19.50, 1.19.51
	public const PROTOCOL_567 = 567; // 1.19.60.26, 1.19.60.27, 1.19.60, 1.19.62
	public const PROTOCOL_568 = 568; // 1.19.63
	public const PROTOCOL_575 = 575; // 1.19.70, 1.19.71, 1.19.72, 1.19.73
	public const PROTOCOL_582 = 582; // 1.19.80.22, 1.19.80.23, 1.19.80.24, 1.19.80, 1.19.81, 1.19.83

	//Bedrock Edition 1.20
	public const PROTOCOL_589 = 589; // 1.20.0.25, 1.20.0, 1.20.1
	public const PROTOCOL_594 = 594; // 1.20.10.23, 1.20.10.24, 1.20.10.25, 1.20.10, 1.20.12, 1.20.13, 1.20.14, 1.20.15,
	public const PROTOCOL_618 = 618; // 1.20.30, 1.20.31, 1.20.32
	public const PROTOCOL_622 = 622; // 1.20.40.21, 1.20.40.22, 1.20.40.23, 1.20.40.24, 1.20.40, 1.20.41
	public const PROTOCOL_630 = 630; // 1.20.50.23, 1.20.50.24, 1.20.50, 1.20.51
	public const PROTOCOL_649 = 649; // 1.20.60.24, 1.20.60.25, 1.20.60.26, 1.20.60, 1.20.62
	public const PROTOCOL_662 = 662; // 1.20.70.24, 1.20.70.25, 1.20.70, 1.20.71, 1.20.72, 1.20.73
	public const PROTOCOL_671 = 671; // 1.20.80.23, 1.20.80.24, 1.20.80, 1.20.81

	//Bedrock Edition 1.21
	public const PROTOCOL_685 = 685; // 1.21.0.24, 1.21.0.25, 1.21.0.26, 1.21.0, 1.21.1
	public const PROTOCOL_686 = 686; // 1.21.2, 1.21.3
	public const PROTOCOL_712 = 712; // 1.21.20.22, 1.21.20.23, 1.21.20.24, 1.21.20, 1.21.21, 1.21.22, 1.21.23
	public const PROTOCOL_729 = 729; // 1.21.30.24, 1.21.30.25, 1.21.30, 1.21.31
	public const PROTOCOL_748 = 748; // 1.21.40.23, 1.21.40.25, 1.21.40, 1.21.41, 1.21.42, 1.21.43, 1.21.44
	public const PROTOCOL_766 = 766; // 1.21.50.29, 1.21.50.30. 1.21.50, 1.21.51
	public const PROTOCOL_776 = 776; // 1.21.60.25, 1.21.60.27, 1.21.60.28, 1.21.60, 1.21.61, 1.21.62
	public const PROTOCOL_786 = 786; // 1.21.70.25, 1.21.70.26, 1.21.70, 1.21.71, v1.21.72, v1.21.73
	public const PROTOCOL_800 = 800; // 1.21.80.27, 1.21.80.28, 1.21.80, 1.21.81, 1.21.82
	public const PROTOCOL_818 = 818; // 1.21.90.26, 1.21.90.27, 1.21.90.28, 1.21.90, 1.21.91, 1.21.92
	public const PROTOCOL_819 = 819; // 1.21.93, 1.21.94
	public const PROTOCOL_827 = 827; // 1.21.100.23, 1.21.100.24, 1.21.100
	public const PROTOCOL_844 = 844; // 1.21.110, 1.21.111, 1.21.112, 1.21.113
	public const PROTOCOL_859 = 859; // 1.21.120.24, 1.21.120.25, 1.21.120, 1.21.121, 1.21.122, 1.21.123
	public const PROTOCOL_860 = 860; // 1.21.124
	public const PROTOCOL_897 = 897; // 1.21.130.27
	public const PROTOCOL_898 = 898; // 1.21.130

	//Bedrock Edition 1.26
	public const PROTOCOL_924 = 924; // 26.0
	public const PROTOCOL_944 = 944; // 26.10
	public const PROTOCOL_975 = 975; // 26.20
	public const PROTOCOL_1001 = 1001; // 26.3
	/*
	 * Starting with 26.40, Mojang switched the protocol to a new serialization model (variant types with inner type tags,
	 * optionals and lists everywhere), which is why the protocol numbers jumped.
	 */
	public const PROTOCOL_2168 = 2168; // 26.40, 26.41, 26.44
	public const PROTOCOL_2169 = 2169; // 26.45
	public const PROTOCOL_2193 = 2193; // 26.50, 26.51, 26.52

	public const LOGIN_PACKET = 0x01;
	public const PLAY_STATUS_PACKET = 0x02;
	public const SERVER_TO_CLIENT_HANDSHAKE_PACKET = 0x03;
	public const CLIENT_TO_SERVER_HANDSHAKE_PACKET = 0x04;
	public const DISCONNECT_PACKET = 0x05;
	public const RESOURCE_PACKS_INFO_PACKET = 0x06;
	public const RESOURCE_PACK_STACK_PACKET = 0x07;
	public const RESOURCE_PACK_CLIENT_RESPONSE_PACKET = 0x08;
	public const TEXT_PACKET = 0x09;
	public const SET_TIME_PACKET = 0x0a;
	public const START_GAME_PACKET = 0x0b;
	public const ADD_PLAYER_PACKET = 0x0c;
	public const ADD_ACTOR_PACKET = 0x0d;
	public const REMOVE_ACTOR_PACKET = 0x0e;
	public const ADD_ITEM_ACTOR_PACKET = 0x0f;
	public const SERVER_PLAYER_POST_MOVE_POSITION_PACKET = 0x10;
	public const TAKE_ITEM_ACTOR_PACKET = 0x11;
	public const MOVE_ACTOR_ABSOLUTE_PACKET = 0x12;
	public const MOVE_PLAYER_PACKET = 0x13;
	public const RIDER_JUMP_PACKET = 0x14;
	public const UPDATE_BLOCK_PACKET = 0x15;
	public const ADD_PAINTING_PACKET = 0x16;
	public const TICK_SYNC_PACKET = 0x17;
	public const LEVEL_SOUND_EVENT_PACKET_V1 = 0x18;
	public const LEVEL_EVENT_PACKET = 0x19;
	public const BLOCK_EVENT_PACKET = 0x1a;
	public const ACTOR_EVENT_PACKET = 0x1b;
	public const MOB_EFFECT_PACKET = 0x1c;
	public const UPDATE_ATTRIBUTES_PACKET = 0x1d;
	public const INVENTORY_TRANSACTION_PACKET = 0x1e;
	public const MOB_EQUIPMENT_PACKET = 0x1f;
	public const MOB_ARMOR_EQUIPMENT_PACKET = 0x20;
	public const INTERACT_PACKET = 0x21;
	public const BLOCK_PICK_REQUEST_PACKET = 0x22;
	public const ACTOR_PICK_REQUEST_PACKET = 0x23;
	public const PLAYER_ACTION_PACKET = 0x24;
	public const ACTOR_FALL_PACKET = 0x25;
	public const HURT_ARMOR_PACKET = 0x26;
	public const SET_ACTOR_DATA_PACKET = 0x27;
	public const SET_ACTOR_MOTION_PACKET = 0x28;
	public const SET_ACTOR_LINK_PACKET = 0x29;
	public const SET_HEALTH_PACKET = 0x2a;
	public const SET_SPAWN_POSITION_PACKET = 0x2b;
	public const ANIMATE_PACKET = 0x2c;
	public const RESPAWN_PACKET = 0x2d;
	public const CONTAINER_OPEN_PACKET = 0x2e;
	public const CONTAINER_CLOSE_PACKET = 0x2f;
	public const PLAYER_HOTBAR_PACKET = 0x30;
	public const INVENTORY_CONTENT_PACKET = 0x31;
	public const INVENTORY_SLOT_PACKET = 0x32;
	public const CONTAINER_SET_DATA_PACKET = 0x33;
	public const CRAFTING_DATA_PACKET = 0x34;
	public const CRAFTING_EVENT_PACKET = 0x35;
	public const GUI_DATA_PICK_ITEM_PACKET = 0x36;
	public const ADVENTURE_SETTINGS_PACKET = 0x37;
	public const BLOCK_ACTOR_DATA_PACKET = 0x38;
	public const PLAYER_INPUT_PACKET = 0x39;
	public const LEVEL_CHUNK_PACKET = 0x3a;
	public const SET_COMMANDS_ENABLED_PACKET = 0x3b;
	public const SET_DIFFICULTY_PACKET = 0x3c;
	public const CHANGE_DIMENSION_PACKET = 0x3d;
	public const SET_PLAYER_GAME_TYPE_PACKET = 0x3e;
	public const PLAYER_LIST_PACKET = 0x3f;
	public const SIMPLE_EVENT_PACKET = 0x40;
	public const LEGACY_TELEMETRY_EVENT_PACKET = 0x41;
	public const SPAWN_EXPERIENCE_ORB_PACKET = 0x42;
	public const CLIENTBOUND_MAP_ITEM_DATA_PACKET = 0x43;
	public const MAP_INFO_REQUEST_PACKET = 0x44;
	public const REQUEST_CHUNK_RADIUS_PACKET = 0x45;
	public const CHUNK_RADIUS_UPDATED_PACKET = 0x46;
	public const ITEM_FRAME_DROP_ITEM_PACKET = 0x47;
	public const GAME_RULES_CHANGED_PACKET = 0x48;
	public const CAMERA_PACKET = 0x49;
	public const BOSS_EVENT_PACKET = 0x4a;
	public const SHOW_CREDITS_PACKET = 0x4b;
	public const AVAILABLE_COMMANDS_PACKET = 0x4c;
	public const COMMAND_REQUEST_PACKET = 0x4d;
	public const COMMAND_BLOCK_UPDATE_PACKET = 0x4e;
	public const COMMAND_OUTPUT_PACKET = 0x4f;
	public const UPDATE_TRADE_PACKET = 0x50;
	public const UPDATE_EQUIP_PACKET = 0x51;
	public const RESOURCE_PACK_DATA_INFO_PACKET = 0x52;
	public const RESOURCE_PACK_CHUNK_DATA_PACKET = 0x53;
	public const RESOURCE_PACK_CHUNK_REQUEST_PACKET = 0x54;
	public const TRANSFER_PACKET = 0x55;
	public const PLAY_SOUND_PACKET = 0x56;
	public const STOP_SOUND_PACKET = 0x57;
	public const SET_TITLE_PACKET = 0x58;
	public const ADD_BEHAVIOR_TREE_PACKET = 0x59;
	public const STRUCTURE_BLOCK_UPDATE_PACKET = 0x5a;
	public const SHOW_STORE_OFFER_PACKET = 0x5b;
	public const PURCHASE_RECEIPT_PACKET = 0x5c;
	public const PLAYER_SKIN_PACKET = 0x5d;
	public const SUB_CLIENT_LOGIN_PACKET = 0x5e;
	public const AUTOMATION_CLIENT_CONNECT_PACKET = 0x5f;
	public const SET_LAST_HURT_BY_PACKET = 0x60;
	public const BOOK_EDIT_PACKET = 0x61;
	public const NPC_REQUEST_PACKET = 0x62;
	public const PHOTO_TRANSFER_PACKET = 0x63;
	public const MODAL_FORM_REQUEST_PACKET = 0x64;
	public const MODAL_FORM_RESPONSE_PACKET = 0x65;
	public const SERVER_SETTINGS_REQUEST_PACKET = 0x66;
	public const SERVER_SETTINGS_RESPONSE_PACKET = 0x67;
	public const SHOW_PROFILE_PACKET = 0x68;
	public const SET_DEFAULT_GAME_TYPE_PACKET = 0x69;
	public const REMOVE_OBJECTIVE_PACKET = 0x6a;
	public const SET_DISPLAY_OBJECTIVE_PACKET = 0x6b;
	public const SET_SCORE_PACKET = 0x6c;
	public const LAB_TABLE_PACKET = 0x6d;
	public const UPDATE_BLOCK_SYNCED_PACKET = 0x6e;
	public const MOVE_ACTOR_DELTA_PACKET = 0x6f;
	public const SET_SCOREBOARD_IDENTITY_PACKET = 0x70;
	public const SET_LOCAL_PLAYER_AS_INITIALIZED_PACKET = 0x71;
	public const UPDATE_SOFT_ENUM_PACKET = 0x72;
	public const NETWORK_STACK_LATENCY_PACKET = 0x73;
	public const SCRIPT_CUSTOM_EVENT_PACKET = 0x75;
	public const SPAWN_PARTICLE_EFFECT_PACKET = 0x76;
	public const AVAILABLE_ACTOR_IDENTIFIERS_PACKET = 0x77;
	public const LEVEL_SOUND_EVENT_PACKET_V2 = 0x78;
	public const NETWORK_CHUNK_PUBLISHER_UPDATE_PACKET = 0x79;
	public const BIOME_DEFINITION_LIST_PACKET = 0x7a;
	public const LEVEL_SOUND_EVENT_PACKET = 0x7b;
	public const LEVEL_EVENT_GENERIC_PACKET = 0x7c;
	public const LECTERN_UPDATE_PACKET = 0x7d;
	public const VIDEO_STREAM_CONNECT_PACKET = 0x7e;
	public const ADD_ENTITY_PACKET = 0x7f;
	public const REMOVE_ENTITY_PACKET = 0x80;
	public const CLIENT_CACHE_STATUS_PACKET = 0x81;
	public const ON_SCREEN_TEXTURE_ANIMATION_PACKET = 0x82;
	public const MAP_CREATE_LOCKED_COPY_PACKET = 0x83;
	public const STRUCTURE_TEMPLATE_DATA_REQUEST_PACKET = 0x84;
	public const STRUCTURE_TEMPLATE_DATA_RESPONSE_PACKET = 0x85;
	public const UPDATE_BLOCK_PROPERTIES_PACKET = 0x86;
	public const CLIENT_CACHE_BLOB_STATUS_PACKET = 0x87;
	public const CLIENT_CACHE_MISS_RESPONSE_PACKET = 0x88;
	public const EDUCATION_SETTINGS_PACKET = 0x89;
	public const EMOTE_PACKET = 0x8a;
	public const MULTIPLAYER_SETTINGS_PACKET = 0x8b;
	public const SETTINGS_COMMAND_PACKET = 0x8c;
	public const ANVIL_DAMAGE_PACKET = 0x8d;
	public const COMPLETED_USING_ITEM_PACKET = 0x8e;
	public const NETWORK_SETTINGS_PACKET = 0x8f;
	public const PLAYER_AUTH_INPUT_PACKET = 0x90;
	public const CREATIVE_CONTENT_PACKET = 0x91;
	public const PLAYER_ENCHANT_OPTIONS_PACKET = 0x92;
	public const ITEM_STACK_REQUEST_PACKET = 0x93;
	public const ITEM_STACK_RESPONSE_PACKET = 0x94;
	public const PLAYER_ARMOR_DAMAGE_PACKET = 0x95;
	public const CODE_BUILDER_PACKET = 0x96;
	public const UPDATE_PLAYER_GAME_TYPE_PACKET = 0x97;
	public const EMOTE_LIST_PACKET = 0x98;
	public const POSITION_TRACKING_D_B_SERVER_BROADCAST_PACKET = 0x99;
	public const POSITION_TRACKING_D_B_CLIENT_REQUEST_PACKET = 0x9a;
	public const DEBUG_INFO_PACKET = 0x9b;
	public const PACKET_VIOLATION_WARNING_PACKET = 0x9c;
	public const MOTION_PREDICTION_HINTS_PACKET = 0x9d;
	public const ANIMATE_ENTITY_PACKET = 0x9e;
	public const CAMERA_SHAKE_PACKET = 0x9f;
	public const PLAYER_FOG_PACKET = 0xa0;
	public const CORRECT_PLAYER_MOVE_PREDICTION_PACKET = 0xa1;
	public const ITEM_COMPONENT_PACKET = 0xa2;
	public const FILTER_TEXT_PACKET = 0xa3;
	public const CLIENTBOUND_DEBUG_RENDERER_PACKET = 0xa4;
	public const SYNC_ACTOR_PROPERTY_PACKET = 0xa5;
	public const ADD_VOLUME_ENTITY_PACKET = 0xa6;
	public const REMOVE_VOLUME_ENTITY_PACKET = 0xa7;
	public const SIMULATION_TYPE_PACKET = 0xa8;
	public const NPC_DIALOGUE_PACKET = 0xa9;
	public const EDU_URI_RESOURCE_PACKET = 0xaa;
	public const CREATE_PHOTO_PACKET = 0xab;
	public const UPDATE_SUB_CHUNK_BLOCKS_PACKET = 0xac;
	public const PHOTO_INFO_REQUEST_PACKET = 0xad;
	public const SUB_CHUNK_PACKET = 0xae;
	public const SUB_CHUNK_REQUEST_PACKET = 0xaf;
	public const PLAYER_START_ITEM_COOLDOWN_PACKET = 0xb0;
	public const SCRIPT_MESSAGE_PACKET = 0xb1;
	public const CODE_BUILDER_SOURCE_PACKET = 0xb2;
	public const TICKING_AREAS_LOAD_STATUS_PACKET = 0xb3;
	public const DIMENSION_DATA_PACKET = 0xb4;
	public const AGENT_ACTION_EVENT_PACKET = 0xb5;
	public const CHANGE_MOB_PROPERTY_PACKET = 0xb6;
	public const LESSON_PROGRESS_PACKET = 0xb7;
	public const REQUEST_ABILITY_PACKET = 0xb8;
	public const REQUEST_PERMISSIONS_PACKET = 0xb9;
	public const TOAST_REQUEST_PACKET = 0xba;
	public const UPDATE_ABILITIES_PACKET = 0xbb;
	public const UPDATE_ADVENTURE_SETTINGS_PACKET = 0xbc;
	public const DEATH_INFO_PACKET = 0xbd;
	public const EDITOR_NETWORK_PACKET = 0xbe;
	public const FEATURE_REGISTRY_PACKET = 0xbf;
	public const SERVER_STATS_PACKET = 0xc0;
	public const REQUEST_NETWORK_SETTINGS_PACKET = 0xc1;
	public const GAME_TEST_REQUEST_PACKET = 0xc2;
	public const GAME_TEST_RESULTS_PACKET = 0xc3;
	public const UPDATE_CLIENT_INPUT_LOCKS_PACKET = 0xc4;
	public const CLIENT_CHEAT_ABILITY_PACKET = 0xc5;
	public const CAMERA_PRESETS_PACKET = 0xc6;
	public const UNLOCKED_RECIPES_PACKET = 0xc7;

	public const CAMERA_INSTRUCTION_PACKET = 0x12c;
	public const COMPRESSED_BIOME_DEFINITION_LIST_PACKET = 0x12d;
	public const TRIM_DATA_PACKET = 0x12e;
	public const OPEN_SIGN_PACKET = 0x12f;
	public const AGENT_ANIMATION_PACKET = 0x130;
	public const REFRESH_ENTITLEMENTS_PACKET = 0x131;
	public const PLAYER_TOGGLE_CRAFTER_SLOT_REQUEST_PACKET = 0x132;
	public const SET_PLAYER_INVENTORY_OPTIONS_PACKET = 0x133;
	public const SET_HUD_PACKET = 0x134;
	public const AWARD_ACHIEVEMENT_PACKET = 0x135;
	public const CLIENTBOUND_CLOSE_FORM_PACKET = 0x136;

	public const SERVERBOUND_LOADING_SCREEN_PACKET = 0x138;
	public const JIGSAW_STRUCTURE_DATA_PACKET = 0x139;
	public const CURRENT_STRUCTURE_FEATURE_PACKET = 0x13a;
	public const SERVERBOUND_DIAGNOSTICS_PACKET = 0x13b;
	public const CAMERA_AIM_ASSIST_PACKET = 0x13c;
	public const CONTAINER_REGISTRY_CLEANUP_PACKET = 0x13d;
	public const MOVEMENT_EFFECT_PACKET = 0x13e;
	public const SET_MOVEMENT_AUTHORITY_PACKET = 0x13f;
	public const CAMERA_AIM_ASSIST_PRESETS_PACKET = 0x140;
	public const CAMERA_AIM_ASSIST_INSTRUCTION_PACKET = 0x141;
	public const CLIENT_MOVEMENT_PREDICTION_SYNC_PACKET = 0x142;
	public const UPDATE_CLIENT_OPTIONS_PACKET = 0x143;
	public const PLAYER_VIDEO_CAPTURE_PACKET = 0x144;
	public const PLAYER_UPDATE_ENTITY_OVERRIDES_PACKET = 0x145;
	public const ITEM_REGISTRY_PACKET = 0x146;
	public const PLAYER_LOCATION_PACKET = 0x147;
	public const CLIENTBOUND_CONTROL_SCHEME_SET_PACKET = 0x148;
	public const PRIMITIVE_SHAPES_PACKET = 0x149;
	public const SERVERBOUND_PACK_SETTING_CHANGE_PACKET = 0x14a;
	public const CLIENTBOUND_DATA_STORE_PACKET = 0x14c;
	public const GRAPHICS_OVERRIDE_PARAMETER_PACKET = 0x14d;
	public const SERVERBOUND_DATA_STORE_PACKET = 0x14b;
	public const EXPLODE_PACKET = 0x14e;
	public const CONTAINER_SET_SLOT_PACKET = 0x14f;
	public const CONTAINER_SET_CONTENT_PACKET = 0x150;
	public const DROP_ITEM_PACKET = 0x151;
	public const USE_ITEM_PACKET = 0x152;
	public const REMOVE_BLOCK_PACKET = 0x153;
	public const COMMAND_STEP_PACKET = 0x154;
	public const ADD_HANGING_ACTOR_PACKET = 0x155;
	public const INVENTORY_ACTION_PACKET = 0x156;
	public const REPLACE_ITEM_IN_SLOT_PACKET = 0x157;
	public const ADD_ITEM_PACKET = 0x158;
	public const CLIENTBOUND_DATA_DRIVEN_UI_SHOW_SCREEN_PACKET = 0x159;
	public const CLIENTBOUND_DATA_DRIVEN_UI_CLOSE_ALL_SCREENS_PACKET = 0x15a;
	public const CLIENTBOUND_DATA_DRIVEN_UI_RELOAD_PACKET = 0x15b;
	public const CLIENTBOUND_TEXTURE_SHIFT_PACKET = 0x15c;
	public const VOXEL_SHAPES_PACKET = 0x15d;
	public const CAMERA_SPLINE_PACKET = 0x15e;
	public const CAMERA_AIM_ASSIST_ACTOR_PRIORITY_PACKET = 0x15f;
	public const RESOURCE_PACKS_READY_FOR_VALIDATION_PACKET = 0x160;
	public const LOCATOR_BAR_PACKET = 0x161;
	public const PARTY_CHANGED_PACKET = 0x162;
	public const SERVERBOUND_DATA_DRIVEN_SCREEN_CLOSED_PACKET = 0x163;
	public const SYNC_WORLD_CLOCKS_PACKET = 0x164;
	public const CLIENTBOUND_ATTRIBUTE_LAYER_SYNC_PACKET = 0x165;
	public const SERVER_STORE_INFO_PACKET = 0x166;
	public const SERVER_PRESENCE_INFO_PACKET = 0x167;
	public const CLIENTBOUND_UPDATE_SOUND_DATA_PACKET = 0x168;
	public const SEND_PARTY_DESTINATION_COOKIE_PACKET = 0x169;
	public const PARTY_DESTINATION_COOKIE_RESPONSE_PACKET = 0x16a;
	public const SET_PLAYER_FURNACE_OPTIONS_PACKET = 0x16b;
	public const RECORD_STARTED_PACKET = 0x16c;
}
