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

namespace pocketmine\level\generator\vanilla\network\mcpe\protocol;

use pocketmine\level\generator\vanilla\network\mcpe\auth\JwtToken;
use pocketmine\level\generator\vanilla\network\mcpe\NetworkSession;
use pocketmine\level\generator\vanilla\utils\BinaryStream;
use pocketmine\level\generator\vanilla\utils\Utils;
use pocketmine\level\generator\vanilla\utils\UUID;
use Throwable;

use function get_class;
use function in_array;
use function is_array;
use function is_string;
use function json_decode;

use const JSON_THROW_ON_ERROR;

class LoginPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::LOGIN_PACKET;

	public string $username = "";
	public int $protocol;
	public int $gameEdition;
	public string $clientUUID = "";
	public int $clientId;
	public ?string $xuid = null;
	public string $identityPublicKey;
	public string $serverAddress;
	public string $locale;

	public array $authInfo = [];
	/** the "chain" index contains one or more JWTs */
	public array $chainData = [];
	public string $clientDataJwt;
	/** decoded payload of the clientData JWT */
	public array $clientData = [];

	public bool $isValidProtocol = true; // valid protocol

	/**
	 * This field may be used by plugins to bypass keychain verification. It should only be used for plugins such as
	 * Specter where passing verification would take too much time and not be worth it.
	 */
	public bool $skipVerification = false;

	public ?JwtToken $token = null;

	public function canBeSentBeforeLogin() : bool
	{
		return true;
	}

	public function mayHaveUnreadBytes() : bool
	{
		return $this->isValidProtocol === false;
	}

	protected function decodePayload() : void{
		$this->protocol = $this->getInt();

		if (!in_array($this->protocol, ProtocolInfo::ACCEPTED_PROTOCOLS, true)) {
			$this->isValidProtocol = false;

			return;
		}

		if ($this->protocol < ProtocolInfo::PROTOCOL_407) {
			$this->gameEdition = $this->getByte();
		}

		try {
			$this->decodeConnectionRequest();
		} catch (Throwable $e) {
			if ($this->isValidProtocol) {
				throw $e;
			}

			$logger = \GlobalLogger::get();
			$logger->debug(get_class($e) . " was thrown while decoding connection request in login (protocol version " . ($this->protocol ?? "unknown") . "): " . $e->getMessage());
			foreach (Utils::printableTrace($e->getTrace()) as $line) {
				$logger->debug($line);
			}
		}
	}

	protected function decodeConnectionRequest() : void
	{
		$buffer = new BinaryStream($this->getString());

		$authInfoJsonLength = $buffer->getLInt();
		if ($authInfoJsonLength <= 0) {
			//technically this is always positive; the problem results because getLInt() is implicitly signed
			//this is inconsistent with many other methods, but we can't do anything about that for now
			throw new PacketDecodeException("Length of auth info JSON must be positive");
		}

		try {
			$this->authInfo = json_decode($buffer->get($authInfoJsonLength), associative: true, flags: JSON_THROW_ON_ERROR);
		} catch (\JsonException $e) {
			throw new PacketDecodeException("Failed decoding chain data JSON: " . $e->getMessage());
		}

		if(isset($this->authInfo["Token"]) && $this->protocol >= ProtocolInfo::PROTOCOL_944) {
			$chainArray = [];
		} elseif (isset($this->authInfo["Certificate"]) && is_string($this->authInfo["Certificate"])) {
			$certificateData = json_decode($this->authInfo["Certificate"], true);
			if (isset($certificateData["chain"]) && is_array($certificateData["chain"])) {
				$chainArray = $certificateData;
			} else {
				throw new PacketDecodeException("Invalid 'chain' data in Certificate field");
			}
		} else {
			if (isset($this->authInfo["chain"]) && is_array($this->authInfo["chain"])) {
				$chainArray = $this->authInfo;
			} else {
				throw new PacketDecodeException("Missing or invalid 'chain' field in chain data");
			}
		}

		$this->chainData = $chainArray;

		if(isset($this->authInfo["Token"]) && $this->protocol >= ProtocolInfo::PROTOCOL_944){
			try{
				$this->token = JwtToken::parse($this->authInfo["Token"]);

				$this->username = $this->token->getClaims()->get("xname") ?? $this->username;

				if(($xid = $this->token->getClaims()->get("xid")) !== null){
					$this->clientUUID = UUID::fromXuid($xid)->toString();
					$this->xuid = $xid;
				}

				$this->identityPublicKey = $this->token->getClaims()->get("cpk") ?? $this->identityPublicKey;
			}catch(Throwable $e){
				throw new PacketDecodeException("Could not parse token: " . $e->getMessage());
			}
		}elseif(isset($chainArray["chain"]) && is_array($chainArray["chain"])){
			$hasExtraData = false;
			foreach($chainArray["chain"] as $chain){
				$webtoken = Utils::decodeJWT($chain);
				if(isset($webtoken["extraData"])){
					if($hasExtraData){
						throw new PacketDecodeException("Found 'extraData' multiple times in key chain");
					}
					$hasExtraData = true;

					$this->username = $webtoken["extraData"]["displayName"] ?? $this->username;
					$this->clientUUID = $webtoken["extraData"]["identity"] ?? $this->clientUUID;
					$this->xuid = $webtoken["extraData"]["XUID"] ?? $this->xuid;
				}

				if(isset($webtoken["identityPublicKey"])){
					$this->identityPublicKey = $webtoken["identityPublicKey"];
				}
			}
		}else{
			throw new PacketDecodeException("Neither Token nor legacy login successful");
		}

		$this->clientDataJwt = $buffer->get($buffer->getLInt());
		$this->clientData = Utils::decodeJWT($this->clientDataJwt);

		$this->clientId = $this->clientData["ClientRandomId"] ?? null;
		$this->serverAddress = $this->clientData["ServerAddress"] ?? null;

		$this->locale = $this->clientData["LanguageCode"] ?? null;
	}

	protected function encodePayload() : void
	{
		//TODO
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleLogin($this);
	}
}
