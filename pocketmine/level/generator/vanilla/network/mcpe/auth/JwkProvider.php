<?php

/*
 *
 *   _____       _                          _
 *  / ____|     | |                        (_)
 * | (___  _   _| |__  _ __ ___   __ _ _ __ _ _ __   ___
 *  \___ \| | | | '_ \| '_ ` _ \ / _` | '__| | '_ \ / _ \
 *  ____) | |_| | |_) | | | | | | (_| | |  | | | | |  __/
 * |_____/ \__,_|_.__/|_| |_| |_|\__,_|_|  |_|_| |_|\___|
 *
 * This program is private software. No license required.
 * Publication of this program is forbidden and will be punished.
 *
 * @author SEMENNEJO
 * @link vk.com/vk.snikers && t.me/semennejo
 *
 *
 */

declare(strict_types=1);

namespace pocketmine\level\generator\vanilla\network\mcpe\auth;

use pocketmine\level\generator\vanilla\network\mcpe\auth\task\FetchJwksTask;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ProtocolInfo;
use pocketmine\Server;
use function time;

class JwkProvider{

	public static ?JwkProvider $instance = null;
	private const JWKS_TTL = 1800; // 30 minutes

	private string $minecraftVersion;
	private string $expectedIssuer = "";

	/** @var string[] */
	private array $keys = [];
	private int $lastFetchTime = 0;

	/** @var array<string, callable[]> */
	private array $pendingCallbacks = [];

	public function __construct(string $minecraftVersion){
		$this->minecraftVersion = $minecraftVersion;
	}

	public static function getInstance() : self{
		return self::$instance ??= new self(ProtocolInfo::MINECRAFT_VERSION_NETWORK);
	}

	public function getKey(string $kid, ?callable $callback = null) : ?string{
		if(!$this->needsRefresh()){
			if(isset($this->keys[$kid])){
				return $this->keys[$kid];
			}
		}

		if($callback !== null){
			$this->pendingCallbacks[$kid][] = $callback;
		}

		if($this->needsRefresh()){
			try{
				Server::getInstance()->getAsyncPool()->submitTask(new FetchJwksTask($this->minecraftVersion, \Closure::fromCallable([$this, 'applyFetchedKeys'])));
			}catch(\Throwable $e){
				Server::getInstance()->getLogger()->debug("Failed to schedule JWK fetch: " . $e->getMessage());
			}
		}

		return $this->keys[$kid] ?? null;
	}

	private function needsRefresh() : bool{
		return (time() - $this->lastFetchTime) >= self::JWKS_TTL;
	}

	private function applyFetchedKeys(array $keys, string $issuer) : void{
		$this->keys = $keys;
		$this->expectedIssuer = $issuer;
		$this->lastFetchTime = time();

		foreach($keys as $kid => $pem){
			if(!empty($this->pendingCallbacks[$kid])){
				foreach($this->pendingCallbacks[$kid] as $cb){
					try{
						$cb($pem);
					}catch(\Throwable $e){
						Server::getInstance()->getLogger()->debug("JWK callback error: " . $e->getMessage());
					}
				}
				unset($this->pendingCallbacks[$kid]);
			}
		}
	}

	public function getExpectedIssuer() : string{
		return $this->expectedIssuer;
	}
}
