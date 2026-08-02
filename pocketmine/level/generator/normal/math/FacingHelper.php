<?php
/*    
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█    
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░    
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.1
 *               InfinityProject By @Dr1xyDev    
 *   YT:         @Dr1xyDev    
 *   GitHub:     github.com/Dr1xyDev/Infinity    
*/

namespace pocketmine\level\generator\normal\math;

class FacingHelper{

	
	const HORIZONTAL = [2, 3, 4, 5];

	
	public static function opposite(int $face) : int{
		switch($face){
			case 2: return 3; 
			case 3: return 2; 
			case 4: return 5; 
			case 5: return 4; 
			default: return $face;
		}
	}

	
	public static function xOffset(int $face) : int{
		switch($face){
			case 4: return -1; 
			case 5: return 1;  
			default: return 0; 
		}
	}

	
	public static function zOffset(int $face) : int{
		switch($face){
			case 2: return -1; 
			case 3: return 1;  
			default: return 0; 
		}
	}
}
