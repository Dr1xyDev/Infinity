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

class ActorProperties113
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
	public const DATA_ENDERMAN_HELD_ITEM_DAMAGE = 24; //short
	public const DATA_ENTITY_AGE = 25; //short
	/* 26 (int) used by horse, (byte) used by witch */
	public const DATA_PLAYER_FLAGS = 27; //byte
	public const DATA_PLAYER_INDEX = 28; //int, used for marker colours and agent nametag colours
	public const DATA_PLAYER_BED_POSITION = 29; //blockpos
	public const DATA_FIREBALL_POWER_X = 30; //float
	public const DATA_FIREBALL_POWER_Y = 31;
	public const DATA_FIREBALL_POWER_Z = 32;
	/* 33 (unknown) */
	public const DATA_FISH_X = 34; //float
	public const DATA_FISH_Z = 35; //float
	public const DATA_FISH_ANGLE = 36; //float
	public const DATA_POTION_AUX_VALUE = 37; //short
	public const DATA_LEAD_HOLDER_EID = 38; //long
	public const DATA_SCALE = 39; //float
	public const DATA_INTERACTIVE_TAG = 40; //string (button text)
	public const DATA_NPC_SKIN_INDEX = 41; //string
	public const DATA_NPC_ACTIONS = 42; //string
	public const DATA_MAX_AIR = 43; //short
	public const DATA_MARK_VARIANT = 44; //int
	public const DATA_CONTAINER_TYPE = 45; //byte (ContainerComponent)
	public const DATA_CONTAINER_BASE_SIZE = 46; //int (ContainerComponent)
	public const DATA_CONTAINER_EXTRA_SLOTS_PER_STRENGTH = 47; //int (used for llamas, inventory size is baseSize + thisProp * strength)
	public const DATA_BLOCK_TARGET = 48; //block coords (ender crystal)
	public const DATA_WITHER_INVULNERABLE_TICKS = 49; //int
	public const DATA_WITHER_TARGET_1 = 50; //long
	public const DATA_WITHER_TARGET_2 = 51; //long
	public const DATA_WITHER_TARGET_3 = 52; //long
	public const DATA_WITHER_AERIAL_ATTACK = 53; //short
	public const DATA_BOUNDING_BOX_WIDTH = 54; //float
	public const DATA_BOUNDING_BOX_HEIGHT = 55; //float
	public const DATA_FUSE_LENGTH = 56; //int
	public const DATA_RIDER_SEAT_POSITION = 57; //vector3f
	public const DATA_RIDER_ROTATION_LOCKED = 58; //byte
	public const DATA_RIDER_MAX_ROTATION = 59; //float
	public const DATA_RIDER_MIN_ROTATION = 60; //float
	public const DATA_AREA_EFFECT_CLOUD_RADIUS = 61; //float
	public const DATA_AREA_EFFECT_CLOUD_WAITING = 62; //int
	public const DATA_AREA_EFFECT_CLOUD_PARTICLE_ID = 63; //int
	public const DATA_SHULKER_PEEK_ID = 64; //int
	public const DATA_SHULKER_ATTACH_FACE = 65; //byte
	public const DATA_SHULKER_ATTACHED = 66; //byte (TODO: check this - comment said it was a short)
	public const DATA_SHULKER_ATTACH_POS = 67; //block coords
	public const DATA_TRADING_PLAYER_EID = 68; //long
	public const DATA_CAREER = 69; //int
	public const DATA_HAS_COMMAND_BLOCK = 70; //byte
	public const DATA_COMMAND_BLOCK_COMMAND = 71; //string
	public const DATA_COMMAND_BLOCK_LAST_OUTPUT = 72; //string
	public const DATA_COMMAND_BLOCK_TRACK_OUTPUT = 73; //byte
	public const DATA_CONTROLLING_RIDER_SEAT_NUMBER = 74; //byte
	public const DATA_STRENGTH = 75; //int
	public const DATA_MAX_STRENGTH = 76; //int
	public const DATA_EVOKER_SPELL_CASTING_COLOR = 77; //int
	public const DATA_LIMITED_LIFE = 78;
	public const DATA_ARMOR_STAND_POSE_INDEX = 79; //int
	public const DATA_ENDER_CRYSTAL_TIME_OFFSET = 80; //int
	public const DATA_ALWAYS_SHOW_NAMETAG = 81; //byte: -1 = default, 0 = only when looked at, 1 = always
	public const DATA_COLOR_2 = 82; //byte
	public const DATA_NAME_AUTHOR = 83; //string
	public const DATA_SCORE_TAG = 84; //string
	public const DATA_BALLOON_ATTACHED_ENTITY = 85; //int64, entity unique ID of owner
	public const DATA_PUFFERFISH_SIZE = 86; //byte
	public const DATA_BOAT_BUBBLE_TIME = 87; //int (time in bubble column)
	public const DATA_PLAYER_AGENT_EID = 88; //long
	public const DATA_SITTING_AMOUNT = 89; //float
	public const DATA_SITTING_AMOUNT_PREVIOUS = 90; //float
	public const DATA_EAT_COUNTER = 91; //int (used by pandas)
	public const DATA_FLAGS2 = 92; //long (extended data flags)
	public const DATA_LAYING_AMOUNT = 93; //float (used by pandas)
	public const DATA_LAYING_AMOUNT_PREVIOUS = 94; //float (used by pandas)
	public const DATA_AREA_EFFECT_CLOUD_DURATION = 95; //int
	public const DATA_AREA_EFFECT_CLOUD_SPAWN_TIME = 96; //int
	public const DATA_AREA_EFFECT_CLOUD_RADIUS_PER_TICK = 97; //float, usually negative
	public const DATA_AREA_EFFECT_CLOUD_RADIUS_CHANGE_ON_PICKUP = 98; //float
	public const DATA_AREA_EFFECT_CLOUD_PICKUP_COUNT = 99; //int

}
