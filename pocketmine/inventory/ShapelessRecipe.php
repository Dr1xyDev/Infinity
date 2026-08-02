<?php
/*    
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█    
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░    
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.1
 *               InfinityProject By @Dr1xyDev    
 *   YT:         @Dr1xyDev    
 *   GitHub:     github.com/Dr1xyDev/Infinity    
*/

namespace pocketmine\inventory;

use pocketmine\item\Item;
use pocketmine\Server;
use pocketmine\utils\UUID;

class ShapelessRecipe implements Recipe{
	
	private $output;

	private $id = null;

	
	private $ingredients = [];

	public function __construct(Item $result){
		$this->output = clone $result;
	}

	public function getId(){
		return $this->id;
	}

	public function setId(UUID $id){
		if($this->id !== null){
			throw new \InvalidStateException("Id is already set");
		}

		$this->id = $id;
	}

	public function getResult(){
		return clone $this->output;
	}

	
	public function addIngredient(Item $item){
		if(count($this->ingredients) >= 9){
			throw new \InvalidArgumentException("Shapeless recipes cannot have more than 9 ingredients");
		}

		$it = clone $item;
		$it->setCount(1);

		while($item->getCount() > 0){
			$this->ingredients[] = clone $it;
			$item->setCount($item->getCount() - 1);
		}

		return $this;
	}

	
	public function removeIngredient(Item $item){
		foreach($this->ingredients as $index => $ingredient){
			if($item->getCount() <= 0){
				break;
			}
			if($ingredient->equals($item, $item->getDamage() === null ? false : true, $item->getCompoundTag() === null ? false : true)){
				unset($this->ingredients[$index]);
				$item->setCount($item->getCount() - 1);
			}
		}

		return $this;
	}

	
	public function getIngredientList(){
		$ingredients = [];
		foreach($this->ingredients as $ingredient){
			$ingredients[] = clone $ingredient;
		}

		return $ingredients;
	}

	
	public function getIngredientCount(){
		$count = 0;
		foreach($this->ingredients as $ingredient){
			$count += $ingredient->getCount();
		}

		return $count;
	}

	public function registerToCraftingManager(){
		Server::getInstance()->getCraftingManager()->registerShapelessRecipe($this);
	}
}