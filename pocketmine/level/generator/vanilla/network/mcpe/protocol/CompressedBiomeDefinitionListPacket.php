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

use pocketmine\level\generator\vanilla\network\mcpe\NetworkSession;

/**
 * One of the most cursed packets to ever exist in Minecraft Bedrock Edition.
 *
 * This packet is only sent by the server when client-side chunk generation is enabled in vanilla. It contains NBT data
 * for biomes, similar to the BiomeDefinitionListPacket, but with a large amount of extra data for client-side chunk
 * generation.
 *
 * The data is compressed with a cursed home-brewed compression format, and it's a miracle it even works.
 * Hopefully this packet gets removed before I have to implement it...
 */
class CompressedBiomeDefinitionListPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::COMPRESSED_BIOME_DEFINITION_LIST_PACKET;

	public string $payload;

	/**
	 * @generate-create-func
	 */
	public static function create(string $payload) : self
	{
		$result = new self();
		$result->payload = $payload;
		return $result;
	}

	public function getPayload() : string
	{
		return $this->payload;
	}

	protected function decodePayload() : void
	{
		$this->payload = $this->getString();
	}

	protected function encodePayload() : void
	{
		$this->putString($this->payload);
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleCompressedBiomeDefinitionList($this);
	}
}
