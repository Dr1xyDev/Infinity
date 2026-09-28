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

namespace pocketmine\level\generator\vanilla\network\mcpe\convert\constants\actorMetadataList\flags;

class ActorFlags818
{
	public function __construct()
	{
		//NOOP
	}

	public const DATA_FLAG_ONFIRE = 0;
	public const DATA_FLAG_SNEAKING = 1;
	public const DATA_FLAG_RIDING = 2;
	public const DATA_FLAG_SPRINTING = 3;
	public const DATA_FLAG_ACTION = 4;
	public const DATA_FLAG_INVISIBLE = 5;
	public const DATA_FLAG_TEMPTED = 6;
	public const DATA_FLAG_INLOVE = 7;
	public const DATA_FLAG_SADDLED = 8;
	public const DATA_FLAG_POWERED = 9;
	public const DATA_FLAG_IGNITED = 10;
	public const DATA_FLAG_BABY = 11;
	public const DATA_FLAG_CONVERTING = 12;
	public const DATA_FLAG_CRITICAL = 13;
	public const DATA_FLAG_CAN_SHOW_NAMETAG = 14;
	public const DATA_FLAG_ALWAYS_SHOW_NAMETAG = 15;
	public const DATA_FLAG_IMMOBILE = 16, DATA_FLAG_NO_AI = 16;
	public const DATA_FLAG_SILENT = 17;
	public const DATA_FLAG_WALLCLIMBING = 18;
	public const DATA_FLAG_CAN_CLIMB = 19;
	public const DATA_FLAG_SWIMMER = 20;
	public const DATA_FLAG_CAN_FLY = 21;
	public const DATA_FLAG_WALKER = 22;
	public const DATA_FLAG_RESTING = 23;
	public const DATA_FLAG_SITTING = 24;
	public const DATA_FLAG_ANGRY = 25;
	public const DATA_FLAG_INTERESTED = 26;
	public const DATA_FLAG_CHARGED = 27;
	public const DATA_FLAG_TAMED = 28;
	public const DATA_FLAG_ORPHANED = 29;
	public const DATA_FLAG_LEASHED = 30;
	public const DATA_FLAG_SHEARED = 31;
	public const DATA_FLAG_GLIDING = 32;
	public const DATA_FLAG_ELDER = 33;
	public const DATA_FLAG_MOVING = 34;
	public const DATA_FLAG_BREATHING = 35;
	public const DATA_FLAG_CHESTED = 36;
	public const DATA_FLAG_STACKABLE = 37;
	public const DATA_FLAG_SHOWBASE = 38;
	public const DATA_FLAG_REARING = 39;
	public const DATA_FLAG_VIBRATING = 40;
	public const DATA_FLAG_IDLING = 41;
	public const DATA_FLAG_EVOKER_SPELL = 42;
	public const DATA_FLAG_CHARGE_ATTACK = 43;
	public const DATA_FLAG_WASD_CONTROLLED = 44;
	public const DATA_FLAG_CAN_POWER_JUMP = 45;
	public const DATA_FLAG_CAN_DASH = 46;
	public const DATA_FLAG_LINGER = 47;
	public const DATA_FLAG_HAS_COLLISION = 48;
	public const DATA_FLAG_AFFECTED_BY_GRAVITY = 49;
	public const DATA_FLAG_FIRE_IMMUNE = 50;
	public const DATA_FLAG_DANCING = 51;
	public const DATA_FLAG_ENCHANTED = 52;
	public const DATA_FLAG_SHOW_TRIDENT_ROPE = 53; // tridents show an animated rope when enchanted with loyalty after they are thrown and return to their owner. To be combined with DATA_OWNER_EID
	public const DATA_FLAG_CONTAINER_PRIVATE = 54; //inventory is private, doesn't drop contents when killed if true
	public const DATA_FLAG_TRANSFORMING = 55;
	public const DATA_FLAG_SPIN_ATTACK = 56;
	public const DATA_FLAG_SWIMMING = 57;
	public const DATA_FLAG_BRIBED = 58; //dolphins have this set when they go to find treasure for the player
	public const DATA_FLAG_PREGNANT = 59;
	public const DATA_FLAG_LAYING_EGG = 60;
	public const DATA_FLAG_RIDER_CAN_PICK = 61; //???
	public const DATA_FLAG_TRANSITION_SITTING = 62;
	public const DATA_FLAG_EATING = 63;
	public const DATA_FLAG_LAYING_DOWN = 64;
	public const DATA_FLAG_SNEEZING = 65;
	public const DATA_FLAG_TRUSTING = 66;
	public const DATA_FLAG_ROLLING = 67;
	public const DATA_FLAG_SCARED = 68;
	public const DATA_FLAG_IN_SCAFFOLDING = 69;
	public const DATA_FLAG_OVER_SCAFFOLDING = 70;
	public const DATA_FLAG_FALL_THROUGH_SCAFFOLDING = 71;
	public const DATA_FLAG_BLOCKING = 72; //shield
	public const DATA_FLAG_TRANSITION_BLOCKING = 73;
	public const DATA_FLAG_BLOCKED_USING_SHIELD = 74;
	public const DATA_FLAG_BLOCKED_USING_DAMAGED_SHIELD = 75;
	public const DATA_FLAG_SLEEPING = 76;
	public const DATA_FLAG_WANTS_TO_WAKE = 77;
	public const DATA_FLAG_TRADE_INTEREST = 78;
	public const DATA_FLAG_DOOR_BREAKER = 79; //...
	public const DATA_FLAG_BREAKING_OBSTRUCTION = 80;
	public const DATA_FLAG_DOOR_OPENER = 81; //...
	public const DATA_FLAG_ILLAGER_CAPTAIN = 82;
	public const DATA_FLAG_STUNNED = 83;
	public const DATA_FLAG_ROARING = 84;
	public const DATA_FLAG_DELAYED_ATTACKING = 85;
	public const DATA_FLAG_AVOIDING_MOBS = 86;
	public const DATA_FLAG_AVOIDING_BLOCK = 87;
	public const DATA_FLAG_FACING_TARGET_TO_RANGE_ATTACK = 88;
	public const DATA_FLAG_HIDDEN_WHEN_INVISIBLE = 89; //??????????????????
	public const DATA_FLAG_IS_IN_UI = 90;
	public const DATA_FLAG_STALKING = 91;
	public const DATA_FLAG_EMOTING = 92;
	public const DATA_FLAG_CELEBRATING = 93;
	public const DATA_FLAG_ADMIRING = 94;
	public const DATA_FLAG_CELEBRATING_SPECIAL = 95;
	public const DATA_FLAG_OUT_OF_CONTROL = 96;
	public const DATA_FLAG_RAM_ATTACK = 97;
	public const DATA_FLAG_PLAYING_DEAD = 98;
	public const DATA_FLAG_IN_ASCENDABLE_BLOCK = 99;
	public const DATA_FLAG_OVER_DESCENDABLE_BLOCK = 100;
	public const DATA_FLAG_CROAKING = 101;
	public const DATA_FLAG_EAT_MOB = 102;
	public const DATA_FLAG_JUMP_GOAL_JUMP = 103;
	public const DATA_FLAG_EMERGING = 104;
	public const DATA_FLAG_SNIFFING = 105;
	public const DATA_FLAG_DIGGING = 106;
	public const DATA_FLAG_SONIC_BOOM = 107;
	public const DATA_FLAG_HAS_DASH_COOLDOWN = 108;
	public const DATA_FLAG_PUSH_TOWARDS_CLOSEST_SPACE = 109;
	public const DATA_FLAG_SCENTING = 110;
	public const DATA_FLAG_RISING = 111;
	public const DATA_FLAG_HAPPY = 112;
	public const DATA_FLAG_SEARCHING = 113;
	public const DATA_FLAG_CRAWLING = 114;
	public const DATA_FLAG_TIMER_FLAG_1 = 115;
	public const DATA_FLAG_TIMER_FLAG_2 = 116;
	public const DATA_FLAG_TIMER_FLAG_3 = 117;
	public const DATA_FLAG_BODY_ROTATION_BLOCKED = 118;
	public const DATA_FLAG_RENDER_WHEN_INVISIBLE = 119;
	public const DATA_FLAG_ROTATION_AXIS_ALIGNED = 120;
	public const DATA_FLAG_COLLIDABLE = 121;
	public const DATA_FLAG_WASD_FREE_CAMERA_CONTROLLED = 122;
	public const DATA_FLAGDOES_SERVER_AUTH_ONLY_DISMOUNT = 123;
	public const DATA_FLAGBODY_ROTATION_ALWAYS_FOLLOWS_HEAD = 124;

}
