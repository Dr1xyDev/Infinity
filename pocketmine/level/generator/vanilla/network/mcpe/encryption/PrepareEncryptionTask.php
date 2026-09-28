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

namespace pocketmine\level\generator\vanilla\network\mcpe\encryption;

use InvalidArgumentException;
use pocketmine\level\generator\vanilla\network\mcpe\JwtUtils;
use pocketmine\level\generator\vanilla\scheduler\AsyncTask;
use pocketmine\Server;

use function base64_encode;
use function igbinary_serialize;
use function igbinary_unserialize;
use function openssl_error_string;
use function openssl_free_key;
use function openssl_pkey_get_details;
use function openssl_pkey_new;
use function random_bytes;

class PrepareEncryptionTask extends AsyncTask
{
	private static ?\OpenSSLAsymmetricKey $SERVER_PRIVATE_KEY = null;

	private string $serverPrivateKey;
	private string $serverPublickey;
	private string $serverTokenRandom;

	private ?string $aesKey = null;
	private ?string $handshakeJwt = null;

	/**
	 * @phpstan-param \Closure(string $encryptionKey, string $handshakeJwt) : void $onCompletion
	 */
	public function __construct(
		private string $clientPub,
		\Closure $onCompletion
	) {
		if (self::$SERVER_PRIVATE_KEY === null) {
			$serverPrivateKey = openssl_pkey_new(["ec" => ["curve_name" => "secp384r1"]]);
			if ($serverPrivateKey === false) {
				throw new \RuntimeException("openssl_pkey_new() failed: " . openssl_error_string());
			}
			self::$SERVER_PRIVATE_KEY = $serverPrivateKey;
		}

		$this->serverPrivateKey = igbinary_serialize(openssl_pkey_get_details(self::$SERVER_PRIVATE_KEY));
		$this->storeLocal($onCompletion);
	}

	public function onRun() : void
	{
		/** @var mixed[] $serverPrivDetails */
		$serverPrivDetails = igbinary_unserialize($this->serverPrivateKey);
		$serverPriv = openssl_pkey_new($serverPrivDetails);

		if ($serverPriv === false) {
			throw new InvalidArgumentException("Failed to restore server signing key from details");
		}

		$clientPub = JwtUtils::parseDerPublicKey($this->clientPub);
		$sharedSecret = EncryptionUtils::generateSharedSecret($serverPriv, $clientPub);

		$salt = random_bytes(16);
		$this->aesKey = EncryptionUtils::generateKey($sharedSecret, $salt);

		$derServPublicKey = JwtUtils::emitDerPublicKey($serverPriv);

		$this->serverPublickey = base64_encode($derServPublicKey);
		$this->handshakeJwt = EncryptionUtils::generateServerHandshakeJwt($derServPublicKey, $serverPriv, $salt);

		$this->serverTokenRandom = $salt;

		@openssl_free_key($serverPriv);
		@openssl_free_key($clientPub);
	}

	public function onCompletion(Server $server) : void
	{
		/**
		 * @var \Closure $callback
		 * @phpstan-var \Closure(string $encryptionKey, string $handshakeJwt, string $publicServerKey, string $serverToken) : void $callback
		 */
		$callback = $this->fetchLocal();
		if ($this->aesKey === null || $this->handshakeJwt === null) {
			throw new InvalidArgumentException("Something strange happened here ...");
		}
		$callback($this->aesKey, $this->handshakeJwt, $this->serverPublickey, $this->serverTokenRandom);
	}
}
