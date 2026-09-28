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

namespace pocketmine\level\generator\vanilla\network\mcpe\protocol\types\entity;

/**
 * Affects how the modifier value is applied to the target attribute. These operations are described on the Minecraft
 * Wiki at https://minecraft.fandom.com/wiki/Attribute
 *
 * These operations are listed in the order that they are applied in.
 */
final class AttributeModifierOperation
{
	private function __construct()
	{
		//NOOP
	}

	/** Adds the modifier value to the attribute's base value. */
	public const ADD = 0;
	/**
	 * Multiplies the value by (1 + x), where x is the sum of all MULTIPLY_BASE modifiers' amounts. Multiple modifiers
	 * of this type have additive effects on each other.
	 */
	public const MULTIPLY_BASE = 1;
	/**
	 * Each modifier of this type multiplies the value by (1 + x), where x is the current modifier's value. Multiple
	 * modifiers of this type have multiplicative effects on each other.
	 */
	public const MULTIPLY_TOTAL = 2;
	/**
	 * Limits the result value. If the result value is greater than the limit, it is set to the limit.
	 */
	public const CAP = 3;
}
