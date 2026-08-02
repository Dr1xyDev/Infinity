<?php
/*    
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█    
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░    
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.1
 *               InfinityProject By @Dr1xyDev    
 *   YT:         @Dr1xyDev    
 *   GitHub:     github.com/Dr1xyDev/Infinity    
*/

namespace pocketmine\permission;

use pocketmine\Server;

class Permission{
	const DEFAULT_OP = "op";
	const DEFAULT_NOT_OP = "notop";
	const DEFAULT_TRUE = "true";
	const DEFAULT_FALSE = "false";

	public static $DEFAULT_PERMISSION = self::DEFAULT_OP;

	
	public static function getByName($value){
		if(is_bool($value)){
			if($value === true){
				return "true";
			}else{
				return "false";
			}
		}
		switch(strtolower($value)){
			case "op":
			case "isop":
			case "operator":
			case "isoperator":
			case "admin":
			case "isadmin":
				return self::DEFAULT_OP;

			case "!op":
			case "notop":
			case "!operator":
			case "notoperator":
			case "!admin":
			case "notadmin":
				return self::DEFAULT_NOT_OP;

			case "true":
				return self::DEFAULT_TRUE;

			default:
				return self::DEFAULT_FALSE;
		}
	}

	
	private $name;

	
	private $description;

	
	private $children = [];

	
	private $defaultValue;

	
	public function __construct($name, $description = null, $defaultValue = null, array $children = []){
		$this->name = $name;
		$this->description = $description !== null ? $description : "";
		$this->defaultValue = $defaultValue !== null ? $defaultValue : self::$DEFAULT_PERMISSION;
		$this->children = $children;

		$this->recalculatePermissibles();
	}

	
	public function getName() : string{
		return $this->name;
	}

	
	public function &getChildren(){
		return $this->children;
	}

	
	public function getDefault(){
		return $this->defaultValue;
	}

	
	public function setDefault($value){
		if($value !== $this->defaultValue){
			$this->defaultValue = $value;
			$this->recalculatePermissibles();
		}
	}

	
	public function getDescription(){
		return $this->description;
	}

	
	public function setDescription($value){
		$this->description = $value;
	}

	
	public function getPermissibles(){
		return Server::getInstance()->getPluginManager()->getPermissionSubscriptions($this->name);
	}

	public function recalculatePermissibles(){
		$perms = $this->getPermissibles();

		Server::getInstance()->getPluginManager()->recalculatePermissionDefaults($this);

		foreach($perms as $p){
			$p->recalculatePermissions();
		}
	}

	
	public function addParent($name, $value){
		if($name instanceof Permission){
			$name->getChildren()[$this->getName()] = $value;
			$name->recalculatePermissibles();
			return;
		}else{
			$perm = Server::getInstance()->getPluginManager()->getPermission($name);
			if($perm === null){
				$perm = new Permission($name);
				Server::getInstance()->getPluginManager()->addPermission($perm);
			}

			$this->addParent($perm, $value);

			return $perm;
		}
	}

	
	public static function loadPermissions(array $data, $default = self::DEFAULT_OP){
		$result = [];
		foreach($data as $key => $entry){
			$result[] = self::loadPermission($key, $entry, $default, $result);
		}

		return $result;
	}

	
	public static function loadPermission($name, array $data, $default = self::DEFAULT_OP, &$output = []){
		$desc = null;
		$children = [];
		if(isset($data["default"])){
			$value = Permission::getByName($data["default"]);
			if($value !== null){
				$default = $value;
			}else{
				throw new \InvalidStateException("'default' key contained unknown value");
			}
		}

		if(isset($data["children"])){
			if(is_array($data["children"])){
				foreach($data["children"] as $k => $v){
					if(is_array($v)){
						if(($perm = self::loadPermission($k, $v, $default, $output)) !== null){
							$output[] = $perm;
						}
					}
					$children[$k] = true;
				}
			}else{
				throw new \InvalidStateException("'children' key is of wrong type");
			}
		}

		if(isset($data["description"])){
			$desc = $data["description"];
		}

		return new Permission($name, $desc, $default, $children);

	}

}