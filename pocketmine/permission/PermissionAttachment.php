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

use pocketmine\plugin\Plugin;
use pocketmine\utils\PluginException;

class PermissionAttachment{
	
	private $removed = null;

	
	private $permissions = [];

	
	private $permissible;

	
	private $plugin;

	
	public function __construct(Plugin $plugin, Permissible $permissible){
		if(!$plugin->isEnabled()){
			throw new PluginException("Plugin " . $plugin->getDescription()->getName() . " is disabled");
		}

		$this->permissible = $permissible;
		$this->plugin = $plugin;
	}

	
	public function getPlugin(){
		return $this->plugin;
	}

	
	public function setRemovalCallback(PermissionRemovedExecutor $ex){
		$this->removed = $ex;
	}

	
	public function getRemovalCallback(){
		return $this->removed;
	}

	
	public function getPermissible(){
		return $this->permissible;
	}

	
	public function getPermissions(){
		return $this->permissions;
	}

	
	public function clearPermissions(){
		$this->permissions = [];
		$this->permissible->recalculatePermissions();
	}

	
	public function setPermissions(array $permissions){
		foreach($permissions as $key => $value){
			$this->permissions[$key] = (bool) $value;
		}
		$this->permissible->recalculatePermissions();
	}

	
	public function unsetPermissions(array $permissions){
		foreach($permissions as $node){
			unset($this->permissions[$node]);
		}
		$this->permissible->recalculatePermissions();
	}

	
	public function setPermission($name, $value){
		$name = $name instanceof Permission ? $name->getName() : $name;
		if(isset($this->permissions[$name])){
			if($this->permissions[$name] === $value){
				return;
			}
			unset($this->permissions[$name]); 
		}
		$this->permissions[$name] = $value;
		$this->permissible->recalculatePermissions();
	}

	
	public function unsetPermission($name){
		$name = $name instanceof Permission ? $name->getName() : $name;
		if(isset($this->permissions[$name])){
			unset($this->permissions[$name]);
			$this->permissible->recalculatePermissions();
		}
	}

	
	public function remove(){
		$this->permissible->removeAttachment($this);
	}
}