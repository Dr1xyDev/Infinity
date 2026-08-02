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

class PermissionAttachmentInfo{
	
	private $permissible;

	
	private $permission;

	
	private $attachment;

	
	private $value;

	
	public function __construct(Permissible $permissible, $permission, $attachment, $value){
		if($permission === null){
			throw new \InvalidStateException("Permission may not be null");
		}

		$this->permissible = $permissible;
		$this->permission = $permission;
		$this->attachment = $attachment;
		$this->value = $value;
	}

	
	public function getPermissible(){
		return $this->permissible;
	}

	
	public function getPermission(){
		return $this->permission;
	}

	
	public function getAttachment(){
		return $this->attachment;
	}

	
	public function getValue(){
		return $this->value;
	}
}