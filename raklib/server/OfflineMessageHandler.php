<?php
/*    
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█    
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░    
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.1
 *               InfinityProject By @Dr1xyDev    
 *   YT:         @Dr1xyDev    
 *   GitHub:     github.com/Dr1xyDev/Infinity    
*/

declare(strict_types=1);

namespace raklib\server;

use raklib\protocol\OfflineMessage;
use raklib\protocol\OPEN_CONNECTION_REPLY_1;
use raklib\protocol\OPEN_CONNECTION_REPLY_2;
use raklib\protocol\OPEN_CONNECTION_REQUEST_1;
use raklib\protocol\OPEN_CONNECTION_REQUEST_2;
use raklib\protocol\UNCONNECTED_PING;
use raklib\protocol\UNCONNECTED_PONG;

class OfflineMessageHandler{
	
	private $sessionManager;

	public function __construct(SessionManager $manager){
		$this->sessionManager = $manager;
	}

	public function handle(OfflineMessage $packet, string $source, int $port){
		switch($packet::$ID){
			case UNCONNECTED_PING::$ID:
				
				$pk = new UNCONNECTED_PONG();
				$pk->serverID = $this->sessionManager->getID();
				$pk->pingID = $packet->pingID;
				$pk->serverName = $this->sessionManager->getName();
				$this->sessionManager->sendPacket($pk, $source, $port);
				return true;
			case OPEN_CONNECTION_REQUEST_1::$ID:
				
				$packet->protocol; 
				$pk = new OPEN_CONNECTION_REPLY_1();
				$pk->mtuSize = $packet->mtuSize;
				$pk->serverID = $this->sessionManager->getID();
				$this->sessionManager->sendPacket($pk, $source, $port);
				return true;
			case OPEN_CONNECTION_REQUEST_2::$ID:
				

				if($packet->serverPort === $this->sessionManager->getPort() or !$this->sessionManager->portChecking){
					$mtuSize = min(abs($packet->mtuSize), 1464); 
					$pk = new OPEN_CONNECTION_REPLY_2();
					$pk->mtuSize = $mtuSize;
					$pk->serverID = $this->sessionManager->getID();
					$pk->clientAddress = $source;
					$pk->clientPort = $port;
					$this->sessionManager->sendPacket($pk, $source, $port);
					$this->sessionManager->createSession($source, $port, $packet->clientID, $mtuSize);
				}else{
					$this->sessionManager->getLogger()->debug("Not creating session for $source $port due to mismatched port, expected " . $this->sessionManager->getPort() . ", got " . $packet->serverPort);
				}
				return true;
		}

		return false;
	}

}