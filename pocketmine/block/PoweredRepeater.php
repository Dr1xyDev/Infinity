<?php

/*
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.0
 *               InfinityProject By @Dr1xyDev
 *   YT:         @Dr1xyDev
 *   GitHub:     github.com/Dr1xyDev/Infinity
*/

namespace pocketmine\block;

use pocketmine\item\Item;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\Player;

class PoweredRepeater extends RedstoneSource{
	protected $id = self::POWERED_REPEATER_BLOCK;

	const ACTION_ACTIVATE = "Repeater Activate";
	const ACTION_DEACTIVATE = "Repeater Deactivate";

	public function __construct($meta = 0){
		$this->meta = (int) $meta;
	}

	public function getName() : string{
		return "Powered Repeater";
	}

	public function canBeActivated() : bool{
		return true;
	}

	public function getStrength(){
		return 15;
	}

	public function getDirection() : int{
		$direction = 0;
		switch($this->meta % 4){
			case 0:
				$direction = 3;
				break;
			case 1:
				$direction = 4;
				break;
			case 2:
				$direction = 2;
				break;
			case 3:
				$direction = 5;
				break;
		}
		return $direction;
	}
	
	public function getOppositeDirection() : int{
		return $this->getOppositeSide($this->getDirection());
	}

	public function getDelayLevel() : int{
		return round(($this->meta - ($this->meta % 4)) / 4) + 1;
	}

	public function isActivated(Block $from = null){
		if(!$from instanceof Block){
			return false;
		}else{
			if($this->y != $from->y){
				return false;
			}
			if($from->equals($this->getSide($this->getOppositeDirection()))){
				return true;
			}
			return false;
		}
	}

	public function activate(array $ignore = []){
		if($this->canCalc()){
			if($this->id != self::POWERED_REPEATER_BLOCK){
				$this->id = self::POWERED_REPEATER_BLOCK;
				$this->getLevel()->setBlock($this, $this, true, false);
			}
			$this->getLevel()->setBlockTempData($this, self::ACTION_ACTIVATE);
			$this->getLevel()->scheduleUpdate($this, $this->getDelayLevel() * 2);
		}
	}

	public function deactivate(array $ignore = []){
		if($this->canCalc()){
			if($this->id != self::UNPOWERED_REPEATER_BLOCK){
				$this->id = self::UNPOWERED_REPEATER_BLOCK;
				$this->getLevel()->setBlock($this, $this, true, false);
			}
			$this->getLevel()->setBlockTempData($this, self::ACTION_DEACTIVATE);
			$this->getLevel()->scheduleUpdate($this, $this->getDelayLevel() * 2);
		}
	}

	public function deactivateImmediately(){
		$this->deactivateBlock($this->getSide($this->getOppositeDirection()));
		$this->deactivateBlock($this->getSide(Vector3::SIDE_DOWN, 2));//TODO: improve
	}

	public function onUpdate($type){
		if($type == Level::BLOCK_UPDATE_SCHEDULED){
			if($this->getLevel()->getBlockTempData($this) == self::ACTION_ACTIVATE){
				$this->activateBlock($this->getSide($this->getOppositeDirection()));
				$this->activateBlock($this->getSide(Vector3::SIDE_DOWN, 2));
			}elseif($this->getLevel()->getBlockTempData($this) == self::ACTION_DEACTIVATE){
				$this->deactivateImmediately();
			}
			$this->getLevel()->setBlockTempData($this);
		}
		return $type;
	}

	public function onActivate(Item $item, Player $player = null){
		$meta = $this->meta + 4;
		if($meta > 15) $this->meta = $this->meta % 4;
		else $this->meta = (int) $meta;
		$this->getLevel()->setBlock($this, $this, true, false);
		return true;
	}

	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		if($player instanceof Player){
			$this->meta = ((int) $player->getDirection() + 5) % 4;
		}
		$this->getLevel()->setBlock($block, $this, true, false);
		if($this->checkPower($this)){
			$this->activate();
		}
	}

	public function onBreak(Item $item){
		$this->deactivateImmediately();
		$this->getLevel()->setBlock($this, new Air(), true, false);
		$this->getLevel()->setBlockTempData($this);
	}

	public function getDrops(Item $item) : array{
		return [
			[Item::REPEATER, 0, 1]
		];
	}
}
