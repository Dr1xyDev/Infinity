<?php
/*    
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█    
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░    
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.1
 *               InfinityProject By @Dr1xyDev    
 *   YT:         @Dr1xyDev    
 *   GitHub:     github.com/Dr1xyDev/Infinity    
*/

namespace pocketmine\network;

use pocketmine\network\protocol\DataPacket;
use pocketmine\Player;

interface SourceInterface{

	
	public function putPacket(Player $player,  $packet, $needACK = false, $immediate = true);

	
	public function close(Player $player, $reason = "unknown reason");

	
	public function setName($name);

	
	public function process();

	public function shutdown();

	public function emergencyShutdown();

}