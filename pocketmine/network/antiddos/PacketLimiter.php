<?php
/*    
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█    
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░    
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.1
 *               InfinityProject By @Dr1xyDev    
 *   YT:         @Dr1xyDev    
 *   GitHub:     github.com/Dr1xyDev/Infinity    
*/

namespace pocketmine\network\antiddos;

use pocketmine\network\protocol\Info as ProtocolInfo;
use pocketmine\Player;

class PacketLimiter{

        
        private $limits = [];

        
        private $globalCap = 250;

        
        private $counts = [];

        
        private $globalCounts = [];

        
        private $currentSecond = 0;

        public function __construct(AntiDDoS $manager){
                
                $this->limits = [
                        ProtocolInfo::MOVE_PLAYER_PACKET        => 40,
                        ProtocolInfo::PLAYER_ACTION_PACKET      => 30,
                        ProtocolInfo::ANIMATE_PACKET            => 15,
                        ProtocolInfo::USE_ITEM_PACKET           => 25,
                        ProtocolInfo::TEXT_PACKET               => 5,
                        ProtocolInfo::INTERACT_PACKET           => 20,
                        ProtocolInfo::MOB_EQUIPMENT_PACKET      => 15,
                        ProtocolInfo::MOB_ARMOR_EQUIPMENT_PACKET=> 15,
                        ProtocolInfo::CONTAINER_CLOSE_PACKET    => 15,
                        ProtocolInfo::CONTAINER_SET_SLOT_PACKET => 40,
                        ProtocolInfo::CONTAINER_SET_CONTENT_PACKET => 30,
                        ProtocolInfo::CONTAINER_OPEN_PACKET     => 15,
                        ProtocolInfo::DROP_ITEM_PACKET          => 15,
                        ProtocolInfo::PLAYER_INPUT_PACKET       => 30,
                        ProtocolInfo::REQUEST_CHUNK_RADIUS_PACKET => 5,
                        ProtocolInfo::BLOCK_ENTITY_DATA_PACKET  => 20,
                        ProtocolInfo::REMOVE_BLOCK_PACKET       => 25,
                        ProtocolInfo::CRAFTING_EVENT_PACKET     => 10,
                        ProtocolInfo::ITEM_FRAME_DROP_ITEM_PACKET => 15,
                        ProtocolInfo::LOGIN_PACKET              => 1,
                        ProtocolInfo::RESPAWN_PACKET            => 5,
                ];
                $this->currentSecond = (int)time();
        }

        
        public function loadConfig(array $cfg): void{
                if(isset($cfg["player-limiter-global-cap"])){
                        $this->globalCap = (int)$cfg["player-limiter-global-cap"];
                }
                
                if(isset($cfg["player-limiter-limits"]) && is_array($cfg["player-limiter-limits"])){
                        foreach($cfg["player-limiter-limits"] as $name => $limit){
                                $const = "pocketmine\\network\\protocol\\Info::" . $name;
                                if(defined($const)){
                                        $id = constant($const);
                                        $this->limits[$id] = (int)$limit;
                                }
                        }
                }
        }

        
        public function check(Player $player, int $packetId): bool{
                $hash = spl_object_hash($player);

                
                $now = (int)time();
                if($now !== $this->currentSecond){
                        $this->counts = [];
                        $this->globalCounts = [];
                        $this->currentSecond = $now;
                }

                
                if(!isset($this->globalCounts[$hash])){
                        $this->globalCounts[$hash] = 0;
                        $this->counts[$hash] = [];
                }
                $this->globalCounts[$hash]++;

                
                if($this->globalCounts[$hash] > $this->globalCap){
                        return false;
                }

                
                $limit = $this->limits[$packetId] ?? 60;
                if(!isset($this->counts[$hash][$packetId])){
                        $this->counts[$hash][$packetId] = 0;
                }
                $this->counts[$hash][$packetId]++;
                if($this->counts[$hash][$packetId] > $limit){
                        return false;
                }

                return true;
        }

        
        public function tick(float $now): void{
                
                
                
                static $lastClean = 0.0;
                if($now - $lastClean < 30.0) return;
                $lastClean = $now;

                $server = \pocketmine\Server::getInstance();
                if($server === null) return;
                $onlineHashes = [];
                foreach($server->getOnlinePlayers() as $p){
                        $onlineHashes[spl_object_hash($p)] = true;
                }
                foreach($this->globalCounts as $hash => $_){
                        if(!isset($onlineHashes[$hash])){
                                unset($this->globalCounts[$hash]);
                                unset($this->counts[$hash]);
                        }
                }
        }
}
