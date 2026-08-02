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
use pocketmine\event\TimingsHandler;
use pocketmine\event\TranslationContainer;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

abstract class Command{
	
	private $name;

	
	private $nextLabel;

	
	private $label;

	
	private $aliases = [];

	
	private $activeAliases = [];

	
	private $commandMap = null;

	
	protected $description = "";

	
	protected $usageMessage;

	
	private $permission = null;

	
	private $permissionMessage = null;

	
	public $timings;

	
	public function __construct($name, $description = "", $usageMessage = null, array $aliases = []){
		$this->name = $name;
		$this->nextLabel = $name;
		$this->label = $name;
		$this->description = $description;
		$this->usageMessage = $usageMessage === null ? "/" . $name : $usageMessage;
		$this->aliases = $aliases;
		$this->activeAliases = (array) $aliases;
		$this->timings = new TimingsHandler("** Command: " . $name);
	}

	
	public abstract function execute(CommandSender $sender, $commandLabel, array $args);

	
	public function getName() : string{
		return $this->name;
	}

	
	public function getPermission(){
		return $this->permission;
	}

	
	public function setPermission($permission){
		$this->permission = $permission;
	}

	
	public function testPermission(CommandSender $target){
		if($this->testPermissionSilent($target)){
			return true;
		}

		if($this->permissionMessage === null){
			$target->sendMessage(new TranslationContainer(TextFormat::RED . "%commands.generic.permission"));
		}elseif($this->permissionMessage !== ""){
			$target->sendMessage(str_replace("<permission>", $this->permission, $this->permissionMessage));
		}

		return false;
	}

	
	public function testPermissionSilent(CommandSender $target){
		if($this->permission === null or $this->permission === ""){
			return true;
		}

		foreach(explode(";", $this->permission) as $permission){
			if($target->hasPermission($permission)){
				return true;
			}
		}

		return false;
	}

	
	public function getLabel(){
		return $this->label;
	}

	public function setLabel($name){
		$this->nextLabel = $name;
		if(!$this->isRegistered()){
			$this->timings = new TimingsHandler("** Command: " . $name);
			$this->label = $name;

			return true;
		}

		return false;
	}

	
	public function register(CommandMap $commandMap){
		if($this->allowChangesFrom($commandMap)){
			$this->commandMap = $commandMap;

			return true;
		}

		return false;
	}

	
	public function unregister(CommandMap $commandMap){
		if($this->allowChangesFrom($commandMap)){
			$this->commandMap = null;
			$this->activeAliases = $this->aliases;
			$this->label = $this->nextLabel;

			return true;
		}

		return false;
	}

	
	private function allowChangesFrom(CommandMap $commandMap){
		return $this->commandMap === null or $this->commandMap === $commandMap;
	}

	
	public function isRegistered(){
		return $this->commandMap !== null;
	}

	
	public function getAliases(){
		return $this->activeAliases;
	}

	
	public function getPermissionMessage(){
		return $this->permissionMessage;
	}

	
	public function getDescription(){
		return $this->description;
	}

	
	public function getUsage(){
		return $this->usageMessage;
	}

	
	public function setAliases(array $aliases){
		$this->aliases = $aliases;
		if(!$this->isRegistered()){
			$this->activeAliases = (array) $aliases;
		}
	}

	
	public function setDescription($description){
		$this->description = $description;
	}

	
	public function setPermissionMessage($permissionMessage){
		$this->permissionMessage = $permissionMessage;
	}

	
	public function setUsage($usage){
		$this->usageMessage = $usage;
	}

	
	public static function broadcastCommandMessage(CommandSender $source, $message, $sendToSource = true){
		if($message instanceof TextContainer){
			$m = clone $message;
			$result = "[".$source->getName().": ".($source->getServer()->getLanguage()->get($m->getText()) !== $m->getText() ? "%" : "") . $m->getText() ."]";

			$users = $source->getServer()->getPluginManager()->getPermissionSubscriptions(Server::BROADCAST_CHANNEL_ADMINISTRATIVE);
			$colored = TextFormat::GRAY . TextFormat::ITALIC . $result;

			$m->setText($result);
			$result = clone $m;
			$m->setText($colored);
			$colored = clone $m;
		}else{
			$users = $source->getServer()->getPluginManager()->getPermissionSubscriptions(Server::BROADCAST_CHANNEL_ADMINISTRATIVE);
			$result = new TranslationContainer("chat.type.admin", [$source->getName(), $message]);
			$colored = new TranslationContainer(TextFormat::GRAY . TextFormat::ITALIC . "%chat.type.admin", [$source->getName(), $message]);
		}

		if($sendToSource === true and !($source instanceof ConsoleCommandSender)){
			$source->sendMessage($message);
		}

		foreach($users as $user){
			if($user instanceof CommandSender){
				if($user instanceof ConsoleCommandSender){
					$user->sendMessage($result);
				}elseif($user !== $source){
					$user->sendMessage($colored);
				}
			}
		}
	}

	
	public function __toString(){
		return $this->name;
	}
}
