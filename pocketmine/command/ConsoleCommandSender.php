<?php
/*    
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█    
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░    
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.1
 *               InfinityProject By @Dr1xyDev    
 *   YT:         @Dr1xyDev    
 *   GitHub:     github.com/Dr1xyDev/Infinity    
*/

namespace pocketmine\command;

use pocketmine\event\TextContainer;
use pocketmine\permission\PermissibleBase;
use pocketmine\permission\PermissionAttachment;
use pocketmine\plugin\Plugin;
use pocketmine\Server;
use pocketmine\utils\MainLogger;

class ConsoleCommandSender implements CommandSender{

	private $perm;

	public function __construct(){
		$this->perm = new PermissibleBase($this);
	}

	
	public function isPermissionSet($name){
		return $this->perm->isPermissionSet($name);
	}

	
	public function hasPermission($name){
		return $this->perm->hasPermission($name);
	}

	
	public function addAttachment(Plugin $plugin, $name = null, $value = null){
		return $this->perm->addAttachment($plugin, $name, $value);
	}

	
	public function removeAttachment(PermissionAttachment $attachment){
		$this->perm->removeAttachment($attachment);
	}

	public function recalculatePermissions(){
		$this->perm->recalculatePermissions();
	}

	
	public function getEffectivePermissions(){
		return $this->perm->getEffectivePermissions();
	}

	
	public function isPlayer(){
		return false;
	}

	
	public function getServer(){
		return Server::getInstance();
	}

	
	public function sendMessage($message){
		if($message instanceof TextContainer){
			$message = $this->getServer()->getLanguage()->translate($message);
		}else{
			$message = $this->getServer()->getLanguage()->translateString($message);
		}

		foreach(explode("\n", trim($message)) as $line){
			MainLogger::getLogger()->info($line);
		}
	}

	
	public function getName() : string{
		return "CONSOLE";
	}

	
	public function isOp(){
		return true;
	}

	
	public function setOp($value){

	}

}