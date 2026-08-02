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

use pocketmine\Player;
use pocketmine\Server;

class AntiDDoS{

        
        private static $instance = null;

        
        private $server;

        

        
        public $maxSessionsPerIp = 4;

        
        public $maxLoginsPerWindow = 6;

        
        public $loginWindowSeconds = 10;

        
        public $botBanSeconds = 7200;       

        
        public $playerLimiterEnabled = true;

        

        
        private $sessionCount = [];

        
        private $loginTimes = [];

        
        private $banned = [];

        
        private $playerLimiter = null;

        
        private $antiBot = null;

        public function __construct(Server $server){
                $this->server = $server;
                $this->playerLimiter = new PacketLimiter($this);
                $this->antiBot = new AntiBot($this);
                self::$instance = $this;

                
                $cfg = [
                        "maxSessionsPerIp"      => $server->getAdvancedProperty("anti-ddos.max-sessions-per-ip", 4),
                        "maxLoginsPerWindow"    => $server->getAdvancedProperty("anti-ddos.max-logins-per-window", 6),
                        "loginWindowSeconds"    => $server->getAdvancedProperty("anti-ddos.login-window-seconds", 10),
                        "botBanSeconds"         => $server->getAdvancedProperty("anti-ddos.bot-ban-seconds", 7200),
                        "playerLimiterEnabled"  => (bool)$server->getAdvancedProperty("anti-ddos.player-limiter-enabled", true),
                        "player-limiter-global-cap" => (int)$server->getAdvancedProperty("anti-ddos.player-limiter-global-cap", 250),
                ];
                
                $limits = $server->getAdvancedProperty("anti-ddos.player-limiter-limits", []);
                if(is_array($limits)) $cfg["player-limiter-limits"] = $limits;
                $this->loadConfig($cfg);
        }

        public static function getInstance(): ?AntiDDoS{
                return self::$instance;
        }

        public function getServer(): Server{
                return $this->server;
        }

        public function getPacketLimiter(): PacketLimiter{
                return $this->playerLimiter;
        }

        public function getAntiBot(): AntiBot{
                return $this->antiBot;
        }

        
        public function loadConfig(array $cfg): void{
                foreach($cfg as $k => $v){
                        if(property_exists($this, $k)){
                                $this->{$k} = $v;
                        }
                }
                $this->playerLimiter->loadConfig($cfg);
                $this->antiBot->loadConfig($cfg);
        }

        
        public function getRakLibConfig(): array{
                return [
                        "maxPacketsPerSecond"             => $this->server->getAdvancedProperty("anti-ddos.max-packets-per-second", 200),
                        "maxPingsPerSecond"               => $this->server->getAdvancedProperty("anti-ddos.max-pings-per-second", 30),
                        "maxConnectionAttemptsPerWindow"  => $this->server->getAdvancedProperty("anti-ddos.max-conn-per-window", 12),
                        "connectionWindowSeconds"         => $this->server->getAdvancedProperty("anti-ddos.conn-window-seconds", 10),
                        "maxGlobalPacketsPerSecond"       => $this->server->getAdvancedProperty("anti-ddos.max-global-packets-per-second", 8000),
                        "maxPacketBytes"                  => $this->server->getAdvancedProperty("anti-ddos.max-packet-bytes", 65535),
                        "maxNonDataPacketBytes"           => $this->server->getAdvancedProperty("anti-ddos.max-nondatapacket-bytes", 4096),
                        "burstShortPackets"               => $this->server->getAdvancedProperty("anti-ddos.burst-short-packets", 80),
                        "burstShortWindowSec"             => $this->server->getAdvancedProperty("anti-ddos.burst-short-window", 0.1),
                        "banBaseSeconds"                  => $this->server->getAdvancedProperty("anti-ddos.ban-base-seconds", 3600),
                        "banMaxSeconds"                   => $this->server->getAdvancedProperty("anti-ddos.ban-max-seconds", 86400),
                        "banEscalationMax"                => $this->server->getAdvancedProperty("anti-ddos.ban-escalation-max", 4),
                        "logAttacks"                      => $this->server->getAdvancedProperty("anti-ddos.log-attacks", true),
                ];
        }

        

        
        public function onSessionOpen(string $ip): bool{
                if($this->isBanned($ip)){
                        return false;
                }
                if(!isset($this->sessionCount[$ip])){
                        $this->sessionCount[$ip] = 0;
                }
                $this->sessionCount[$ip]++;
                if($this->sessionCount[$ip] > $this->maxSessionsPerIp){
                        $this->ban($ip, "Too many concurrent sessions ({$this->sessionCount[$ip]} > {$this->maxSessionsPerIp})");
                        return false;
                }
                return true;
        }

        
        public function onSessionClose(string $ip): void{
                if(isset($this->sessionCount[$ip])){
                        $this->sessionCount[$ip]--;
                        if($this->sessionCount[$ip] <= 0){
                                unset($this->sessionCount[$ip]);
                        }
                }
        }

        
        public function onLoginAttempt(string $ip): bool{
                if($this->isBanned($ip)){
                        return false;
                }
                $now = microtime(true);
                $this->loginTimes[$ip][] = $now;
                
                $cutoff = $now - $this->loginWindowSeconds;
                $kept = [];
                foreach($this->loginTimes[$ip] as $t){
                        if($t >= $cutoff) $kept[] = $t;
                }
                $this->loginTimes[$ip] = $kept;
                if(count($kept) > $this->maxLoginsPerWindow){
                        $this->ban($ip, "Login flood " . count($kept) . "/{$this->loginWindowSeconds}s (limit {$this->maxLoginsPerWindow})");
                        return false;
                }
                return true;
        }

        

        
        public function checkPlayerPacket(Player $player, int $packetNetworkId): bool{
                if(!$this->playerLimiterEnabled) return true;
                return $this->playerLimiter->check($player, $packetNetworkId);
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
                if($timeout === null) $timeout = $this->botBanSeconds;
                $this->banned[$ip] = microtime(true) + $timeout;
                
                foreach($this->server->getNetwork()->getInterfaces() as $iface){
                        if(method_exists($iface, "blockAddress")){
                                $iface->blockAddress($ip, $timeout);
                        }
                }
                $this->server->getLogger()->notice("§6[AntiDDoS/Bot] §eIP §b$ip §ebaneada: §7$reason §e({$timeout}s)");
                $this->logToFile("BAN $ip — $reason — {$timeout}s");
        }

        public function unban(string $ip): void{
                unset($this->banned[$ip]);
                foreach($this->server->getNetwork()->getInterfaces() as $iface){
                        if(method_exists($iface, "unblockAddress")){
                                $iface->unblockAddress($ip);
                        }
                }
        }

        public function getBannedIps(): array{
                $now = microtime(true);
                $list = [];
                foreach($this->banned as $ip => $expiry){
                        if($expiry > $now) $list[$ip] = $expiry - $now;
                }
                return $list;
        }

        

        public function tick(): void{
                $now = microtime(true);

                
                if(count($this->banned) > 0){
                        foreach($this->banned as $ip => $expiry){
                                if($expiry <= $now) unset($this->banned[$ip]);
                        }
                }

                
                $cutoff = $now - $this->loginWindowSeconds;
                foreach($this->loginTimes as $ip => $times){
                        $kept = [];
                        foreach($times as $t){
                                if($t >= $cutoff) $kept[] = $t;
                        }
                        if(count($kept) === 0) unset($this->loginTimes[$ip]);
                        else $this->loginTimes[$ip] = $kept;
                }

                
                $this->playerLimiter->tick($now);
        }

        

        private function logToFile(string $msg): void{
                $d = date("m.d.y H:i:s");
                $ab = @fopen($this->server->getDataPath() . "AntiDDoS.log", "a+");
                if($ab !== false){
                        @fwrite($ab, "[$d] $msg\n");
                        @fclose($ab);
                }
        }

        public function getStats(): array{
                return [
                        "banned_ips"        => count($this->banned),
                        "tracked_sessions"  => count($this->sessionCount),
                        "login_flood_watch" => count($this->loginTimes),
                ];
        }
}
