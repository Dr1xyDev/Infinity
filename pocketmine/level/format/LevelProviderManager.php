<?php
/*    
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█    
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░    
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.1
 *               InfinityProject By @Dr1xyDev    
 *   YT:         @Dr1xyDev    
 *   GitHub:     github.com/Dr1xyDev/Infinity    
*/

namespace pocketmine\level\format;

use pocketmine\Server;
use pocketmine\utils\LevelException;

abstract class LevelProviderManager{
	protected static $providers = [];

	
	public static function addProvider(Server $server, $class){
		if(!is_subclass_of($class, LevelProvider::class)){
			throw new LevelException("Class is not a subclass of LevelProvider");
		}
		
		self::$providers[strtolower($class::getProviderName())] = $class;
	}

	
	public static function getProvider($path){
		foreach(self::$providers as $provider){
			
			if($provider::isValid($path)){
				return $provider;
			}
		}

		return null;
	}

	public static function getProviderByName($name){
		$name = trim(strtolower($name));

		return isset(self::$providers[$name]) ? self::$providers[$name] : null;
	}
}