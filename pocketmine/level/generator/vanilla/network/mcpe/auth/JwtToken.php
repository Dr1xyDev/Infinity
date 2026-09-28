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

use pocketmine\level\generator\vanilla\network\mcpe\JwtUtils;

/**
 * Represents a parsed JSON Web Token (JWT).
 *
 * A JWT is a compact token containing a header, payload (claims), and signature.
 * This class provides easy access to each part of the token.
 */
class JwtToken{

	private string $jwt;

	/** @var array<string, mixed> */
	private array $header;
	private ?JwtClaims $claims = null;
	private string $signature;

	/**
	 * Use JwtToken::parse() to create a new instance.
	 */
	private function __construct(){ }

	/**
	 * Parses a raw JWT string into a JwtToken instance.
	 *
	 * @param string $jwt The raw JWT string (header.payload.signature)
	 *
	 * @return self A JwtToken instance with parsed header, claims, and signature
	 */
	public static function parse(string $jwt) : self{
		[$header, $body, $signature] = JwtUtils::parse($jwt);

		$token = new self();
		$token->jwt = $jwt;
		$token->header = $header;
		$token->claims = new JwtClaims($body);
		$token->signature = $signature;

		return $token;
	}

	/**
	 * Returns the parsed JWT claims (payload).
	 */
	public function getClaims() : JwtClaims{
		return $this->claims;
	}

	/**
	 * Returns the decoded JWT header.
	 */
	public function getHeader() : array{
		return $this->header;
	}

	/**
	 * Returns the JWT signature part.
	 */
	public function getSignature() : string{
		return $this->signature;
	}

	/**
	 * Returns the original raw JWT string
	 */
	public function getJwt() : string{
		return $this->jwt;
	}
}
