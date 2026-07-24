<?php

/*
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.0
 *               InfinityProject By @Dr1xyDev
 *   YT:         @Dr1xyDev
 *   GitHub:     github.com/Dr1xyDev/Infinity
*/

namespace pocketmine\math;


abstract class VectorMath{

	public static function getDirection2D($azimuth) : Vector2{
		return new Vector2(cos($azimuth), sin($azimuth));
	}

	public static function getDirection3D($azimuth, $inclination) : Vector3{
		$yFact = cos($inclination);
		return new Vector3($yFact * cos($azimuth), sin($inclination), $yFact * sin($azimuth));
	}

}