<?php
/*    
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█    
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░    
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.1
 *               InfinityProject By @Dr1xyDev    
 *   YT:         @Dr1xyDev    
 *   GitHub:     github.com/Dr1xyDev/Infinity    
*/

namespace raklib\server;

class UDPServerSocket{
	
	protected $logger;
	protected $socket;

	public function __construct(\ThreadedLogger $logger, $port = 19132, $interface = "0.0.0.0"){
		$this->socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
		
		if(@socket_bind($this->socket, $interface, $port) === true){
			socket_set_option($this->socket, SOL_SOCKET, SO_REUSEADDR, 0);
			$this->setSendBuffer(1024 * 1024 * 8)->setRecvBuffer(1024 * 1024 * 8);
		}else{
			$logger->critical("**** FAILED TO BIND TO " . $interface . ":" . $port . "!");
			$logger->critical("Perhaps a server is already running on that port?");
			exit(1);
		}
		socket_set_nonblock($this->socket);
	}

	public function getSocket(){
		return $this->socket;
	}

	public function close(){
		socket_close($this->socket);
	}

	
	public function readPacket(&$buffer, &$source, &$port){
		return socket_recvfrom($this->socket, $buffer, 65535, 0, $source, $port);
	}

	
	public function writePacket($buffer, $dest, $port){
		return socket_sendto($this->socket, $buffer, strlen($buffer), 0, $dest, $port);
	}

	
	public function setSendBuffer($size){
		@socket_set_option($this->socket, SOL_SOCKET, SO_SNDBUF, $size);

		return $this;
	}

	
	public function setRecvBuffer($size){
		@socket_set_option($this->socket, SOL_SOCKET, SO_RCVBUF, $size);

		return $this;
	}

}

