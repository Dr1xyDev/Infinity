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
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\TrimMaterial;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\TrimPattern;

use function count;

class TrimDataPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::TRIM_DATA_PACKET;

	/**
	 * @var TrimPattern[]
	 * @phpstan-var list<TrimPattern>
	 */
	public array $trimPatterns = [];
	/**
	 * @var TrimMaterial[]
	 * @phpstan-var list<TrimMaterial>
	 */
	public array $trimMaterials = [];

	/**
	 * @generate-create-func
	 * @param TrimPattern[]  $trimPatterns
	 * @param TrimMaterial[] $trimMaterials
	 * @phpstan-param list<TrimPattern>  $trimPatterns
	 * @phpstan-param list<TrimMaterial> $trimMaterials
	 */
	public static function create(array $trimPatterns, array $trimMaterials) : self
	{
		$result = new self();
		$result->trimPatterns = $trimPatterns;
		$result->trimMaterials = $trimMaterials;
		return $result;
	}

	/**
	 * @return TrimPattern[]
	 * @phpstan-return list<TrimPattern>
	 */
	public function getTrimPatterns() : array
	{
		return $this->trimPatterns;
	}

	/**
	 * @return TrimMaterial[]
	 * @phpstan-return list<TrimMaterial>
	 */
	public function getTrimMaterials() : array
	{
		return $this->trimMaterials;
	}

	protected function decodePayload() : void
	{
		$this->trimPatterns = [];
		for ($i = 0, $count = $this->getUnsignedVarInt(); $i < $count; ++$i) {
			$this->trimPatterns[] = TrimPattern::read($this);
		}
		$this->trimMaterials = [];
		for ($i = 0, $count = $this->getUnsignedVarInt(); $i < $count; ++$i) {
			$this->trimMaterials[] = TrimMaterial::read($this);
		}
	}

	protected function encodePayload() : void
	{
		$this->putUnsignedVarInt(count($this->trimPatterns));
		foreach ($this->trimPatterns as $trimPattern) {
			$trimPattern->write($this);
		}
		$this->putUnsignedVarInt(count($this->trimMaterials));
		foreach ($this->trimMaterials as $trimMaterial) {
			$trimMaterial->write($this);
		}
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleTrimData($this);
	}
}
