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

namespace pocketmine\level\generator\vanilla\network\mcpe\auth\task;

use pocketmine\level\generator\vanilla\network\mcpe\auth\JwkProvider;
use pocketmine\level\generator\vanilla\network\mcpe\auth\JwtToken;
use pocketmine\level\generator\vanilla\network\mcpe\auth\validator\LegacyValidator;
use pocketmine\level\generator\vanilla\network\mcpe\auth\validator\OdicValidator;
use pocketmine\level\generator\vanilla\network\mcpe\auth\VerifyLoginException;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\LoginPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ProtocolInfo;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\login\AuthenticationType;
use pocketmine\Player;
use pocketmine\level\generator\vanilla\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\level\generator\vanilla\thread\NonThreadSafeValue;
use function is_array;
use function json_decode;
use function serialize;
use function unserialize;

class VerifyLoginTask extends AsyncTask{

	/** @var string */
	private $chainJwts;
	/** @var string */
	private $clientDataJwt;
	/** @phpstan-var NonThreadSafeValue<JwtToken> */
	private ?NonThreadSafeValue $token;
	private int $authType;

	/**
	 * @var string|null
	 * Whether the keychain signatures were validated correctly. This will be set to an error message if any link in the
	 * keychain is invalid for whatever reason (bad signature, not in nbf-exp window, etc). If this is non-null, the
	 * keychain might have been tampered with. The player will always be disconnected if this is non-null.
	 */
	private $error = "Unknown";
	/**
	 * @var bool
	 * Whether the player is logged into Xbox Live. This is true if any link in the keychain is signed with the Mojang
	 * root public key.
	 */
	private $authenticated = false;

	private function __construct(
		Player $player,
		LoginPacket $packet,
		private string $pem = "",
		private string $expectedIss = "",
	){
		$this->storeLocal([$player, $packet]);

		$chainJwtsExtracted = [];
		if ($packet->protocol >= ProtocolInfo::PROTOCOL_944) {
			if(!isset($packet->authInfo["Token"])){
				$certificateData = json_decode($packet->authInfo["Certificate"], true);
				if(is_array($certificateData["chain"] ?? null)){
					$chainJwtsExtracted = $certificateData["chain"];
				}
			}

			$this->token = new NonThreadSafeValue($packet->token);
			$this->authType = $packet->authInfo["AuthenticationType"];
		} else {
			$chainJwtsExtracted = $packet->chainData["chain"];
			$this->token = null;
			$this->authType = AuthenticationType::SELF_SIGNED;
		}

		$this->chainJwts = serialize($chainJwtsExtracted);
		$this->clientDataJwt = $packet->clientDataJwt;
	}

	public static function verify(Player $player, LoginPacket $packet) : void{
		if(!isset($packet->authInfo["Token"]) || $packet->protocol < ProtocolInfo::PROTOCOL_944){
			$player->getServer()->getAsyncPool()->submitTask(new self($player, $packet));
			return;
		}

		$kid = $packet->token->getHeader()["kid"] ?? "";
		$provider = JwkProvider::getInstance();

		$pem = $provider->getKey($kid, function(?string $pem) use ($packet, $kid, $provider, $player) : void{
			if($pem === null){
				$player->getServer()->getLogger()->warning("JWK: verification failed, no key available for kid $kid");
				$player->onVerifyCompleted($packet, null, false);
				return;
			}

			try{
				$player->getServer()->getAsyncPool()->submitTask(new self($player, $packet, $pem, $provider->getExpectedIssuer()));
			}catch(\Throwable $e){
				$player->getServer()->getLogger()->debug("Failed to schedule VerifyLoginTask: " . $e->getMessage());
				$player->onVerifyCompleted($packet, null, false);
			}
		});

		if($pem === null){
			$player->getServer()->getLogger()->debug("JWK: no cached key for kid $kid, verification deferred until keys applied");
			return;
		}

		try{
			$player->getServer()->getAsyncPool()->submitTask(new self($player, $packet, $pem, $provider->getExpectedIssuer()));
		}catch(\Throwable $e){
			$player->getServer()->getLogger()->debug("Failed to schedule VerifyLoginTask: " . $e->getMessage());
			$player->onVerifyCompleted($packet, null, false);
		}
	}

	public function onRun() : void{
		try{
			$validator = match (true) {
				$this->authType === AuthenticationType::FULL && $this->token !== null => new OdicValidator(
					$this->token->deserialize(),
					$this->clientDataJwt,
					$this->pem,
					$this->expectedIss
				),
				$this->authType === AuthenticationType::SELF_SIGNED => new LegacyValidator(
					unserialize($this->chainJwts),
					$this->clientDataJwt
				),
				default => throw new VerifyLoginException("Unknown or invalid authentication type: " . $this->authType),
			};

			$result = $validator->validate();
			$this->authenticated = $result->isAuthenticated();
			$this->error = $result->getError();
		}catch(VerifyLoginException $e){
			$this->error = $e->getMessage();
		}
	}

	public function onCompletion(Server $server){
		/**
		 * @var Player      $player
		 * @var LoginPacket $packet
		 */
		[$player, $packet] = $this->fetchLocal();
		if(!$player->isConnected()){
			$server->getLogger()->error("Player " . $player->getName() . " was disconnected before their login could be verified");
		}else{
			$player->onVerifyCompleted($packet, $this->error, $this->authenticated);
		}
	}
}
