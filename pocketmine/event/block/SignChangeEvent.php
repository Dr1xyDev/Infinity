<?php
/*    
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█    
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░    
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.1
 *               InfinityProject By @Dr1xyDev    
 *   YT:         @Dr1xyDev    
 *   GitHub:     github.com/Dr1xyDev/Infinity    
*/

namespace pocketmine\event\block;

use pocketmine\block\Block;
use pocketmine\event\Cancellable;
use pocketmine\Player;

class SignChangeEvent extends BlockEvent implements Cancellable{
	public static $handlerList = null;

	
	private $player;
	
	private $lines = [];

	
	public function __construct(Block $theBlock, Player $thePlayer, array $theLines){
		parent::__construct($theBlock);
		$this->player = $thePlayer;
		$this->lines = $theLines;
	}

	
	public function getPlayer(){
		return $this->player;
	}

	
	public function getLines(){
		return $this->lines;
	}

	
	public function getLine($index){
		return $this->lines[$index];
	}

	
	public function setLine($index, $line){
		$this->lines[$index] = $line;
	}
}