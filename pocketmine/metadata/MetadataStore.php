<?php
/*    
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█    
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░    
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.1
 *               InfinityProject By @Dr1xyDev    
 *   YT:         @Dr1xyDev    
 *   GitHub:     github.com/Dr1xyDev/Infinity    
*/

namespace pocketmine\metadata;

use pocketmine\plugin\Plugin;
use pocketmine\utils\PluginException;

abstract class MetadataStore{
	
	private $metadataMap;

	
	public function setMetadata($subject, $metadataKey, MetadataValue $newMetadataValue){
		$owningPlugin = $newMetadataValue->getOwningPlugin();
		if($owningPlugin === null){
			throw new PluginException("Plugin cannot be null");
		}

		$key = $this->disambiguate($subject, $metadataKey);
		if(!isset($this->metadataMap[$key])){
			
			$this->metadataMap[$key] = new \SplObjectStorage();
		}else{
			$entry = $this->metadataMap[$key];
		}
		$entry[$owningPlugin] = $newMetadataValue;
	}

	
	public function getMetadata($subject, $metadataKey){
		$key = $this->disambiguate($subject, $metadataKey);
		if(isset($this->metadataMap[$key])){
			return $this->metadataMap[$key];
		}else{
			return [];
		}
	}

	
	public function hasMetadata($subject, $metadataKey){
		return isset($this->metadataMap[$this->disambiguate($subject, $metadataKey)]);
	}

	
	public function removeMetadata($subject, $metadataKey, Plugin $owningPlugin){
		$key = $this->disambiguate($subject, $metadataKey);
		if(isset($this->metadataMap[$key])){
			unset($this->metadataMap[$key][$owningPlugin]);
			if($this->metadataMap[$key]->count() === 0){
				unset($this->metadataMap[$key]);
			}
		}
	}

	
	public function invalidateAll(Plugin $owningPlugin){
		
		foreach($this->metadataMap as $values){
			if(isset($values[$owningPlugin])){
				$values[$owningPlugin]->invalidate();
			}
		}
	}

	
	public abstract function disambiguate(Metadatable $subject, $metadataKey);
}