<?php
/*    
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█    
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░    
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.1
 *               InfinityProject By @Dr1xyDev    
 *   YT:         @Dr1xyDev    
 *   GitHub:     github.com/Dr1xyDev/Infinity    
*/

namespace pocketmine\event\player;

use pocketmine\block\Block;
use pocketmine\event\Cancellable;
use pocketmine\item\Item;
use pocketmine\Player;

abstract class PlayerBucketEvent extends PlayerEvent implements Cancellable{

	
	private $blockClicked;
	
	private $blockFace;
	
	private $bucket;
	
	private $item;

	
	public function __construct(Player $who, Block $blockClicked, $blockFace, Item $bucket, Item $itemInHand){
		$this->player = $who;
		$this->blockClicked = $blockClicked;
		$this->blockFace = (int) $blockFace;
		$this->item = $itemInHand;
		$this->bucket = $bucket;
	}

	
	public function getBucket(){
		return $this->bucket;
	}

	
	public function getItem(){
		return $this->item;
	}

	
	public function setItem(Item $item){
		$this->item = $item;
	}

	
	public function getBlockClicked(){
		return $this->blockClicked;
	}

	
	public function getBlockFace(){
		return $this->blockFace;
	}
}