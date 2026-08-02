<?php
/*    
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█    
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░    
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.1
 *               InfinityProject By @Dr1xyDev    
 *   YT:         @Dr1xyDev    
 *   GitHub:     github.com/Dr1xyDev/Infinity    
*/

namespace pocketmine\level;

use pocketmine\entity\Entity;
use pocketmine\math\Vector3;

class MovingObjectPosition{

	
	public $typeOfHit;

	public $blockX;
	public $blockY;
	public $blockZ;

	
	public $sideHit;

	
	public $hitVector;

	
	public $entityHit = null;

	protected function __construct(){

	}

	
	public static function fromBlock($x, $y, $z, $side, Vector3 $hitVector){
		$ob = new MovingObjectPosition;
		$ob->typeOfHit = 0;
		$ob->blockX = $x;
		$ob->blockY = $y;
		$ob->blockZ = $z;
		$ob->hitVector = new Vector3($hitVector->x, $hitVector->y, $hitVector->z);
		return $ob;
	}

	
	public static function fromEntity(Entity $entity){
		$ob = new MovingObjectPosition;
		$ob->typeOfHit = 1;
		$ob->entityHit = $entity;
		$ob->hitVector = new Vector3($entity->x, $entity->y, $entity->z);
		return $ob;
	}
}
