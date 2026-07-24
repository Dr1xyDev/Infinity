<?php

/*
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.0
 *               InfinityProject By @Dr1xyDev
 *   YT:         @Dr1xyDev
 *   GitHub:     github.com/Dr1xyDev/Infinity
*/

namespace pocketmine\level\generator\normal\math;

/**
 * Helper class for directional facing calculations.
 * Provides utilities for working with horizontal directions (north, south, east, west)
 * following Minecraft's directional convention.
 */
class FacingHelper{

	/**
	 * Horizontal facing directions following Minecraft convention:
	 * 2 = North, 3 = South, 4 = West, 5 = East
	 */
	const HORIZONTAL = [2, 3, 4, 5];

	/**
	 * Returns the opposite facing direction.
	 *
	 * North (2) <-> South (3)
	 * West (4) <-> East (5)
	 *
	 * @param int $face The facing direction
	 * @return int The opposite facing direction
	 */
	public static function opposite(int $face) : int{
		switch($face){
			case 2: return 3; // North -> South
			case 3: return 2; // South -> North
			case 4: return 5; // West -> East
			case 5: return 4; // East -> West
			default: return $face;
		}
	}

	/**
	 * Returns the X-axis offset for a given facing direction.
	 *
	 * @param int $face The facing direction
	 * @return int The X offset (-1, 0, or 1)
	 */
	public static function xOffset(int $face) : int{
		switch($face){
			case 4: return -1; // West
			case 5: return 1;  // East
			default: return 0; // North/South
		}
	}

	/**
	 * Returns the Z-axis offset for a given facing direction.
	 *
	 * @param int $face The facing direction
	 * @return int The Z offset (-1, 0, or 1)
	 */
	public static function zOffset(int $face) : int{
		switch($face){
			case 2: return -1; // North
			case 3: return 1;  // South
			default: return 0; // West/East
		}
	}
}
