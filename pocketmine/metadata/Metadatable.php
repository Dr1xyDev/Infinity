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

interface Metadatable{

	
	public function setMetadata($metadataKey, MetadataValue $newMetadataValue);

	
	public function getMetadata($metadataKey);

	
	public function hasMetadata($metadataKey);

	
	public function removeMetadata($metadataKey, Plugin $owningPlugin);

}