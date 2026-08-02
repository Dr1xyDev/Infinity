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
use pocketmine\level\Level;
use pocketmine\network\Network;
use pocketmine\network\protocol\BlockEventPacket;
use pocketmine\Player;

use pocketmine\tile\Chest;

class DoubleChestInventory extends ChestInventory implements InventoryHolder{
	
	private $left;
	
	private $right;

	public function __construct(Chest $left, Chest $right){
		$this->left = $left->getRealInventory();
		$this->right = $right->getRealInventory();
		$items = array_merge($this->left->getContents(true), $this->right->getContents(true));
		BaseInventory::__construct($this, InventoryType::get(InventoryType::DOUBLE_CHEST), $items);
	}

	public function getInventory(){
		return $this;
	}

	public function getHolder(){
		return $this->left->getHolder();
	}

	public function getItem($index){
		return $index < $this->left->getSize() ? $this->left->getItem($index) : $this->right->getItem($index - $this->right->getSize());
	}

	public function setItem($index, Item $item, $send = true){
		return $index < $this->left->getSize() ? $this->left->setItem($index, $item, $send) : $this->right->setItem($index - $this->right->getSize(), $item, $send);
	}

	public function clear($index, $send = true){
		return $index < $this->left->getSize() ? $this->left->clear($index, $send) : $this->right->clear($index - $this->right->getSize(), $send);
	}

	public function getContents($withAir = false){
		$contents = [];
		for($i = 0; $i < $this->getSize(); ++$i){
			$contents[$i] = $this->getItem($i);
		}

		return $contents;
	}

	
	public function setContents(array $items, $send = true){
		if(count($items) > $this->size){
			$items = array_slice($items, 0, $this->size, true);
		}

		for($i = 0; $i < $this->size; ++$i){
			if(!isset($items[$i])){
				if ($i < $this->left->size){
					if(isset($this->left->slots[$i])){
						$this->clear($i, $send);
					}
				}elseif(isset($this->right->slots[$i - $this->left->size])){
					$this->clear($i, $send);
				}
			}elseif(!$this->setItem($i, $items[$i], $send)){
				$this->clear($i, $send);
			}
		}
	}

	public function onOpen(Player $who){
		parent::onOpen($who);

		if(count($this->getViewers()) === 1){
			$pk = new BlockEventPacket();
			$pk->x = $this->right->getHolder()->getX();
			$pk->y = $this->right->getHolder()->getY();
			$pk->z = $this->right->getHolder()->getZ();
			$pk->case1 = 1;
			$pk->case2 = 2;
			if(($level = $this->right->getHolder()->getLevel()) instanceof Level){
				$level->addChunkPacket($this->right->getHolder()->getX() >> 4, $this->right->getHolder()->getZ() >> 4, $pk);
			}
		}
	}

	public function onClose(Player $who){
		if(count($this->getViewers()) === 1){
			$pk = new BlockEventPacket();
			$pk->x = $this->right->getHolder()->getX();
			$pk->y = $this->right->getHolder()->getY();
			$pk->z = $this->right->getHolder()->getZ();
			$pk->case1 = 1;
			$pk->case2 = 0;
			if(($level = $this->right->getHolder()->getLevel()) instanceof Level){
				$level->addChunkPacket($this->right->getHolder()->getX() >> 4, $this->right->getHolder()->getZ() >> 4, $pk);
			}
		}
		parent::onClose($who);
	}

	
	public function getLeftSide(){
		return $this->left;
	}

	
	public function getRightSide(){
		return $this->right;
	}
}