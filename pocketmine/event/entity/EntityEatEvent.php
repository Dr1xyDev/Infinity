<?php
/*    
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█    
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░    
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.1
 *               InfinityProject By @Dr1xyDev    
 *   YT:         @Dr1xyDev    
 *   GitHub:     github.com/Dr1xyDev/Infinity    
*/

namespace pocketmine\event\entity;

use pocketmine\entity\Effect;
use pocketmine\entity\Entity;
use pocketmine\event\Cancellable;
use pocketmine\item\FoodSource;

class EntityEatEvent extends EntityEvent implements Cancellable{
	public static $handlerList = null;

	
	private $foodSource;
	
	private $foodRestore;
	
	private $saturationRestore;
	private $residue;
	
	private $additionalEffects;

	public function __construct(Entity $entity, FoodSource $foodSource){
		$this->entity = $entity;
		$this->foodSource = $foodSource;
		$this->foodRestore = $foodSource->getFoodRestore();
		$this->saturationRestore = $foodSource->getSaturationRestore();
		$this->residue = $foodSource->getResidue();
		$this->additionalEffects = $foodSource->getAdditionalEffects();
	}

	public function getFoodSource(){
		return $this->foodSource;
	}

	public function getFoodRestore() : int{
		return $this->foodRestore;
	}

	public function setFoodRestore(int $foodRestore){
		$this->foodRestore = $foodRestore;
	}

	public function getSaturationRestore() : float{
		return $this->saturationRestore;
	}

	public function setSaturationRestore(float $saturationRestore){
		$this->saturationRestore = $saturationRestore;
	}

	public function getResidue(){
		return $this->residue;
	}

	public function setResidue($residue){
		$this->residue = $residue;
	}

	
	public function getAdditionalEffects(){
		return $this->additionalEffects;
	}

	
	public function setAdditionalEffects(array $additionalEffects){
		foreach($additionalEffects as $effect){
			if(!($effect instanceof Effect)){
				throw new \TypeError("Argument 1 passed to EntityEatEvent::setAdditionalEffects() must be an Effect array");
			}
		}
		$this->additionalEffects = $additionalEffects;
	}
}
