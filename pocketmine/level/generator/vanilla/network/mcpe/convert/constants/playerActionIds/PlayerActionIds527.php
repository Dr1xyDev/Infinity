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

namespace pocketmine\level\generator\vanilla\network\mcpe\convert\constants\playerActionIds;

final class PlayerActionIds527
{
	private function __construct()
	{
		//NOOP
	}

	public const ACTION_START_BREAK = 0;
	public const ACTION_ABORT_BREAK = 1;
	public const ACTION_STOP_BREAK = 2;
	public const ACTION_GET_UPDATED_BLOCK = 3;
	public const ACTION_DROP_ITEM = 4;
	public const ACTION_START_SLEEPING = 5;
	public const ACTION_STOP_SLEEPING = 6;
	public const ACTION_RESPAWN = 7;
	public const ACTION_JUMP = 8;
	public const ACTION_START_SPRINT = 9;
	public const ACTION_STOP_SPRINT = 10;
	public const ACTION_START_SNEAK = 11;
	public const ACTION_STOP_SNEAK = 12;
	public const ACTION_CREATIVE_PLAYER_DESTROY_BLOCK = 13;
	public const ACTION_DIMENSION_CHANGE_ACK = 14; //sent when spawning in a different dimension to tell the server we spawned
	public const ACTION_START_GLIDE = 15;
	public const ACTION_STOP_GLIDE = 16;
	public const ACTION_BUILD_DENIED = 17;
	public const ACTION_CRACK_BREAK = 18;
	public const ACTION_CHANGE_SKIN = 19;
	public const ACTION_SET_ENCHANTMENT_SEED = 20; //no longer used
	public const ACTION_START_SWIMMING = 21;
	public const ACTION_STOP_SWIMMING = 22;
	public const ACTION_START_SPIN_ATTACK = 23;
	public const ACTION_STOP_SPIN_ATTACK = 24;
	public const ACTION_INTERACT_BLOCK = 25;
	public const ACTION_PREDICT_DESTROY_BLOCK = 26;
	public const ACTION_CONTINUE_DESTROY_BLOCK = 27;
	public const ACTION_START_ITEM_USE_ON = 28;
	public const ACTION_STOP_ITEM_USE_ON = 29;

}
