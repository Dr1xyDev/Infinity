<?php

/*
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.0
 *               InfinityProject By @Dr1xyDev
 *   YT:         @Dr1xyDev
 *   GitHub:     github.com/Dr1xyDev/Infinity
*/

namespace pocketmine\level\weather;

use pocketmine\level\Level;

/**
 * @deprecated
 */
class WeatherManager{
	/** @var Level[] */
	public static $registeredLevel = [];
	
	public static function registerLevel(Level $level){
		self::$registeredLevel[$level->getName()] = $level;
		return true;
	}
	
	public static function unregisterLevel(Level $level){
		if(isset(self::$registeredLevel[$level->getName()])) {
			unset(self::$registeredLevel[$level->getName()]);
			return true;
		}
		return false;
	}
	
	public static function updateWeather(){
		foreach(self::$registeredLevel as $level) {
			$level->getWeather()->calcWeather($level->getServer()->getTick());
		}
	}
	
	public static function isRegistered(Level $level){
		if(isset(self::$registeredLevel[$level->getName()])) return true;
		return false;
	}

}