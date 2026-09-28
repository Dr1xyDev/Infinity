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

namespace pocketmine\level\generator\vanilla\network\mcpe\convert\constants\actorMetadataList\properties;

class ActorProperties407
{
	public function __construct()
	{
		//NOOP
	}

	/*
	 * Readers beware: this isn't a nice list. Some of the properties have different types for different entities, and
	 * are used for entirely different things.
	 */
	public const DATA_FLAGS = 0;
	public const DATA_HEALTH = 1; //int (minecart/boat)
	public const DATA_VARIANT = 2; //int
	public const DATA_COLOR = 3; //byte
	public const DATA_NAMETAG = 4; //string
	public const DATA_OWNER_EID = 5; //long
	public const DATA_TARGET_EID = 6; //long
	public const DATA_AIR = 7; //short
	public const DATA_POTION_COLOR = 8; //int (ARGB!)
	public const DATA_POTION_AMBIENT = 9; //byte
	public const DATA_JUMP_DURATION = 10; //byte
	public const DATA_HURT_TIME = 11; //int (minecart/boat)
	public const DATA_HURT_DIRECTION = 12; //int (minecart/boat)
	public const DATA_PADDLE_TIME_LEFT = 13; //float
	public const DATA_PADDLE_TIME_RIGHT = 14; //float
	public const DATA_EXPERIENCE_VALUE = 15; //int (xp orb)
	public const DATA_MINECART_DISPLAY_BLOCK = 16; //int (block runtime ID)
	public const DATA_HORSE_FLAGS = 16; //int
	public const DATA_FIREWORK_ITEM = 16; //compoundtag
	/* 16 (byte) used by wither skull */
	public const DATA_MINECART_DISPLAY_OFFSET = 17; //int
	public const DATA_SHOOTER_ID = 17; //long (used by arrows)
	public const DATA_MINECART_HAS_DISPLAY = 18; //byte (must be 1 for minecart to show block inside)
	public const DATA_HORSE_TYPE = 19; //byte
	public const DATA_CREEPER_SWELL = 19; //int
	public const DATA_CREEPER_SWELL_PREVIOUS = 20; //int
	public const DATA_CREEPER_SWELL_DIRECTION = 21; //byte
	public const DATA_CHARGE_AMOUNT = 22; //int8, used for ghasts and also crossbow charging
	public const DATA_ENDERMAN_HELD_ITEM_ID = 23; //short
	public const DATA_ENTITY_AGE = 24; //short
	/* 25 (int) used by horse, (byte) used by witch */
	public const DATA_PLAYER_FLAGS = 26; //byte
	public const DATA_PLAYER_INDEX = 27; //int, used for marker colours and agent nametag colours
	public const DATA_PLAYER_BED_POSITION = 28; //blockpos
	public const DATA_FIREBALL_POWER_X = 29; //float
	public const DATA_FIREBALL_POWER_Y = 30;
	public const DATA_FIREBALL_POWER_Z = 31;
	/* 32 (unknown) */
	public const DATA_FISH_X = 33; //float
	public const DATA_FISH_Z = 34; //float
	public const DATA_FISH_ANGLE = 35; //float
	public const DATA_POTION_AUX_VALUE = 36; //short
	public const DATA_LEAD_HOLDER_EID = 37; //long
	public const DATA_SCALE = 38; //float
	public const DATA_HAS_NPC_COMPONENT = 39; //byte (???)
	public const DATA_NPC_SKIN_INDEX = 40; //string
	public const DATA_NPC_ACTIONS = 41; //string (maybe JSON blob?)
	public const DATA_MAX_AIR = 42; //short
	public const DATA_MARK_VARIANT = 43; //int
	public const DATA_CONTAINER_TYPE = 44; //byte (ContainerComponent)
	public const DATA_CONTAINER_BASE_SIZE = 45; //int (ContainerComponent)
	public const DATA_CONTAINER_EXTRA_SLOTS_PER_STRENGTH = 46; //int (used for llamas, inventory size is baseSize + thisProp * strength)
	public const DATA_BLOCK_TARGET = 47; //block coords (ender crystal)
	public const DATA_WITHER_INVULNERABLE_TICKS = 48; //int
	public const DATA_WITHER_TARGET_1 = 49; //long
	public const DATA_WITHER_TARGET_2 = 50; //long
	public const DATA_WITHER_TARGET_3 = 51; //long
	public const DATA_WITHER_AERIAL_ATTACK = 52; //short
	public const DATA_BOUNDING_BOX_WIDTH = 53; //float
	public const DATA_BOUNDING_BOX_HEIGHT = 54; //float
	public const DATA_FUSE_LENGTH = 55; //int
	public const DATA_RIDER_SEAT_POSITION = 56; //vector3f
	public const DATA_RIDER_ROTATION_LOCKED = 57; //byte
	public const DATA_RIDER_MAX_ROTATION = 58; //float
	public const DATA_RIDER_MIN_ROTATION = 59; //float
	public const DATA_AREA_EFFECT_CLOUD_RADIUS = 60; //float
	public const DATA_AREA_EFFECT_CLOUD_WAITING = 61; //int
	public const DATA_AREA_EFFECT_CLOUD_PARTICLE_ID = 62; //int
	public const DATA_SHULKER_PEEK_ID = 63; //int
	public const DATA_SHULKER_ATTACH_FACE = 64; //byte
	public const DATA_SHULKER_ATTACHED = 65; //byte (TODO: check this - comment said it was a short)
	public const DATA_SHULKER_ATTACH_POS = 66; //block coords
	public const DATA_TRADING_PLAYER_EID = 67; //long
	public const DATA_CAREER = 68; //int
	public const DATA_HAS_COMMAND_BLOCK = 69; //byte
	public const DATA_COMMAND_BLOCK_COMMAND = 70; //string
	public const DATA_COMMAND_BLOCK_LAST_OUTPUT = 71; //string
	public const DATA_COMMAND_BLOCK_TRACK_OUTPUT = 72; //byte
	public const DATA_CONTROLLING_RIDER_SEAT_NUMBER = 73; //byte
	public const DATA_STRENGTH = 74; //int
	public const DATA_MAX_STRENGTH = 75; //int
	public const DATA_EVOKER_SPELL_CASTING_COLOR = 76; //int
	public const DATA_LIMITED_LIFE = 77;
	public const DATA_ARMOR_STAND_POSE_INDEX = 78; //int
	public const DATA_ENDER_CRYSTAL_TIME_OFFSET = 79; //int
	public const DATA_ALWAYS_SHOW_NAMETAG = 80; //byte: -1 = default, 0 = only when looked at, 1 = always
	public const DATA_COLOR_2 = 81; //byte
	public const DATA_NAME_AUTHOR = 82; //string
	public const DATA_SCORE_TAG = 83; //string
	public const DATA_BALLOON_ATTACHED_ENTITY = 84; //int64, entity unique ID of owner
	public const DATA_PUFFERFISH_SIZE = 85; //byte
	public const DATA_BOAT_BUBBLE_TIME = 86; //int (time in bubble column)
	public const DATA_PLAYER_AGENT_EID = 87; //long
	public const DATA_SITTING_AMOUNT = 88; //float
	public const DATA_SITTING_AMOUNT_PREVIOUS = 89; //float
	public const DATA_EAT_COUNTER = 90; //int (used by pandas)
	public const DATA_FLAGS2 = 91; //long (extended data flags)
	public const DATA_LAYING_AMOUNT = 92; //float (used by pandas)
	public const DATA_LAYING_AMOUNT_PREVIOUS = 93; //float (used by pandas)
	public const DATA_AREA_EFFECT_CLOUD_DURATION = 94; //int
	public const DATA_AREA_EFFECT_CLOUD_SPAWN_TIME = 95; //int
	public const DATA_AREA_EFFECT_CLOUD_RADIUS_PER_TICK = 96; //float, usually negative
	public const DATA_AREA_EFFECT_CLOUD_RADIUS_CHANGE_ON_PICKUP = 97; //float
	public const DATA_AREA_EFFECT_CLOUD_PICKUP_COUNT = 98; //int
	public const DATA_INTERACTIVE_TAG = 99; //string (button text)
	public const DATA_TRADE_TIER = 100; //int
	public const DATA_MAX_TRADE_TIER = 101; //int
	public const DATA_TRADE_XP = 102; //int
	public const DATA_SKIN_ID = 103; //int ???
	public const DATA_SPAWNING_FRAMES = 104; //int - related to wither
	public const DATA_COMMAND_BLOCK_TICK_DELAY = 105; //int
	public const DATA_COMMAND_BLOCK_EXECUTE_ON_FIRST_TICK = 106; //byte
	public const DATA_AMBIENT_SOUND_INTERVAL_MIN = 107; //float
	public const DATA_AMBIENT_SOUND_INTERVAL_RANGE = 108; //float
	public const DATA_AMBIENT_SOUND_EVENT = 109; //string

}
