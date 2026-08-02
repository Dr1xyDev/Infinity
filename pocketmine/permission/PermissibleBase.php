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

use pocketmine\event\Timings;
use pocketmine\plugin\Plugin;
use pocketmine\Server;
use pocketmine\utils\PluginException;

class PermissibleBase implements Permissible{
	
	private $opable = null;

	
	private $parent = null;

	
	private $attachments = [];

	
	private $permissions = [];

	
	public function __construct(ServerOperator $opable){
		$this->opable = $opable;
		if($opable instanceof Permissible){
			$this->parent = $opable;
		}
	}

	public function __destruct(){
		$this->parent = null;
		$this->opable = null;
	}

	
	public function isOp(){
		if($this->opable === null){
			return false;
		}else{
			return $this->opable->isOp();
		}
	}

	
	public function setOp($value){
		if($this->opable === null){
			throw new \LogicException("Cannot change op value as no ServerOperator is set");
		}else{
			$this->opable->setOp($value);
		}
	}

	
	public function isPermissionSet($name){
		return isset($this->permissions[$name instanceof Permission ? $name->getName() : $name]);
	}

	
	public function hasPermission($name){
		if($name instanceof Permission){
			$name = $name->getName();
		}

		if($this->isPermissionSet($name)){
			return $this->permissions[$name]->getValue();
		}

		if(($perm = Server::getInstance()->getPluginManager()->getPermission($name)) !== null){
			$perm = $perm->getDefault();

			return $perm === Permission::DEFAULT_TRUE or ($this->isOp() and $perm === Permission::DEFAULT_OP) or (!$this->isOp() and $perm === Permission::DEFAULT_NOT_OP);
		}else{
			return Permission::$DEFAULT_PERMISSION === Permission::DEFAULT_TRUE or ($this->isOp() and Permission::$DEFAULT_PERMISSION === Permission::DEFAULT_OP) or (!$this->isOp() and Permission::$DEFAULT_PERMISSION === Permission::DEFAULT_NOT_OP);
		}

	}

	
	public function addAttachment(Plugin $plugin, $name = null, $value = null){
		if($plugin === null){
			throw new PluginException("Plugin cannot be null");
		}elseif(!$plugin->isEnabled()){
			throw new PluginException("Plugin " . $plugin->getDescription()->getName() . " is disabled");
		}

		$result = new PermissionAttachment($plugin, $this->parent !== null ? $this->parent : $this);
		$this->attachments[spl_object_hash($result)] = $result;
		if($name !== null and $value !== null){
			$result->setPermission($name, $value);
		}

		$this->recalculatePermissions();

		return $result;
	}

	
	public function removeAttachment(PermissionAttachment $attachment){
		if($attachment === null){
			throw new \InvalidStateException("Attachment cannot be null");
		}

		if(isset($this->attachments[spl_object_hash($attachment)])){
			unset($this->attachments[spl_object_hash($attachment)]);
			if(($ex = $attachment->getRemovalCallback()) !== null){
				$ex->attachmentRemoved($attachment);
			}

			$this->recalculatePermissions();

		}

	}

	public function recalculatePermissions(){
		Timings::$permissibleCalculationTimer->startTiming();

		$this->clearPermissions();
		$defaults = Server::getInstance()->getPluginManager()->getDefaultPermissions($this->isOp());
		Server::getInstance()->getPluginManager()->subscribeToDefaultPerms($this->isOp(), $this->parent !== null ? $this->parent : $this);

		foreach($defaults as $perm){
			$name = $perm->getName();
			$this->permissions[$name] = new PermissionAttachmentInfo($this->parent !== null ? $this->parent : $this, $name, null, true);
			Server::getInstance()->getPluginManager()->subscribeToPermission($name, $this->parent !== null ? $this->parent : $this);
			$this->calculateChildPermissions($perm->getChildren(), false, null);
		}

		foreach($this->attachments as $attachment){
			$this->calculateChildPermissions($attachment->getPermissions(), false, $attachment);
		}

		Timings::$permissibleCalculationTimer->stopTiming();
	}

	public function clearPermissions(){
		foreach(array_keys($this->permissions) as $name){
			Server::getInstance()->getPluginManager()->unsubscribeFromPermission($name, $this->parent !== null ? $this->parent : $this);
		}

		Server::getInstance()->getPluginManager()->unsubscribeFromDefaultPerms(false, $this->parent !== null ? $this->parent : $this);
		Server::getInstance()->getPluginManager()->unsubscribeFromDefaultPerms(true, $this->parent !== null ? $this->parent : $this);

		$this->permissions = [];
	}

	
	private function calculateChildPermissions(array $children, $invert, $attachment){
		foreach($children as $name => $v){
			$perm = Server::getInstance()->getPluginManager()->getPermission($name);
			$value = ($v xor $invert);
			$this->permissions[$name] = new PermissionAttachmentInfo($this->parent !== null ? $this->parent : $this, $name, $attachment, $value);
			Server::getInstance()->getPluginManager()->subscribeToPermission($name, $this->parent !== null ? $this->parent : $this);

			if($perm instanceof Permission){
				$this->calculateChildPermissions($perm->getChildren(), !$value, $attachment);
			}
		}
	}

	
	public function getEffectivePermissions(){
		return $this->permissions;
	}
}
