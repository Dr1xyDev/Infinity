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

namespace pocketmine\level\generator\vanilla\network\mcpe\convert\protocol;

final class ProtocolInfo113
{
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
	public const ADD_HANGING_ACTOR_PACKET = 0x10;
	public const TAKE_ITEM_ACTOR_PACKET = 0x11;
	public const MOVE_ACTOR_ABSOLUTE_PACKET = 0x12;
	public const MOVE_PLAYER_PACKET = 0x13;
	public const RIDER_JUMP_PACKET = 0x14;
	public const REMOVE_BLOCK_PACKET = 0x15;
	public const UPDATE_BLOCK_PACKET = 0x16;
	public const ADD_PAINTING_PACKET = 0x17;
	public const EXPLODE_PACKET = 0x18;
	public const LEVEL_SOUND_EVENT_PACKET = 0x19;
	public const LEVEL_EVENT_PACKET = 0x1a;
	public const BLOCK_EVENT_PACKET = 0x1b;
	public const ACTOR_EVENT_PACKET = 0x1c;
	public const MOB_EFFECT_PACKET = 0x1d;
	public const UPDATE_ATTRIBUTES_PACKET = 0x1e;
	public const MOB_EQUIPMENT_PACKET = 0x1f;
	public const MOB_ARMOR_EQUIPMENT_PACKET = 0x20;
	public const INTERACT_PACKET = 0x21;
	public const BLOCK_PICK_REQUEST_PACKET = 0x22;
	public const USE_ITEM_PACKET = 0x23;
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
	public const DROP_ITEM_PACKET = 0x2e;
	public const INVENTORY_ACTION_PACKET = 0x2f;
	public const CONTAINER_OPEN_PACKET = 0x30;
	public const CONTAINER_CLOSE_PACKET = 0x31;
	public const CONTAINER_SET_SLOT_PACKET = 0x32;
	public const CONTAINER_SET_DATA_PACKET = 0x33;
	public const CONTAINER_SET_CONTENT_PACKET = 0x34;
	public const CRAFTING_DATA_PACKET = 0x35;
	public const CRAFTING_EVENT_PACKET = 0x36;
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
	public const REPLACE_ITEM_IN_SLOT_PACKET = 0x48;
	public const GAME_RULES_CHANGED_PACKET = 0x49;
	public const CAMERA_PACKET = 0x4a;
	public const ADD_ITEM_PACKET = 0x4b;
	public const BOSS_EVENT_PACKET = 0x4c;
	public const SHOW_CREDITS_PACKET = 0x4d;
	public const AVAILABLE_COMMANDS_PACKET = 0x4e;
	public const COMMAND_STEP_PACKET = 0x4f;
	public const COMMAND_BLOCK_UPDATE_PACKET = 0x50;
	public const UPDATE_TRADE_PACKET = 0x51;
	public const UPDATE_EQUIP_PACKET = 0x52;
	public const RESOURCE_PACK_DATA_INFO_PACKET = 0x53;
	public const RESOURCE_PACK_CHUNK_DATA_PACKET = 0x54;
	public const RESOURCE_PACK_CHUNK_REQUEST_PACKET = 0x55;
	public const TRANSFER_PACKET = 0x56;
	public const PLAY_SOUND_PACKET = 0x57;
	public const STOP_SOUND_PACKET = 0x58;
	public const SET_TITLE_PACKET = 0x59;
	public const ADD_BEHAVIOR_TREE_PACKET = 0x5a;
	public const STRUCTURE_BLOCK_UPDATE_PACKET = 0x5b;
	public const SHOW_STORE_OFFER_PACKET = 0x5c;
	public const PURCHASE_RECEIPT_PACKET = 0x5d;

}
