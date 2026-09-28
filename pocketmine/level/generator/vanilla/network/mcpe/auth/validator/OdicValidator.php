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

namespace pocketmine\level\generator\vanilla\network\mcpe\auth\validator;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use pocketmine\level\generator\vanilla\network\mcpe\auth\JwtToken;
use pocketmine\level\generator\vanilla\network\mcpe\auth\VerifyLoginException;
use function chunk_split;
use function openssl_pkey_get_public;

class OdicValidator implements Validator{

	public const MOJANG_AUDIENCE = "api://auth-minecraft-services/multiplayer";

	public function __construct(
		private JwtToken $token,
		private string $clientDataJwt,
		private string $pem,
		private string $expectedIss,
	){ }

	public function validate() : ValidatorResult{
		try{
			$header = $this->token->getHeader();

			if(!isset($header["kid"], $header["alg"]) || $header["alg"] !== "RS256"){
				throw new VerifyLoginException("Unexpected JWT algorithm");
			}

			try{
				$publicKey = openssl_pkey_get_public($this->pem);
				$oidcData = JWT::decode($this->token->getJwt(), new Key($publicKey, 'RS256'));
			}catch(\Exception $e){
				throw new VerifyLoginException($e->getMessage(), null, 0, $e);
			}

			if(!isset($oidcData->aud) || $oidcData->aud !== self::MOJANG_AUDIENCE){
				throw new VerifyLoginException("Invalid audience");
			}

			if(!isset($oidcData->iss) || $oidcData->iss !== $this->expectedIss){
				throw new VerifyLoginException("Invalid issuer");
			}

			if(!isset($oidcData->cpk)){
				throw new VerifyLoginException("Missing client public key");
			}

			$pemKey = "-----BEGIN PUBLIC KEY-----\n" . chunk_split($oidcData->cpk, 64) . "-----END PUBLIC KEY-----";
			JWT::decode($this->clientDataJwt, new Key($pemKey, 'ES384'));

			return new ValidatorResult(true);
		} catch(\Exception $e) {
			return new ValidatorResult(false, $e->getMessage());
		}
	}
}
