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

class AntiBot{

        
        private $manager;

        
        public $minNameLength = 3;

        
        public $maxNameLength = 16;

        
        public $blockDigitOnlyNames = true;

        
        public $blockConsecutiveChars = true;

        
        public $consecutiveThreshold = 5;

        
        public $forbiddenNamePatterns = [
                '/^bot\d+$/i',
                '/^mcbot\d*$/i',
                '/^\d{6,}$/',
                '/^(?:fuck|shit|nigger)/i',
        ];

        
        private $botScore = [];

        public function __construct(AntiDDoS $manager){
                $this->manager = $manager;
        }

        public function loadConfig(array $cfg): void{
                foreach($cfg as $k => $v){
                        if(property_exists($this, $k)){
                                $this->{$k} = $v;
                        }
                }
        }

        
        public function checkUsername(string $username): bool{
                $len = strlen($username);
                if($len < $this->minNameLength || $len > $this->maxNameLength){
                        return false;
                }
                if($this->blockDigitOnlyNames && ctype_digit($username)){
                        return false;
                }
                if($this->blockConsecutiveChars){
                        $count = 1;
                        for($i = 1; $i < $len; $i++){
                                if($username[$i] === $username[$i - 1]){
                                        $count++;
                                        if($count >= $this->consecutiveThreshold) return false;
                                }else{
                                        $count = 1;
                                }
                        }
                }
                foreach($this->forbiddenNamePatterns as $pattern){
                        if(preg_match($pattern, $username)) return false;
                }
                return true;
        }

        
        public function addBotScore(string $ip, int $score, string $reason): void{
                if(!isset($this->botScore[$ip])){
                        $this->botScore[$ip] = 0;
                }
                $this->botScore[$ip] += $score;
                if($this->botScore[$ip] >= 100){
                        $this->manager->ban($ip, "Bot score {$this->botScore[$ip]} ($reason)");
                        unset($this->botScore[$ip]);
                }
        }

        
        public function tick(float $now): void{
                
                static $lastDecay = 0.0;
                if($now - $lastDecay < 60.0) return;
                $lastDecay = $now;
                foreach($this->botScore as $ip => $score){
                        $this->botScore[$ip] = max(0, $score - 1);
                        if($this->botScore[$ip] === 0) unset($this->botScore[$ip]);
                }
        }

        public function getStats(): array{
                return [
                        "tracked_ips" => count($this->botScore),
                ];
        }
}
