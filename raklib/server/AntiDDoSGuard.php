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

use raklib\protocol\UNCONNECTED_PING;
use raklib\protocol\UNCONNECTED_PING_OPEN_CONNECTIONS;
use raklib\protocol\OPEN_CONNECTION_REQUEST_1;
use raklib\protocol\OPEN_CONNECTION_REQUEST_2;

class AntiDDoSGuard{

        

        
        public $maxPacketsPerSecond = 200;

        
        public $maxPingsPerSecond = 30;

        
        public $maxConnectionAttemptsPerWindow = 12;

        
        public $connectionWindowSeconds = 10;

        
        public $maxGlobalPacketsPerSecond = 8000;

        
        public $maxPacketBytes = 65535;

        
        public $maxNonDataPacketBytes = 4096;

        
        public $burstShortPackets = 80;
        public $burstShortWindowSec = 0.1;

        
        public $banBaseSeconds = 3600;       

        
        public $banMaxSeconds = 86400;       

        
        public $banEscalationMax = 4;

        

        
        private $banned = [];

        
        private $banHistory = [];

        
        private $packetCount = [];

        
        private $pingCount = [];

        
        private $connAttempts = [];

        
        private $globalPacketCount = 0;

        
        private $burstWindow = [];

        
        private $currentSecond = 0;

        
        private $lastBurstPrune = 0.0;

        
        private $logger = null;

        
        private $logFile = "RakLib.log";

        
        public $logAttacks = true;

        public function __construct($logger = null){
                $this->logger = $logger;
                $this->currentSecond = (int)time();
        }

        
        public function setConfig(array $cfg): void{
                foreach($cfg as $k => $v){
                        if(property_exists($this, $k)){
                                $this->{$k} = $v;
                        }
                }
        }

        

        
        public function checkIncomingPacket(string $ip, int $packetId, int $length): bool{
                
                if(isset($this->banned[$ip])){
                        if($this->banned[$ip] > microtime(true)){
                                return false;
                        }
                        unset($this->banned[$ip]);
                }

                
                $isDataPacket = ($packetId >= 0x80 && $packetId <= 0x8f);
                $sizeLimit = $isDataPacket ? $this->maxPacketBytes : $this->maxNonDataPacketBytes;
                if($length > $sizeLimit){
                        $this->ban($ip, "Oversize packet {$length}B (id=0x" . dechex($packetId) . ")");
                        return false;
                }

                
                $now = (int)time();
                if($now !== $this->currentSecond){
                        $this->rollSecond($now);
                        $this->currentSecond = $now;
                }

                
                if(!isset($this->packetCount[$ip])){
                        $this->packetCount[$ip] = 0;
                }
                $this->packetCount[$ip]++;
                $this->globalPacketCount++;

                
                if($this->packetCount[$ip] > $this->maxPacketsPerSecond){
                        $this->ban($ip, "Packet flood {$this->packetCount[$ip]}/s (limit {$this->maxPacketsPerSecond})");
                        return false;
                }

                
                if($this->globalPacketCount > $this->maxGlobalPacketsPerSecond){
                        
                        
                        $this->log("Global flood: {$this->globalPacketCount}/s — dropping packet from $ip");
                        return false;
                }

                
                if($packetId === 0x01 || $packetId === 0x02){
                        if(!isset($this->pingCount[$ip])){
                                $this->pingCount[$ip] = 0;
                        }
                        $this->pingCount[$ip]++;
                        if($this->pingCount[$ip] > $this->maxPingsPerSecond){
                                $this->ban($ip, "Ping flood {$this->pingCount[$ip]}/s (limit {$this->maxPingsPerSecond})");
                                return false;
                        }
                }

                
                if($packetId === 0x05 || $packetId === 0x07){
                        $now2 = microtime(true);
                        $this->connAttempts[$ip][] = $now2;
                        
                        $cutoff = $now2 - $this->connectionWindowSeconds;
                        $kept = [];
                        foreach($this->connAttempts[$ip] as $t){
                                if($t >= $cutoff) $kept[] = $t;
                        }
                        $this->connAttempts[$ip] = $kept;
                        if(count($kept) > $this->maxConnectionAttemptsPerWindow){
                                $this->ban($ip, "Connection flood " . count($kept) . "/{$this->connectionWindowSeconds}s (limit {$this->maxConnectionAttemptsPerWindow})");
                                return false;
                        }
                }

                
                $this->burstWindow[$ip][] = microtime(true);
                
                
                if(count($this->burstWindow[$ip]) > $this->burstShortPackets){
                        $cutoff = microtime(true) - $this->burstShortWindowSec;
                        $kept = [];
                        foreach($this->burstWindow[$ip] as $t){
                                if($t >= $cutoff) $kept[] = $t;
                        }
                        $this->burstWindow[$ip] = $kept;
                        if(count($kept) > $this->burstShortPackets){
                                $this->ban($ip, "Burst flood " . count($kept) . "/{$this->burstShortWindowSec}s (limit {$this->burstShortPackets})");
                                return false;
                        }
                }

                return true;
        }

        
        public function checkNewSession(string $ip): bool{
                if(isset($this->banned[$ip])){
                        if($this->banned[$ip] > microtime(true)){
                                return false;
                        }
                        unset($this->banned[$ip]);
                }
                return true;
        }

        
        public function isBanned(string $ip): bool{
                if(!isset($this->banned[$ip])) return false;
                if($this->banned[$ip] <= microtime(true)){
                        unset($this->banned[$ip]);
                        return false;
                }
                return true;
        }

        
        public function ban(string $ip, string $reason, ?int $timeout = null): void{
                if($timeout === null){
                        $offences = isset($this->banHistory[$ip]) ? $this->banHistory[$ip] : 0;
                        $multiplier = 1 << min($offences, $this->banEscalationMax);
                        $timeout = min($this->banBaseSeconds * $multiplier, $this->banMaxSeconds);
                        $this->banHistory[$ip] = $offences + 1;
                }
                $this->banned[$ip] = microtime(true) + $timeout;
                $this->log("Anti-DDoS ban: $ip — $reason — {$timeout}s");
        }

        
        public function unban(string $ip): void{
                unset($this->banned[$ip]);
                unset($this->banHistory[$ip]);
        }

        
        public function tick(): void{
                $now = microtime(true);

                
                if(count($this->banned) > 0){
                        foreach($this->banned as $ip => $expiry){
                                if($expiry <= $now){
                                        unset($this->banned[$ip]);
                                }
                        }
                }

                
                if($now - $this->lastBurstPrune > 1.0){
                        $this->lastBurstPrune = $now;
                        $cutoff = $now - $this->burstShortWindowSec;
                        foreach($this->burstWindow as $ip => $times){
                                $kept = [];
                                foreach($times as $t){
                                        if($t >= $cutoff) $kept[] = $t;
                                }
                                if(count($kept) === 0){
                                        unset($this->burstWindow[$ip]);
                                }else{
                                        $this->burstWindow[$ip] = $kept;
                                }
                        }
                }
        }

        
        private function rollSecond(int $newSecond): void{
                $this->packetCount = [];
                $this->pingCount = [];
                $this->globalPacketCount = 0;
                
                
        }

        
        public function getStats(): array{
                return [
                        "banned_ips"    => count($this->banned),
                        "tracked_ips"   => count($this->packetCount),
                        "global_pps"    => $this->globalPacketCount,
                        "current_sec"   => $this->currentSecond,
                ];
        }

        
        private function log(string $msg): void{
                if(!$this->logAttacks) return;
                if($this->logger !== null){
                        try{
                                $this->logger->notice("§6[AntiDDoS] §e" . $msg);
                        }catch(\Throwable $e){}
                }
                $d = date("m.d.y H:i:s");
                $ab = @fopen($this->logFile, "a+");
                if($ab !== false){
                        @fwrite($ab, "\n[$d] [AntiDDoS] $msg");
                        @fclose($ab);
                }
        }
}
